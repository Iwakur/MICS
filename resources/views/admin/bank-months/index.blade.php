@extends('layouts.app')

@section('title', __('messages.bank_reconciliation').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('messages.bank_reconciliation'))
@section('page-description', __('finance.bank_page_description'))

@section('content')
    <section class="grid gap-6">
        <div class="app-surface p-6">
            <form method="GET" class="flex items-end gap-3">
                <div>
                    <label for="month" class="text-sm text-shell-muted">{{ __('finance.month') }}</label>
                    <input id="month" type="month" name="month" value="{{ $month }}" class="app-input mt-2" required>
                </div>
                <button type="submit" class="app-button-secondary">{{ __('finance.load') }}</button>
            </form>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.opening') }}</dt><dd class="mt-2 text-xl font-semibold">{{ \App\Support\LocalizedFormat::number($totals['opening']) }}</dd></div>
                <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.validated_receipts') }}</dt><dd class="mt-2 text-xl font-semibold">{{ \App\Support\LocalizedFormat::number($totals['receipts']) }}</dd></div>
                <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.validated_expenses') }}</dt><dd class="mt-2 text-xl font-semibold">{{ \App\Support\LocalizedFormat::number($totals['expenses']) }}</dd></div>
                <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.expected_close') }}</dt><dd class="mt-2 text-xl font-semibold">{{ \App\Support\LocalizedFormat::number($totals['expected']) }}</dd></div>
            </dl>
        </div>

        @if (! $bankMonth || $bankMonth->status === 'draft')
            <div class="app-surface p-6">
                <form method="POST" action="{{ route('admin.bank-months.store') }}" class="grid gap-5">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div><label for="closing_balance" class="text-sm text-shell-muted">{{ __('finance.actual_closing_balance') }}</label><input id="closing_balance" name="closing_balance" type="number" step="0.01" value="{{ old('closing_balance') }}" class="app-input mt-2" required></div>
                    <div><label for="variance_reason" class="text-sm text-shell-muted">{{ __('finance.variance_reason_help') }}</label><textarea id="variance_reason" name="variance_reason" class="app-input mt-2" rows="3" minlength="10">{{ old('variance_reason') }}</textarea></div>
                    <div><label for="note" class="text-sm text-shell-muted">{{ __('messages.note') }}</label><textarea id="note" name="note" class="app-input mt-2" rows="3">{{ old('note') }}</textarea></div>
                    <button type="submit" class="app-button-primary justify-self-start">{{ __('finance.reconcile_month') }}</button>
                </form>
            </div>
        @else
            <div class="app-surface grid gap-5 p-6">
                <p class="font-semibold">{{ __('finance.reconciled_by_at', ['user' => $bankMonth->reconciledBy?->username ?? __('finance.deleted_user'), 'date' => \App\Support\LocalizedFormat::dateTime($bankMonth->reconciled_at)]) }}</p>
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.expected_close') }}</dt><dd class="mt-2 text-xl font-semibold">{{ \App\Support\LocalizedFormat::number($bankMonth->expected_closing_balance) }}</dd></div>
                    <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.actual_close') }}</dt><dd class="mt-2 text-xl font-semibold">{{ \App\Support\LocalizedFormat::number($bankMonth->closing_balance) }}</dd></div>
                    <div class="app-surface-strong p-4"><dt class="text-sm text-shell-muted">{{ __('finance.variance') }}</dt><dd class="mt-2 text-xl font-semibold {{ $variance != 0 ? 'text-amber-300' : 'text-shell-success' }}">{{ $variance > 0 ? '+' : '' }}{{ \App\Support\LocalizedFormat::number($variance) }}</dd></div>
                </dl>
                @if ($bankMonth->variance_reason)<p class="text-sm text-shell-muted"><span class="font-semibold text-shell-text">{{ __('finance.variance_reason') }}:</span> {{ $bankMonth->variance_reason }}</p>@endif
                <form method="POST" action="{{ route('admin.bank-months.reopen', $bankMonth) }}" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end" data-confirm="{{ __('finance.confirm_reopen_bank') }}">
                    @csrf
                    <div><label for="reason" class="text-sm text-shell-muted">{{ __('finance.reopen_reason') }}</label><textarea id="reason" name="reason" class="app-input mt-2" minlength="10" required></textarea></div>
                    <button type="submit" class="app-button-danger">{{ __('finance.reopen') }}</button>
                </form>
            </div>
        @endif

        @if ($bankMonth?->events->isNotEmpty())
            <div class="app-surface p-6">
                <h3 class="font-semibold">{{ __('finance.audit_history') }}</h3>
                <ul class="mt-4 grid gap-3">
                    @foreach ($bankMonth->events as $event)
                        <li class="app-surface-strong p-4 text-sm"><span class="font-semibold">{{ __('finance.bank_event_'.$event->action) }}</span> · {{ $event->user?->username ?? __('finance.deleted_user') }} · {{ \App\Support\LocalizedFormat::dateTime($event->occurred_at) }} @if($event->reason)<span class="text-shell-muted">— {{ $event->reason }}</span>@endif</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endsection
