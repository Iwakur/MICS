{{-- MICS HUB Blade view: admin finance-summary. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', __('messages.finance_summary').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('finance.monthly_finance_summary'))
@section('page-description', __('finance.summary_page_description'))

@section('content')
    <section class="grid gap-6">
        <div class="app-surface p-5">
            <form method="GET" action="{{ route('admin.finance-summary') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs"><label for="month" class="text-sm font-medium text-shell-text">{{ __('finance.reporting_month') }}</label><input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}" class="app-input mt-2" required></div>
                <button type="submit" class="app-button-secondary">{{ __('messages.view_month') }}</button>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.monthly_charges') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['charges']) }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.charges_include_adjustments') }}</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.validated_payments') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['validated_payments']) }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.draft_payments_excluded') }}</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.outstanding_debt') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['outstanding_debt']) }}</p><p class="mt-2 text-sm text-shell-muted">{{ trans_choice('finance.students_with_debt', $summary['students_with_debt'], ['count' => $summary['students_with_debt']]) }}</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.opening_debt') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['opening_balance']) }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.debt_carried_into', ['month' => \App\Support\LocalizedFormat::month($month)]) }}</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.student_credit') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['student_credit']) }}</p><p class="mt-2 text-sm text-shell-muted">{{ __('finance.credit_explanation') }}</p></article>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <article class="app-surface p-5"><div class="flex items-end justify-between gap-4"><div><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.staff_salaries') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['validated_salaries']) }}</p></div><span class="app-badge-active">{{ __('finance.validated') }}</span></div><p class="mt-4 text-sm text-shell-muted">{{ __('finance.draft_salary_waiting') }} <span class="font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['draft_salaries']) }}</span></p></article>
            <article class="app-surface p-5"><div class="flex items-end justify-between gap-4"><div><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.manual_expenses') }}</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['validated_manual_expenses']) }}</p></div><span class="app-badge-active">{{ __('finance.validated') }}</span></div><p class="mt-4 text-sm text-shell-muted">{{ __('finance.draft_expense_waiting') }} <span class="font-semibold text-shell-text">{{ \App\Support\LocalizedFormat::number($summary['draft_manual_expenses']) }}</span></p></article>
        </div>

        <article class="app-surface overflow-hidden">
            <div class="border-b border-shell-border px-5 py-5"><h3 class="text-lg font-semibold text-shell-text">{{ __('finance.student_debt_ledger') }}</h3><p class="mt-1 text-sm text-shell-muted">{{ __('finance.debt_formula') }}</p></div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-brand-950/40 text-xs uppercase tracking-[0.16em] text-shell-muted"><tr><th class="px-5 py-3">{{ __('finance.student') }}</th><th class="px-5 py-3">{{ __('finance.teacher') }}</th><th class="px-5 py-3">{{ __('finance.opening') }}</th><th class="px-5 py-3">{{ __('finance.charges') }}</th><th class="px-5 py-3">{{ __('finance.payments') }}</th><th class="px-5 py-3">{{ __('finance.closing') }}</th></tr></thead>
                    <tbody class="divide-y divide-shell-border">
                        @forelse($summary['student_rows'] as $row)
                            @php($student = $row['student_month']->student)
                            <tr><td class="px-5 py-4 font-medium text-shell-text">{{ trim($student->first_name.' '.$student->family_name) }}</td><td class="px-5 py-4 text-shell-muted">{{ $student->teacher ? trim($student->teacher->first_name.' '.$student->teacher->family_name) : __('finance.unassigned') }}</td><td class="px-5 py-4">{{ \App\Support\LocalizedFormat::number($row['student_month']->opening_balance) }}</td><td class="px-5 py-4">{{ \App\Support\LocalizedFormat::number($row['charges']) }}</td><td class="px-5 py-4">{{ \App\Support\LocalizedFormat::number($row['payments']) }}</td><td class="px-5 py-4 font-semibold {{ $row['balance'] > 0 ? 'text-amber-300' : 'text-shell-text' }}">{{ \App\Support\LocalizedFormat::number($row['balance']) }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-shell-muted">{{ __('finance.no_student_finance_rows') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
