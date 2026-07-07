{{-- MICS HUB Blade view: monthly student-charge review and navigation. --}}
@extends('layouts.app')

@section('title', 'Student Charges | MICS HUB')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', 'Student Charge Review')
@section('page-description', 'Review closed-month charges, apply auditable adjustments, and validate final records.')

@section('content')
    <section class="app-surface p-6">
        <form method="GET" class="flex items-end gap-3">
            <div>
                <label class="text-sm text-shell-muted">Month</label>
                <input name="month" type="month" value="{{ $month }}" class="app-input mt-2">
            </div>
            <button class="app-button-secondary">View</button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Charge</th>
                        <th class="px-4 py-3">Adjustment</th>
                        <th class="px-4 py-3">Final</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
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
                                    {{ $row->status->value }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <a class="app-button-secondary px-3 py-2" href="{{ route('admin.student-charges.edit', $row) }}">
                                    {{ $row->status === \App\Enums\ReviewStatus::Draft ? 'Review' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-shell-muted">No monthly charges found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $studentMonths->links() }}</div>
    </section>
@endsection
