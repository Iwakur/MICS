{{-- MICS Blade view: admin month-closing index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Month Closing | MICS')
@section('eyebrow', 'Administrator · Billing')
@section('page-title', 'Manual Month Closing')
@section('page-description', 'Review the selected month, then generate student charges, salary drafts, and next-month opening balances in one transaction.')

@section('content')
    @php($isClosed = $billingMonth?->status === \App\Enums\BillingMonthStatus::Closed)

    <section class="grid gap-6">
        <div class="app-surface p-5">
            <form method="GET" action="{{ route('admin.month-closing.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs">
                    <label for="month" class="text-sm font-medium text-shell-text">Billing month</label>
                    <input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}" class="app-input mt-2" required>
                </div>
                <button type="submit" class="app-button-secondary">Preview Month</button>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="app-surface p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Student Charges</p>
                <p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($preview['student_total'], 2) }}</p>
                <p class="mt-2 text-sm text-shell-muted">{{ $preview['students']->count() }} students included</p>
            </article>
            <article class="app-surface p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Salary Drafts</p>
                <p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($preview['salary_total'], 2) }}</p>
                <p class="mt-2 text-sm text-shell-muted">{{ $preview['salaries']->count() }} active staff included</p>
            </article>
            <article class="app-surface p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-shell-muted">Month Status</p>
                <span class="mt-3 {{ $isClosed ? 'app-badge-inactive' : 'app-badge-active' }}">{{ $isClosed ? 'Closed' : 'Open' }}</span>
                @if ($isClosed)<p class="mt-3 text-sm text-shell-muted">Closed {{ $billingMonth->closed_at?->format('d M Y H:i') }} by {{ $billingMonth->closedBy?->username ?? 'deleted user' }}</p>@endif
            </article>
        </div>

        <article class="app-surface overflow-hidden">
            <div class="border-b border-shell-border px-5 py-5">
                <h3 class="text-lg font-semibold text-shell-text">Student Charge Preview</h3>
                <p class="mt-1 text-sm text-shell-muted">Discounts reduce the school charge but do not reduce the teacher earning.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-brand-950/40 text-xs uppercase tracking-[0.16em] text-shell-muted"><tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Source</th><th class="px-5 py-3">Units</th><th class="px-5 py-3">Gross</th><th class="px-5 py-3">Discount</th><th class="px-5 py-3">Charge</th></tr></thead>
                    <tbody class="divide-y divide-shell-border">
                        @forelse ($preview['students'] as $item)
                            <tr><td class="px-5 py-4 font-medium text-shell-text">{{ trim($item['student']->first_name.' '.$item['student']->family_name) }}</td><td class="px-5 py-4 text-shell-muted">{{ $item['description'] }}</td><td class="px-5 py-4">{{ $item['units'] }}</td><td class="px-5 py-4">{{ number_format($item['gross_charge'], 2) }}</td><td class="px-5 py-4">{{ number_format($item['discount'], 2) }}</td><td class="px-5 py-4 font-semibold">{{ number_format($item['charge'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-shell-muted">No active billable students for this month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="app-surface overflow-hidden">
            <div class="border-b border-shell-border px-5 py-5"><h3 class="text-lg font-semibold text-shell-text">Salary Draft Preview</h3></div>
            <div class="divide-y divide-shell-border">
                @foreach ($preview['salaries'] as $salary)
                    <details class="group px-5 py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
                            <div><p class="font-medium text-shell-text">{{ trim($salary['staff']->first_name.' '.$salary['staff']->family_name) }}</p><p class="mt-1 text-xs uppercase tracking-[0.16em] text-shell-muted">{{ $salary['staff']->compensation_mode->value }} · {{ $salary['sources']->count() }} sources</p></div>
                            <p class="text-lg font-semibold text-shell-text">{{ number_format($salary['amount'], 2) }}</p>
                        </summary>
                        <div class="mt-4 grid gap-2">
                            @foreach ($salary['sources'] as $source)
                                <div class="app-surface-strong flex flex-col gap-2 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between"><span class="text-shell-muted">{{ $source['student'] ? trim($source['student']->first_name.' '.$source['student']->family_name).' · ' : '' }}{{ $source['description'] }}</span><span>{{ $source['units'] }} × {{ number_format($source['rate'], 2) }} = {{ number_format($source['amount'], 2) }}</span></div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </article>

        <div class="app-surface flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="max-w-2xl text-sm text-shell-muted">Closing locks lesson counts and snapshots current catalog rates. Review counts and assignments before continuing.</p>
            @if (! $isClosed)
                <form method="POST" action="{{ route('admin.month-closing.store') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                    <button type="submit" class="app-button-primary">Close Month and Generate Drafts</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.month-closing.reopen') }}" class="grid w-full gap-3 sm:max-w-xl">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                    <label for="reason" class="text-sm font-medium text-shell-text">Reason for reopening</label>
                    <textarea id="reason" name="reason" class="app-input" rows="3" minlength="10" required placeholder="Describe the correction that requires reopening."></textarea>
                    <button type="submit" class="app-button-danger justify-self-start">Reopen Month</button>
                </form>
            @endif
        </div>

        @if($billingMonth?->events->isNotEmpty())
            <article class="app-surface overflow-hidden">
                <div class="border-b border-shell-border px-5 py-5"><h3 class="text-lg font-semibold text-shell-text">Lifecycle Audit</h3><p class="mt-1 text-sm text-shell-muted">Every close and reopen action is retained with administrator attribution.</p></div>
                <div class="divide-y divide-shell-border">
                    @foreach($billingMonth->events as $event)
                        <div class="grid gap-2 px-5 py-4 sm:grid-cols-[8rem_1fr_auto] sm:items-center"><span class="{{ $event->action === 'closed' ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $event->action }}</span><div><p class="text-sm text-shell-text">{{ $event->reason ?: 'Month closed and drafts generated.' }}</p><p class="mt-1 text-xs text-shell-muted">{{ $event->user?->username ?? 'deleted user' }}</p></div><time class="text-xs text-shell-muted">{{ $event->occurred_at->format('d M Y H:i') }}</time></div>
                    @endforeach
                </div>
            </article>
        @endif
    </section>
@endsection
