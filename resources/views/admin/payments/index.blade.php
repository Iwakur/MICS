{{-- MICS HUB Blade view: admin payments index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', __('messages.student_payments').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('messages.student_payments'))
@section('page-description', __('finance.payments_page_description'))

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form method="GET" class="flex items-end gap-3">
                <div><label for="month" class="text-sm text-shell-muted">{{ __('messages.billing_month') }}</label><input id="month" name="month" type="month" value="{{ $month }}" class="app-input mt-2" required></div>
                <button type="submit" class="app-button-secondary">{{ __('finance.filter') }}</button>
            </form>
            <a href="{{ route('admin.payments.create', ['month' => $month]) }}" class="app-button-primary">{{ __('finance.record_payment') }}</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted"><tr><th class="px-4 py-3">{{ __('finance.student') }}</th><th class="px-4 py-3">{{ __('finance.type') }}</th><th class="px-4 py-3">{{ __('finance.paid') }}</th><th class="px-4 py-3">{{ __('finance.method') }}</th><th class="px-4 py-3">{{ __('finance.amount') }}</th><th class="px-4 py-3">{{ __('messages.status') }}</th><th class="px-4 py-3">{{ __('finance.action') }}</th></tr></thead>
                <tbody class="divide-y divide-shell-border">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-4 font-medium text-shell-text">{{ trim($payment->studentMonth->student->first_name.' '.$payment->studentMonth->student->family_name) }}</td>
                            <td class="px-4 py-4"><span class="{{ $payment->isReversal() ? 'app-badge-inactive' : 'app-badge-teacher' }}">{{ __('finance.'.($payment->isReversal() ? 'refund' : ($payment->refunds->isNotEmpty() ? 'partly_refunded' : 'payment'))) }}</span></td>
                            <td class="px-4 py-4 text-shell-muted">{{ \App\Support\LocalizedFormat::dateTime($payment->paid_at) }}</td>
                            <td class="px-4 py-4">{{ __('finance.payment_method_'.$payment->payment_method) }}</td>
                            <td class="px-4 py-4 font-semibold">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-4 py-4"><span class="{{ $payment->status === \App\Enums\ReviewStatus::Validated ? 'app-badge-active' : 'app-badge-inactive' }}">{{ __('finance.'.$payment->status->value) }}</span></td>
                            <td class="px-4 py-4"><a href="{{ route('admin.payments.edit', $payment) }}" class="app-button-secondary px-3 py-2">{{ $payment->status === \App\Enums\ReviewStatus::Draft ? __('finance.review') : __('finance.view') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-shell-muted">{{ __('finance.no_payments') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $payments->links() }}</div>
    </section>
@endsection
