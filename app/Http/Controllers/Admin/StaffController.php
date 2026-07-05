<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staffMembers' => Staff::query()
                ->with(['role', 'user'])
                ->withCount('students')
                ->orderByDesc('is_active')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'staffRoles' => StaffRole::query()->where('is_active', true)->orderBy('name')->get(),
            'availableUsers' => $this->availableUsers(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $staff = Staff::query()->create($request->validatedStaff());

            if ($request->linkedUserId()) {
                User::query()->findOrFail($request->linkedUserId())->update(['staff_id' => $staff->id]);
            }
        });

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff member created successfully.');
    }

    public function edit(Staff $staff): View
    {
        $staff->load('user');

        return view('admin.staff.edit', [
            'staffMember' => $staff,
            'staffRoles' => StaffRole::query()
                ->where('is_active', true)
                ->orWhereKey($staff->staff_role_id)
                ->orderBy('name')
                ->get(),
            'availableUsers' => $this->availableUsers($staff),
        ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        DB::transaction(function () use ($request, $staff): void {
            $staff->update($request->validatedStaff());

            $selectedUserId = $request->linkedUserId();

            if ($staff->user && $staff->user->id !== $selectedUserId) {
                $staff->user->update(['staff_id' => null]);
            }

            if ($selectedUserId) {
                User::query()->findOrFail($selectedUserId)->update(['staff_id' => $staff->id]);
            }
        });

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->update(['is_active' => false]);

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff member archived successfully.');
    }

    private function availableUsers(?Staff $staff = null)
    {
        return User::query()
            ->where(function ($query) use ($staff): void {
                $query->whereNull('staff_id');

                if ($staff?->user) {
                    $query->orWhereKey($staff->user->id);
                }
            })
            ->orderBy('username')
            ->get();
    }
}
