{{-- MICS HUB Blade view: admin payments edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Review Payment | MICS HUB')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', $payment->isReversal() ? 'Payment Refund' : ($payment->status === \App\Enums\ReviewStatus::Draft ? 'Review Payment Draft' : 'Validated Payment'))
@section('page-description', 'Validated records stay immutable. Partial or full refunds are stored as linked negative payments.')
@section('content')
    <section class="grid gap-6">
        @if($payment->status === \App\Enums\ReviewStatus::Validated)<div class="app-surface-strong p-5 text-sm text-shell-muted">Validated {{ \App\Support\LocalizedFormat::dateTime($payment->validated_at) }} by <span class="font-semibold text-shell-text">{{ $payment->validatedBy?->username ?? 'deleted user' }}</span>.</div>@endif
        @if($payment->isReversal())<div class="app-flash-error">This record refunds part or all of <a class="font-semibold underline" href="{{ route('admin.payments.edit', $payment->reversalOf) }}">payment #{{ $payment->reversal_of_payment_id }}</a>. Reason: {{ $payment->note }}</div>@endif
        @if(!$payment->isReversal() && $payment->refunds->isNotEmpty())<div class="app-flash-error">Refunded {{ number_format($payment->refundedCents() / 100, 2) }}; {{ number_format($payment->refundableCents() / 100, 2) }} remains refundable.</div>@endif
        <div class="app-surface p-6">
            <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="grid gap-6">@csrf @method('PUT') @include('admin.payments.partials.form')
                <div class="flex flex-wrap gap-3">@if($payment->status === \App\Enums\ReviewStatus::Draft)<button class="app-button-secondary">Save Draft</button>@endif<a href="{{ route('admin.payments.index', ['month' => $selectedMonth]) }}" class="app-button-secondary">Back</a></div>
            </form>
        </div>
        @if($payment->status === \App\Enums\ReviewStatus::Draft)
            <div class="app-surface flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"><p class="max-w-2xl text-sm text-shell-muted">Confirm the bank, cash, or card evidence first. Validation immediately makes this amount count against student debt.</p><div class="flex gap-3"><form method="POST" action="{{ route('admin.payments.validate', $payment) }}">@csrf<button class="app-button-primary">Validate Payment</button></form><form method="POST" action="{{ route('admin.payments.destroy', $payment) }}">@csrf @method('DELETE')<button class="app-button-danger">Delete Draft</button></form></div></div>
        @endif
        @if($payment->status === \App\Enums\ReviewStatus::Validated && ! $payment->isReversal() && $payment->refundableCents() > 0)
            <div class="app-surface grid gap-4 p-5"><div><p class="font-semibold text-shell-text">Refund a validated payment</p><p class="mt-2 max-w-2xl text-sm text-shell-muted">Refunds are immutable and restore the refunded amount to student debt. Remaining refundable: {{ number_format($payment->refundableCents() / 100, 2) }}.</p></div><form method="POST" action="{{ route('admin.payments.reverse', $payment) }}" class="grid gap-3 lg:grid-cols-[12rem_1fr_auto] lg:items-end">@csrf<div><label for="amount" class="text-sm text-shell-muted">Refund amount</label><input id="amount" name="amount" type="number" min="0.01" max="{{ number_format($payment->refundableCents() / 100, 2, '.', '') }}" step="0.01" class="app-input mt-2" required></div><div><label for="reason" class="text-sm text-shell-muted">Required refund reason</label><textarea id="reason" name="reason" class="app-input mt-2" rows="3" minlength="10" required></textarea></div><button class="app-button-danger">Record Refund</button></form></div>
        @endif
    </section>
@endsection
