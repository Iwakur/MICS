{{-- MICS HUB Blade view: admin month-closing index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', __('messages.month_closing').' | MICS HUB')
@section('eyebrow', __('messages.admin_billing'))
@section('page-title', __('messages.draft_generation_title'))
@section('page-description', __('messages.draft_generation_description'))

@section('content')
    @php($isClosed = $billingMonth?->status === \App\Enums\BillingMonthStatus::Closed)

    <section class="grid gap-6">
        <div class="app-surface p-5">
            <form method="GET" action="{{ route('admin.month-closing.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs">
                    <label for="month" class="text-sm font-medium text-shell-text">{{ __('messages.billing_month') }}</label>
                    <input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}" class="app-input mt-2" required>
                </div>
                <button type="submit" class="app-button-secondary">{{ __('finance.preview_month') }}</button>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="app-surface p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('messages.student_charges') }}</p>
                <p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($preview['student_total'], 2) }}</p>
                <p class="mt-2 text-sm text-shell-muted">{{ trans_choice('finance.students_included', $preview['students']->count(), ['count' => $preview['students']->count()]) }}</p>
            </article>
            <article class="app-surface p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('finance.salary_drafts') }}</p>
                <p class="mt-2 text-3xl font-semibold text-shell-text">{{ number_format($preview['salary_total'], 2) }}</p>
                <p class="mt-2 text-sm text-shell-muted">{{ trans_choice('finance.staff_included', $preview['salaries']->count(), ['count' => $preview['salaries']->count()]) }}</p>
            </article>
            <article class="app-surface p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('messages.input_status') }}</p>
                <span class="mt-3 {{ $isClosed ? 'app-badge-inactive' : 'app-badge-active' }}">{{ __('messages.'.($isClosed ? 'inputs_locked' : 'inputs_editable')) }}</span>
                @if ($isClosed)<p class="mt-3 text-sm text-shell-muted">{{ __('messages.inputs_locked_at', ['date' => \App\Support\LocalizedFormat::dateTime($billingMonth->closed_at), 'user' => $billingMonth->closedBy?->username ?? 'deleted user']) }}</p>@endif
            </article>
        </div>

        <article class="app-surface overflow-hidden">
            <div class="border-b border-shell-border px-5 py-5">
                <h3 class="text-lg font-semibold text-shell-text">{{ __('finance.student_charge_preview') }}</h3>
                <p class="mt-1 text-sm text-shell-muted">{{ __('finance.discount_preview_help') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-brand-950/40 text-xs uppercase tracking-[0.16em] text-shell-muted"><tr><th class="px-5 py-3">{{ __('finance.student') }}</th><th class="px-5 py-3">{{ __('finance.source') }}</th><th class="px-5 py-3">{{ __('finance.units') }}</th><th class="px-5 py-3">{{ __('finance.gross') }}</th><th class="px-5 py-3">{{ __('finance.discount') }}</th><th class="px-5 py-3">{{ __('finance.charge') }}</th></tr></thead>
                    <tbody class="divide-y divide-shell-border">
                        @forelse ($preview['students'] as $item)
                            <tr><td class="px-5 py-4 font-medium text-shell-text">{{ trim($item['student']->first_name.' '.$item['student']->family_name) }}</td><td class="px-5 py-4 text-shell-muted">{{ $item['description'] }}</td><td class="px-5 py-4">{{ $item['units'] }}</td><td class="px-5 py-4">{{ number_format($item['gross_charge'], 2) }}</td><td class="px-5 py-4">{{ number_format($item['discount'], 2) }}</td><td class="px-5 py-4 font-semibold">{{ number_format($item['charge'], 2) }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-shell-muted">{{ __('finance.no_billable_students') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="app-surface overflow-hidden">
            <div class="border-b border-shell-border px-5 py-5"><h3 class="text-lg font-semibold text-shell-text">{{ __('finance.salary_draft_preview') }}</h3></div>
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
            <div class="grid max-w-2xl gap-3 text-sm text-shell-muted">
                <p>{{ __('messages.lock_inputs_description') }}</p>
                @if (! $isClosed)
                    <div class="rounded-2xl border border-brand-500/40 bg-brand-950/40 p-4">
                        <p class="font-semibold text-shell-text">{{ trans_choice('messages.months_affected', $affectedMonths->count(), ['count' => $affectedMonths->count()]) }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($affectedMonths as $affectedMonth)
                                <span class="app-badge-teacher">{{ \App\Support\LocalizedFormat::month($affectedMonth) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @if (! $isClosed)
                <form method="POST" action="{{ route('admin.month-closing.store') }}" data-confirm="{{ trans_choice('finance.confirm_generate_months', $affectedMonths->count(), ['count' => $affectedMonths->count()]) }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                    <button type="submit" class="app-button-primary">{{ __('messages.generate_drafts_and_lock') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.month-closing.reopen') }}" class="grid w-full gap-3 sm:max-w-xl" data-confirm="{{ __('finance.confirm_unlock_inputs') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                    <label for="reason" class="text-sm font-medium text-shell-text">{{ __('messages.reason_for_unlocking') }}</label>
                    <textarea id="reason" name="reason" class="app-input" rows="3" minlength="10" required placeholder="{{ __('messages.unlock_inputs_placeholder') }}"></textarea>
                    <button type="submit" class="app-button-danger justify-self-start">{{ __('messages.unlock_inputs') }}</button>
                </form>
            @endif
        </div>

        @if($billingMonth?->events->isNotEmpty())
            <article class="app-surface overflow-hidden">
                <div class="border-b border-shell-border px-5 py-5"><h3 class="text-lg font-semibold text-shell-text">{{ __('finance.lifecycle_audit') }}</h3><p class="mt-1 text-sm text-shell-muted">{{ __('finance.lifecycle_audit_description') }}</p></div>
                <div class="divide-y divide-shell-border">
                    @foreach($billingMonth->events as $event)
                        <div class="grid gap-2 px-5 py-4 sm:grid-cols-[8rem_1fr_auto] sm:items-center"><span class="{{ $event->action === 'closed' ? 'app-badge-active' : 'app-badge-inactive' }}">{{ __('messages.'.($event->action === 'closed' ? 'drafts_generated' : 'inputs_unlocked')) }}</span><div><p class="text-sm text-shell-text">{{ $event->reason ?: __('messages.drafts_generated_event') }}</p><p class="mt-1 text-xs text-shell-muted">{{ $event->user?->username ?? 'deleted user' }}</p></div><time class="text-xs text-shell-muted">{{ \App\Support\LocalizedFormat::dateTime($event->occurred_at) }}</time></div>
                    @endforeach
                </div>
            </article>
        @endif
    </section>
@endsection
