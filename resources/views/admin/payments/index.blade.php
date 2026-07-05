{{-- MICS Blade view: admin payments index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Student Payments | MICS')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', 'Student Payments')
@section('page-description', 'Record payment evidence as a draft, then validate it before it reduces student debt.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form method="GET" class="flex items-end gap-3">
                <div><label for="month" class="text-sm text-shell-muted">Billing month</label><input id="month" name="month" type="month" value="{{ $month }}" class="app-input mt-2" required></div>
                <button class="app-button-secondary">Filter</button>
            </form>
            <a href="{{ route('admin.payments.create', ['month' => $month]) }}" class="app-button-primary">Record Payment</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted"><tr><th class="px-4 py-3">Student</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Paid</th><th class="px-4 py-3">Method</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Action</th></tr></thead>
                <tbody class="divide-y divide-shell-border">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-4 font-medium text-shell-text">{{ trim($payment->studentMonth->student->first_name.' '.$payment->studentMonth->student->family_name) }}</td>
                            <td class="px-4 py-4"><span class="{{ $payment->isReversal() ? 'app-badge-inactive' : 'app-badge-teacher' }}">{{ $payment->isReversal() ? 'refund' : ($payment->refunds->isNotEmpty() ? 'partly refunded' : 'payment') }}</span></td>
                            <td class="px-4 py-4 text-shell-muted">{{ $payment->paid_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-4">{{ str($payment->payment_method)->replace('_', ' ')->title() }}</td>
                            <td class="px-4 py-4 font-semibold">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-4 py-4"><span class="{{ $payment->status === \App\Enums\ReviewStatus::Validated ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $payment->status->value }}</span></td>
                            <td class="px-4 py-4"><a href="{{ route('admin.payments.edit', $payment) }}" class="app-button-secondary px-3 py-2">{{ $payment->status === \App\Enums\ReviewStatus::Draft ? 'Review' : 'View' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-shell-muted">No payments recorded for this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $payments->links() }}</div>
    </section>
@endsection
