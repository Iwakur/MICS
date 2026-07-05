{{-- MICS Blade view: admin users index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'User Management | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'User Management')
@section('page-description', 'Administrators can create, edit, activate, deactivate, and selectively delete user accounts here. Safety rules still protect the last active admin and the current administrator account.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-shell-text">All Users</h3>
                <p class="mt-2 max-w-2xl text-sm text-shell-muted">
                    This is the first real admin CRUD screen. It works directly against the Laravel users table and respects the current role and activation rules.
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}" class="app-button-primary">
                Create User
            </a>
        </div>

        {{-- The table keeps the current user list transparent before more complex staff modules exist. --}}
        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">Username</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shell-border bg-shell-panel/70 text-shell-text">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-shell-text">{{ $user->username }}</div>
                                @if (auth()->id() === $user->id)
                                    <div class="mt-1 text-xs uppercase tracking-[0.2em] text-brand-300">You</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-shell-muted">{{ $user->email }}</td>
                            <td class="px-4 py-4">
                                <span class="{{ $user->isAdmin() ? 'app-badge-admin' : 'app-badge-teacher' }}">
                                    {{ $user->role->value }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="{{ $user->is_active ? 'app-badge-active' : 'app-badge-inactive' }}">
                                    {{ $user->is_active ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="app-button-danger">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $users->links() }}</div>
    </section>
@endsection
