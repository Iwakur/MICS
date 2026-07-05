<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveStaffRoleRequest;
use App\Models\StaffRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffRoleController extends Controller
{
    public function index(): View
    {
        return view('admin.staff-roles.index', [
            'staffRoles' => StaffRole::query()
                ->withCount('staffMembers')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff-roles.create');
    }

    public function store(SaveStaffRoleRequest $request): RedirectResponse
    {
        StaffRole::query()->create($request->validated());

        return to_route('admin.staff-roles.index')->with('status', 'Staff role created successfully.');
    }

    public function edit(StaffRole $staffRole): View
    {
        return view('admin.staff-roles.edit', ['staffRole' => $staffRole]);
    }

    public function update(SaveStaffRoleRequest $request, StaffRole $staffRole): RedirectResponse
    {
        $staffRole->update($request->validated());

        return to_route('admin.staff-roles.index')->with('status', 'Staff role updated successfully.');
    }

    public function destroy(StaffRole $staffRole): RedirectResponse
    {
        $staffRole->update(['is_active' => false]);

        return to_route('admin.staff-roles.index')->with('status', 'Staff role archived successfully.');
    }
}
