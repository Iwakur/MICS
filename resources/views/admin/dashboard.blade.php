@extends('layouts.app')

@section('title', 'Admin Dashboard | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'The administrator home is the control point for managing access and for entering future school operations modules.')

@section('content')
    {{-- Summary cards give the admin a quick read on people and billing catalogs. --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Users</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $totalUsers }}</p>
            <p class="mt-2 text-sm text-shell-muted">Total accounts currently stored in the Laravel users table.</p>
        </article>

        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Staff</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $totalStaff }}</p>
            <p class="mt-2 text-sm text-shell-muted">{{ $fixedStaff }} fixed · {{ $dynamicStaff }} dynamic compensation profiles.</p>
        </article>

        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Lesson Types</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $totalLessonTypes }}</p>
            <p class="mt-2 text-sm text-shell-muted">Per-lesson school prices and teacher earning rates.</p>
        </article>

        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Plans</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $totalPlans }}</p>
            <p class="mt-2 text-sm text-shell-muted">Monthly student charges and teacher earning amounts.</p>
        </article>

        <article class="app-surface p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-shell-muted">Students</p>
            <p class="mt-4 text-4xl font-semibold text-shell-text">{{ $totalStudents }}</p>
            <p class="mt-2 text-sm text-shell-muted">Student profiles across both billing models.</p>
        </article>
    </section>

    {{-- Operational shortcuts keep the main admin catalogs one click away. --}}
    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Staff</h3>
            <p class="mt-2 max-w-2xl text-sm text-shell-muted">
                Create and maintain staff identities, active state, and optional linked login accounts.
            </p>
            <a href="{{ route('admin.staff.index') }}" class="app-button-primary mt-4 inline-flex">
                Open Staff
            </a>
        </article>

        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Lesson Types</h3>
            <p class="mt-2 text-sm text-shell-muted">Maintain per-lesson school and teacher rates.</p>
            <a href="{{ route('admin.lesson-types.index') }}" class="app-button-primary mt-4 inline-flex">Open Lesson Types</a>
        </article>

        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Students</h3>
            <p class="mt-2 text-sm text-shell-muted">Assign teachers and configure per-lesson or plan billing.</p>
            <a href="{{ route('admin.students.index') }}" class="app-button-primary mt-4 inline-flex">Open Students</a>
        </article>

        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Plans</h3>
            <p class="mt-2 text-sm text-shell-muted">Maintain monthly plan charges and teacher amounts.</p>
            <a href="{{ route('admin.plans.index') }}" class="app-button-primary mt-4 inline-flex">Open Plans</a>
        </article>

        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">User Management</h3>
            <p class="mt-2 max-w-2xl text-sm text-shell-muted">
                Maintain usernames, passwords, roles, and activation status for login access.
            </p>
            <a href="{{ route('admin.users.index') }}" class="app-button-primary mt-4 inline-flex">
                Open User Management
            </a>
        </article>
    </section>
@endsection
