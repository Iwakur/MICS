{{-- MICS HUB Blade view: admin dashboard. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard | MICS HUB')
@section('eyebrow', 'Administrator')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'People, billing setup, and access controls are separated so each part of school operations has a clear home.')

@section('content')
    <section class="app-surface mb-6 p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label for="month" class="text-sm font-medium text-shell-text">Dashboard month</label>
                    <input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}" class="app-input mt-2" required>
                </div>
                <button class="app-button-secondary">View month</button>
            </form>
            <nav aria-label="Dashboard month navigation" class="flex flex-wrap gap-2">
                <a href="{{ route('admin.dashboard', ['month' => $month->subMonth()->format('Y-m')]) }}" class="app-button-secondary">Previous</a>
                <a href="{{ route('admin.dashboard', ['month' => $currentMonth->format('Y-m')]) }}" class="app-button-secondary">Current month</a>
                <a href="{{ route('admin.dashboard', ['month' => $month->addMonth()->format('Y-m')]) }}" class="app-button-secondary">Next</a>
            </nav>
        </div>
        <p class="mt-4 text-sm text-shell-muted">Showing the student configuration effective in {{ \App\Support\LocalizedFormat::month($month) }}.</p>
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
        <article class="app-surface p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">People</p>
            <div class="mt-5 grid grid-cols-3 gap-4">
                <div><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['total'] }}</p><p class="mt-1 text-xs text-shell-muted">Students</p></div>
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

    <section class="app-surface mt-6 p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-300">Student analysis</p>
        <h3 class="mt-2 text-xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::month($month) }} student snapshot</h3>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="app-surface-strong p-4"><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['total'] }}</p><p class="mt-1 text-sm text-shell-muted">Total</p></div>
            <div class="app-surface-strong p-4"><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['active'] }}</p><p class="mt-1 text-sm text-shell-muted">Active</p></div>
            <div class="app-surface-strong p-4"><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['paused'] }}</p><p class="mt-1 text-sm text-shell-muted">Paused</p></div>
            <div class="app-surface-strong p-4"><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['archived'] }}</p><p class="mt-1 text-sm text-shell-muted">Archived</p></div>
            <div class="app-surface-strong p-4"><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['per_lesson'] }}</p><p class="mt-1 text-sm text-shell-muted">Per lesson</p></div>
            <div class="app-surface-strong p-4"><p class="text-3xl font-semibold text-shell-text">{{ $studentStatistics['plan_based'] }}</p><p class="mt-1 text-sm text-shell-muted">Monthly plan</p></div>
        </div>
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
        <h3 class="mt-2 text-xl font-semibold text-shell-text">{{ __('finance.review_validate_generate') }}</h3>
        <p class="mt-2 text-sm text-shell-muted">Keep operational inputs separate from financial validation and month lifecycle controls.</p>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.finance-summary', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.finance_summary') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.summary_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
            <a href="{{ route('admin.lesson-counts.index', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.monthly_lesson_counts') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.lesson_counts_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
            <a href="{{ route('admin.month-closing.index', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.month_closing') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('messages.draft_generation_card_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
            <a href="{{ route('admin.student-charges.index', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.student_charges') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.charges_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
            <a href="{{ route('admin.payments.index', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.student_payments') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.payments_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
            <a href="{{ route('admin.expenses.index', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.expenses_salaries') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.expenses_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
            <a href="{{ route('admin.bank-months.index', ['month' => $month->format('Y-m')]) }}" class="app-action-card app-surface-strong group p-5"><p class="font-semibold text-shell-text">{{ __('messages.bank_reconciliation') }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.bank_description') }}</p><span class="app-action-card-label">{{ __('finance.open') }} →</span></a>
        </div>
    </section>
@endsection
