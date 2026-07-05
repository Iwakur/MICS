<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Admin CRUD controller for application users.
 *
 * This is the first real operational CRUD screen in the rebuilt app. It keeps
 * the controller readable by delegating field validation to Form Requests and
 * keeping only business-protection rules here.
 */
class UserController extends Controller
{
    /**
     * Show the current users, with admins listed first for visibility.
     */
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('staffMember')
                ->orderByRaw('case when role = ? then 0 else 1 end', [UserRole::Admin->value])
                ->orderBy('username')
                ->paginate(25),
        ]);
    }

    /**
     * Show the create-user form.
     */
    public function create(): View
    {
        return view('admin.users.create', ['staffMembers' => $this->availableStaff()]);
    }

    /**
     * Create a new user from already-validated form input.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    /**
     * Show the edit form for a single managed user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'managedUser' => $user,
            'staffMembers' => $this->availableStaff($user),
        ]);
    }

    /**
     * Update a user while enforcing high-level business protection rules.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Never allow an edit to remove the last active administrator.
        $this->ensureLastActiveAdminRemains($user, $data);

        // A blank password means "keep the current one" rather than "erase it".
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

    /**
     * Delete a user when doing so does not break admin safety rules.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureDeleteAllowed($request->user(), $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureLastActiveAdminRemains(User $user, array $data): void
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            return;
        }

        $willRemainAdmin = ($data['role'] ?? $user->role->value) === UserRole::Admin->value;
        $willRemainActive = (bool) ($data['is_active'] ?? $user->is_active);

        if ($willRemainAdmin && $willRemainActive) {
            return;
        }

        $hasAnotherActiveAdmin = User::query()
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->whereKeyNot($user->id)
            ->exists();

        if (! $hasAnotherActiveAdmin) {
            throw ValidationException::withMessages([
                'role' => 'At least one active administrator must remain in the system.',
            ]);
        }
    }

    /**
     * Prevent destructive admin actions that would lock the app out of admin access.
     */
    private function ensureDeleteAllowed(User $actingUser, User $user): void
    {
        if ($actingUser->is($user)) {
            throw ValidationException::withMessages([
                'danger' => 'You cannot delete your own account.',
            ]);
        }

        if (! $user->isAdmin() || ! $user->is_active) {
            return;
        }

        $hasAnotherActiveAdmin = User::query()
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->whereKeyNot($user->id)
            ->exists();

        if (! $hasAnotherActiveAdmin) {
            throw ValidationException::withMessages([
                'danger' => 'You cannot delete the last active administrator.',
            ]);
        }
    }

    private function availableStaff(?User $user = null)
    {
        return Staff::query()->with('role')
            ->where('is_active', true)
            ->where(function ($query) use ($user): void {
                $query->whereDoesntHave('user');
                if ($user?->staff_id) {
                    $query->orWhere('id', $user->staff_id);
                }
            })
            ->orderBy('first_name')->orderBy('family_name')->get();
    }
}
