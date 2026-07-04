<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->orderByRaw('case when role = ? then 0 else 1 end', [UserRole::Admin->value])
                ->orderBy('username')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'managedUser' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $this->ensureLastActiveAdminRemains($user, $data);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }

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
}
