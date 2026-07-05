{{-- MICS Blade view: admin finance-summary. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Finance Summary | MICS')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', 'Monthly Finance Summary')
@section('page-description', 'Compare charges, validated cash, outstanding student debt, salaries, and irregular expenses without mixing drafts into confirmed activity.')

@section('content')
    <section class="grid gap-6">
        <div class="app-surface p-5">
            <form method="GET" action="{{ route('admin.finance-summary') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs"><label for="month" class="text-sm font-medium text-shell-text">Reporting month</label><input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}" class="app-input mt-2" required></div>
                <button class="app-button-secondary">View Month</button>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Monthly Charges</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['charges'], 2) }}</p><p class="mt-2 text-sm text-shell-muted">Includes attributed charge adjustments.</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Validated Payments</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['validated_payments'], 2) }}</p><p class="mt-2 text-sm text-shell-muted">Draft payments are excluded.</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Outstanding Debt</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['outstanding_debt'], 2) }}</p><p class="mt-2 text-sm text-shell-muted">{{ $summary['students_with_debt'] }} students with a positive balance.</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Opening Debt</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['opening_balance'], 2) }}</p><p class="mt-2 text-sm text-shell-muted">Debt carried into {{ $month->format('F Y') }}.</p></article>
            <article class="app-surface p-5"><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Student Credit</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['student_credit'], 2) }}</p><p class="mt-2 text-sm text-shell-muted">Credit is shown separately and never hides another student's debt.</p></article>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <article class="app-surface p-5"><div class="flex items-end justify-between gap-4"><div><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Staff Salaries</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['validated_salaries'], 2) }}</p></div><span class="app-badge-active">validated</span></div><p class="mt-4 text-sm text-shell-muted">Draft salary amount waiting for review: <span class="font-semibold text-shell-text">{{ number_format($summary['draft_salaries'], 2) }}</span></p></article>
            <article class="app-surface p-5"><div class="flex items-end justify-between gap-4"><div><p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Manual Expenses</p><p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($summary['validated_manual_expenses'], 2) }}</p></div><span class="app-badge-active">validated</span></div><p class="mt-4 text-sm text-shell-muted">Draft manual amount waiting for review: <span class="font-semibold text-shell-text">{{ number_format($summary['draft_manual_expenses'], 2) }}</span></p></article>
        </div>

        <article class="app-surface overflow-hidden">
            <div class="border-b border-shell-border px-5 py-5"><h3 class="text-lg font-semibold text-shell-text">Student Debt Ledger</h3><p class="mt-1 text-sm text-shell-muted">Opening balance + monthly charges − validated payments = closing balance.</p></div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-brand-950/40 text-xs uppercase tracking-[0.16em] text-shell-muted"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Teacher</th><th class="px-5 py-3">Opening</th><th class="px-5 py-3">Charges</th><th class="px-5 py-3">Payments</th><th class="px-5 py-3">Closing</th></tr></thead>
                    <tbody class="divide-y divide-shell-border">
                        @forelse($summary['student_rows'] as $row)
                            @php($student = $row['student_month']->student)
                            <tr><td class="px-5 py-4 font-medium text-shell-text">{{ trim($student->first_name.' '.$student->family_name) }}</td><td class="px-5 py-4 text-shell-muted">{{ $student->teacher ? trim($student->teacher->first_name.' '.$student->teacher->family_name) : 'Unassigned' }}</td><td class="px-5 py-4">{{ number_format((float) $row['student_month']->opening_balance, 2) }}</td><td class="px-5 py-4">{{ number_format($row['charges'], 2) }}</td><td class="px-5 py-4">{{ number_format($row['payments'], 2) }}</td><td class="px-5 py-4 font-semibold {{ $row['balance'] > 0 ? 'text-amber-300' : 'text-shell-text' }}">{{ number_format($row['balance'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-shell-muted">No student finance rows exist for this month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
