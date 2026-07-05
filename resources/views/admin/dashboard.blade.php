@extends('layouts.app')

@section('title', 'Admin Dashboard | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'The administrator home is the control point for managing access and for entering future school operations modules.')

@section('content')
    {{-- Summary cards give the admin a quick read on the current user base. --}}
    <section class="grid gap-4 md:grid-cols-3">
        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Users</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $totalUsers }}</p>
            <p class="mt-2 text-sm text-shell-muted">Total accounts currently stored in the Laravel users table.</p>
        </article>

        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Active Admins</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $activeAdmins }}</p>
            <p class="mt-2 text-sm text-shell-muted">At least one active administrator must always remain available.</p>
        </article>

        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Active Teachers</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $activeTeachers }}</p>
            <p class="mt-2 text-sm text-shell-muted">Teachers have a narrower workspace than administrators.</p>
        </article>
    </section>

    {{-- The first operational action on the dashboard is user management. --}}
    <section class="app-surface mt-6 p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-shell-text">User Management</h3>
                <p class="mt-2 max-w-2xl text-sm text-shell-muted">
                    Maintain usernames, emails, roles, passwords, and activation status without violating the current database rules.
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="app-button-primary">
                Open User Management
            </a>
        </div>
    </section>
@endsection
