{{-- MICS Blade view: admin staff-roles index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Staff Roles | MICS')
@section('eyebrow', 'Administrator · Staff')
@section('page-title', 'Staff Roles')
@section('page-description', 'Business roles describe staff responsibilities. Login permissions remain separate in User Management.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-shell-text">Role Catalog</h3>
                <p class="mt-2 text-sm text-shell-muted">Teaching capability controls who may receive students; names are descriptive only.</p>
            </div>
            <a href="{{ route('admin.staff-roles.create') }}" class="app-button-primary">Create Staff Role</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted">
                    <tr><th class="px-4 py-3 font-medium">Role</th><th class="px-4 py-3 font-medium">Capability</th><th class="px-4 py-3 font-medium">Staff</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-shell-border bg-shell-panel/70 text-shell-text">
                    @forelse ($staffRoles as $staffRole)
                        <tr>
                            <td class="px-4 py-4"><p class="font-semibold">{{ $staffRole->name }}</p><p class="mt-1 text-xs text-shell-muted">{{ $staffRole->note ?: 'No note' }}</p></td>
                            <td class="px-4 py-4"><span class="{{ $staffRole->can_teach ? 'app-badge-teacher' : 'app-badge-inactive' }}">{{ $staffRole->can_teach ? 'can teach' : 'non-teaching' }}</span></td>
                            <td class="px-4 py-4 text-shell-muted">{{ $staffRole->staff_members_count }}</td>
                            <td class="px-4 py-4"><span class="{{ $staffRole->is_active ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $staffRole->is_active ? 'active' : 'archived' }}</span></td>
                            <td class="px-4 py-4"><div class="flex flex-wrap gap-3"><a href="{{ route('admin.staff-roles.edit', $staffRole) }}" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">Edit</a>@if ($staffRole->is_active)<form method="POST" action="{{ route('admin.staff-roles.destroy', $staffRole) }}">@csrf @method('DELETE')<button type="submit" class="app-button-danger">Archive</button></form>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-shell-muted">No staff roles configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $staffRoles->links() }}</div>
    </section>
@endsection
