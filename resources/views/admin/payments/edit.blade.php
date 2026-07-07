{{-- MICS HUB Blade view: admin payments edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', __('finance.review_payment').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', $payment->isReversal() ? __('finance.payment_refund') : ($payment->status === \App\Enums\ReviewStatus::Draft ? __('finance.review_payment_draft') : __('finance.validated_payment')))
@section('page-description', __('finance.payment_review_description'))
@section('content')
    <section class="grid gap-6">
        @if($payment->status === \App\Enums\ReviewStatus::Validated)<div class="app-surface-strong p-5 text-sm text-shell-muted">{{ __('finance.validated_by_at', ['date' => \App\Support\LocalizedFormat::dateTime($payment->validated_at), 'user' => $payment->validatedBy?->username ?? __('finance.deleted_user')]) }}</div>@endif
        @if($payment->isReversal())<div class="app-flash-error">{{ __('finance.refund_record_notice') }} <a class="font-semibold underline" href="{{ route('admin.payments.edit', $payment->reversalOf) }}">#{{ $payment->reversal_of_payment_id }}</a>. {{ __('finance.variance_reason') }}: {{ $payment->note }}</div>@endif
        @if(!$payment->isReversal() && $payment->refunds->isNotEmpty())<div class="app-flash-error">{{ __('finance.refund_summary', ['refunded' => \App\Support\LocalizedFormat::number($payment->refundedCents() / 100), 'remaining' => \App\Support\LocalizedFormat::number($payment->refundableCents() / 100)]) }}</div>@endif
        <div class="app-surface p-6">
            <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="grid gap-6">@csrf @method('PUT') @include('admin.payments.partials.form')
                <div class="flex flex-wrap gap-3">@if($payment->status === \App\Enums\ReviewStatus::Draft)<button type="submit" class="app-button-secondary">{{ __('finance.save_draft') }}</button>@endif<a href="{{ route('admin.payments.index', ['month' => $selectedMonth]) }}" class="app-button-secondary">{{ __('finance.back') }}</a></div>
            </form>
        </div>
        @if($payment->status === \App\Enums\ReviewStatus::Draft)
            <div class="app-surface flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"><p class="max-w-2xl text-sm text-shell-muted">{{ __('finance.validate_payment_help') }}</p><div class="flex gap-3"><form method="POST" action="{{ route('admin.payments.validate', $payment) }}">@csrf<button type="submit" class="app-button-primary">{{ __('finance.validate_payment') }}</button></form><form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" data-confirm="{{ __('finance.confirm_delete_draft') }}">@csrf @method('DELETE')<button type="submit" class="app-button-danger">{{ __('finance.delete_draft') }}</button></form></div></div>
        @endif
        @if($payment->status === \App\Enums\ReviewStatus::Validated && ! $payment->isReversal() && $payment->refundableCents() > 0)
            <div class="app-surface grid gap-4 p-5"><div><p class="font-semibold text-shell-text">{{ __('finance.refund_validated_payment') }}</p><p class="mt-2 max-w-2xl text-sm text-shell-muted">{{ __('finance.refund_help', ['amount' => \App\Support\LocalizedFormat::number($payment->refundableCents() / 100)]) }}</p></div><form method="POST" action="{{ route('admin.payments.reverse', $payment) }}" class="grid gap-3 lg:grid-cols-[12rem_1fr_auto] lg:items-end" data-confirm="{{ __('finance.confirm_refund') }}">@csrf<div><label for="amount" class="text-sm text-shell-muted">{{ __('finance.refund_amount') }}</label><input id="amount" name="amount" type="number" min="0.01" max="{{ number_format($payment->refundableCents() / 100, 2, '.', '') }}" step="0.01" class="app-input mt-2" required></div><div><label for="reason" class="text-sm text-shell-muted">{{ __('finance.required_refund_reason') }}</label><textarea id="reason" name="reason" class="app-input mt-2" rows="3" minlength="10" required></textarea></div><button type="submit" class="app-button-danger">{{ __('finance.record_refund') }}</button></form></div>
        @endif
    </section>
@endsection
