@extends('layouts.app')

@section('title', 'Staff | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Staff')
@section('page-description', 'Manage staff identity, active state, and linked login accounts. Staff records stay separate from access accounts.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-shell-text">All Staff</h3>
                <p class="mt-2 max-w-2xl text-sm text-shell-muted">
                    Staff is the business profile layer. Users can be linked later as login accounts.
                </p>
            </div>

            <a href="{{ route('admin.staff.create') }}" class="app-button-primary">
                Create Staff
            </a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium">Students</th>
                        <th class="px-4 py-3 font-medium">Compensation</th>
                        <th class="px-4 py-3 font-medium">Account</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shell-border bg-shell-panel/70 text-shell-text">
                    @foreach ($staffMembers as $staffMember)
                        <tr>
                            <td class="px-4 py-4 font-semibold text-shell-text">
                                {{ trim($staffMember->first_name.' '.$staffMember->family_name) }}
                            </td>
                            <td class="px-4 py-4 text-shell-muted">{{ $staffMember->role?->name }}</td>
                            <td class="px-4 py-4 text-shell-muted">{{ $staffMember->phone ?: $staffMember->email ?: 'No contact' }}</td>
                            <td class="px-4 py-4 text-shell-muted">{{ $staffMember->students_count }}</td>
                            <td class="px-4 py-4 text-shell-muted">
                                @if ($staffMember->compensation_mode->value === 'fixed')
                                    Fixed · {{ number_format((float) $staffMember->salary_amount, 2) }}
                                @else
                                    Dynamic · calculated monthly
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($staffMember->user)
                                    <span class="app-badge-active">{{ $staffMember->user->username }}</span>
                                @else
                                    <span class="app-badge-inactive">Unlinked</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="{{ $staffMember->is_active ? 'app-badge-active' : 'app-badge-inactive' }}">
                                    {{ $staffMember->is_active ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.staff.edit', $staffMember) }}" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.staff.destroy', $staffMember) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="app-button-danger">
                                            Archive
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
