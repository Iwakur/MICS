{{-- MICS Blade view: admin dashboard. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'People, billing setup, and access controls are separated so each part of school operations has a clear home.')

@section('content')
    <section class="grid gap-4 lg:grid-cols-3">
        <article class="app-surface p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">People</p>
            <div class="mt-5 grid grid-cols-3 gap-4">
                <div><p class="text-3xl font-semibold text-shell-text">{{ $totalStudents }}</p><p class="mt-1 text-xs text-shell-muted">Students</p></div>
                <div><p class="text-3xl font-semibold text-shell-text">{{ $totalStaff }}</p><p class="mt-1 text-xs text-shell-muted">Staff</p></div>
                <div><p class="text-3xl font-semibold text-shell-text">{{ $totalUsers }}</p><p class="mt-1 text-xs text-shell-muted">Accounts</p></div>
            </div>
        </article>

        <article class="app-surface p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">Compensation</p>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <div><p class="text-3xl font-semibold text-shell-text">{{ $fixedStaff }}</p><p class="mt-1 text-xs text-shell-muted">Fixed staff</p></div>
                <div><p class="text-3xl font-semibold text-shell-text">{{ $dynamicStaff }}</p><p class="mt-1 text-xs text-shell-muted">Dynamic staff</p></div>
            </div>
        </article>

        <article class="app-surface p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">Billing Catalog</p>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <div><p class="text-3xl font-semibold text-shell-text">{{ $totalLessonTypes }}</p><p class="mt-1 text-xs text-shell-muted">Lesson types</p></div>
                <div><p class="text-3xl font-semibold text-shell-text">{{ $totalPlans }}</p><p class="mt-1 text-xs text-shell-muted">Plans</p></div>
            </div>
        </article>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-[1.2fr_1fr]">
        <article class="app-surface p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">People</p>
                <h3 class="mt-2 text-xl font-semibold text-shell-text">Students and staff</h3>
                <p class="mt-2 text-sm text-shell-muted">Daily profile management and assignment controls.</p>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <a href="{{ route('admin.students.index') }}" class="app-surface-strong p-5">
                    <p class="font-semibold text-shell-text">Students</p><p class="mt-2 text-sm text-shell-muted">Assignments and billing modes.</p><span class="app-button-secondary mt-4 inline-flex">Open</span>
                </a>
                <a href="{{ route('admin.staff.index') }}" class="app-surface-strong p-5">
                    <p class="font-semibold text-shell-text">Staff</p><p class="mt-2 text-sm text-shell-muted">Profiles and compensation.</p><span class="app-button-secondary mt-4 inline-flex">Open</span>
                </a>
                <a href="{{ route('admin.staff-roles.index') }}" class="app-surface-strong p-5">
                    <p class="font-semibold text-shell-text">Staff Roles</p><p class="mt-2 text-sm text-shell-muted">Teaching capabilities.</p><span class="app-button-secondary mt-4 inline-flex">Open</span>
                </a>
            </div>
        </article>

        <article class="app-surface p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">Access</p>
            <h3 class="mt-2 text-xl font-semibold text-shell-text">Login accounts</h3>
            <p class="mt-2 text-sm text-shell-muted">Manage usernames, passwords, admin access, and teacher access separately from business profiles.</p>
            <a href="{{ route('admin.users.index') }}" class="app-button-primary mt-6 inline-flex">Open User Management</a>
        </article>
    </section>

    <section class="app-surface mt-6 p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">Billing Setup</p>
        <h3 class="mt-2 text-xl font-semibold text-shell-text">Student and teacher rates</h3>
        <p class="mt-2 text-sm text-shell-muted">These catalogs provide the amounts used later during manual month closing.</p>
        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <a href="{{ route('admin.lesson-types.index') }}" class="app-surface-strong flex items-center justify-between gap-4 p-5">
                <div><p class="font-semibold text-shell-text">Lesson Types</p><p class="mt-1 text-sm text-shell-muted">Per-lesson student price and teacher earning.</p></div><span class="app-button-secondary">Open</span>
            </a>
            <a href="{{ route('admin.plans.index') }}" class="app-surface-strong flex items-center justify-between gap-4 p-5">
                <div><p class="font-semibold text-shell-text">Plans</p><p class="mt-1 text-sm text-shell-muted">Monthly student price and teacher earning.</p></div><span class="app-button-secondary">Open</span>
            </a>
        </div>
    </section>

    <section class="app-surface mt-6 p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">Monthly Finance</p>
        <h3 class="mt-2 text-xl font-semibold text-shell-text">Review, validate, and close</h3>
        <p class="mt-2 text-sm text-shell-muted">Keep operational inputs separate from financial validation and month lifecycle controls.</p>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <a href="{{ route('admin.finance-summary') }}" class="app-surface-strong p-5"><p class="font-semibold text-shell-text">Finance Summary</p><p class="mt-2 text-sm text-shell-muted">See monthly totals and debt.</p></a>
            <a href="{{ route('admin.lesson-counts.index') }}" class="app-surface-strong p-5"><p class="font-semibold text-shell-text">Lesson Counts</p><p class="mt-2 text-sm text-shell-muted">Enter monthly teaching activity.</p></a>
            <a href="{{ route('admin.month-closing.index') }}" class="app-surface-strong p-5"><p class="font-semibold text-shell-text">Month Closing</p><p class="mt-2 text-sm text-shell-muted">Generate or reopen monthly drafts.</p></a>
            <a href="{{ route('admin.payments.index') }}" class="app-surface-strong p-5"><p class="font-semibold text-shell-text">Payments</p><p class="mt-2 text-sm text-shell-muted">Validate student payment evidence.</p></a>
            <a href="{{ route('admin.expenses.index') }}" class="app-surface-strong p-5"><p class="font-semibold text-shell-text">Expenses</p><p class="mt-2 text-sm text-shell-muted">Review salaries and manual costs.</p></a>
        </div>
    </section>
@endsection
