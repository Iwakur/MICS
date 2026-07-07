{{-- MICS HUB Blade view: monthly student-charge review and navigation. --}}
@extends('layouts.app')

@section('title', __('messages.student_charges').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('finance.student_charge_review'))
@section('page-description', __('finance.charge_review_description'))

@section('content')
    <section class="app-surface p-6">
        <form method="GET" class="flex items-end gap-3">
            <div>
                <label for="charge-month" class="text-sm text-shell-muted">{{ __('finance.month') }}</label>
                <input id="charge-month" name="month" type="month" value="{{ $month }}" class="app-input mt-2">
            </div>
            <button type="submit" class="app-button-secondary">{{ __('finance.view') }}</button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3">{{ __('finance.student') }}</th>
                        <th class="px-4 py-3">{{ __('finance.charge') }}</th>
                        <th class="px-4 py-3">{{ __('finance.adjustment') }}</th>
                        <th class="px-4 py-3">{{ __('finance.final') }}</th>
                        <th class="px-4 py-3">{{ __('messages.status') }}</th>
                        <th class="px-4 py-3">{{ __('finance.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shell-border">
                    @forelse ($studentMonths as $row)
                        <tr>
                            <td class="px-4 py-4">{{ trim($row->student->first_name.' '.$row->student->family_name) }}</td>
                            <td class="px-4 py-4">{{ $row->charge_amount }}</td>
                            <td class="px-4 py-4">{{ $row->manual_adjustment }}</td>
                            <td class="px-4 py-4 font-semibold">{{ number_format($row->adjustedCharge(), 2) }}</td>
                            <td class="px-4 py-4">
                                <span class="{{ $row->status === \App\Enums\ReviewStatus::Validated ? 'app-badge-active' : 'app-badge-inactive' }}">
                                    {{ __('finance.'.$row->status->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <a class="app-button-secondary px-3 py-2" href="{{ route('admin.student-charges.edit', $row) }}">
                                    {{ $row->status === \App\Enums\ReviewStatus::Draft ? __('finance.review') : __('finance.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-shell-muted">{{ __('finance.no_monthly_charges') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $studentMonths->links() }}</div>
    </section>
@endsection
