{{-- MICS Blade view: admin payments edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Review Payment | MICS')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', $payment->isReversal() ? 'Payment Reversal' : ($payment->status === \App\Enums\ReviewStatus::Draft ? 'Review Payment Draft' : 'Validated Payment'))
@section('page-description', 'Validated records stay immutable. Mistakes are corrected with a linked reversal and, when needed, a new replacement payment.')
@section('content')
    <section class="grid gap-6">
        @if($payment->status === \App\Enums\ReviewStatus::Validated)<div class="app-surface-strong p-5 text-sm text-shell-muted">Validated {{ $payment->validated_at?->format('d M Y H:i') }} by <span class="font-semibold text-shell-text">{{ $payment->validatedBy?->username ?? 'deleted user' }}</span>.</div>@endif
        @if($payment->isReversal())<div class="app-flash-error">This record reverses <a class="font-semibold underline" href="{{ route('admin.payments.edit', $payment->reversalOf) }}">payment #{{ $payment->reversal_of_payment_id }}</a>. Reason: {{ $payment->note }}</div>@endif
        @if($payment->reversal)<div class="app-flash-error">This payment was reversed by <a class="font-semibold underline" href="{{ route('admin.payments.edit', $payment->reversal) }}">record #{{ $payment->reversal->id }}</a>.</div>@endif
        <div class="app-surface p-6">
            <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="grid gap-6">@csrf @method('PUT') @include('admin.payments.partials.form')
                <div class="flex flex-wrap gap-3">@if($payment->status === \App\Enums\ReviewStatus::Draft)<button class="app-button-secondary">Save Draft</button>@endif<a href="{{ route('admin.payments.index', ['month' => $selectedMonth]) }}" class="app-button-secondary">Back</a></div>
            </form>
        </div>
        @if($payment->status === \App\Enums\ReviewStatus::Draft)
            <div class="app-surface flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"><p class="max-w-2xl text-sm text-shell-muted">Confirm the bank, cash, or card evidence first. Validation immediately makes this amount count against student debt.</p><div class="flex gap-3"><form method="POST" action="{{ route('admin.payments.validate', $payment) }}">@csrf<button class="app-button-primary">Validate Payment</button></form><form method="POST" action="{{ route('admin.payments.destroy', $payment) }}">@csrf @method('DELETE')<button class="app-button-danger">Delete Draft</button></form></div></div>
        @endif
        @if($payment->status === \App\Enums\ReviewStatus::Validated && ! $payment->isReversal() && ! $payment->reversal)
            <div class="app-surface grid gap-4 p-5"><div><p class="font-semibold text-shell-text">Correct a validated payment</p><p class="mt-2 max-w-2xl text-sm text-shell-muted">Reversal restores the full amount to student debt and cannot be undone. Enter the correction reason, then record a replacement payment if necessary.</p></div><form method="POST" action="{{ route('admin.payments.reverse', $payment) }}" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">@csrf<div><label for="reason" class="text-sm text-shell-muted">Required reversal reason</label><textarea id="reason" name="reason" class="app-input mt-2" rows="3" minlength="10" required></textarea></div><button class="app-button-danger">Reverse Payment</button></form></div>
        @endif
    </section>
@endsection
