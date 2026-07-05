{{-- MICS Blade view: lesson-counts index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Monthly Lesson Counts | MICS')
@section('eyebrow', $routePrefix === 'admin' ? 'Administrator · Billing' : 'Teacher · Monthly Input')
@section('page-title', 'Monthly Lesson Counts')
@section('page-description', 'Record completed lessons for active per-lesson students. These counts remain editable until an administrator closes the selected month.')

@section('content')
    @php($isClosed = $billingMonth?->status === \App\Enums\BillingMonthStatus::Closed)

    <section class="grid gap-6">
        <div class="app-surface p-5">
            <form method="GET" action="{{ route($routePrefix.'.lesson-counts.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs">
                    <label for="month" class="text-sm font-medium text-shell-text">Billing month</label>
                    <input id="month" name="month" type="month" value="{{ $month->format('Y-m') }}" class="app-input mt-2" required>
                </div>
                <button type="submit" class="app-button-secondary">View Month</button>
            </form>
        </div>

        <div class="app-surface overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-shell-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-shell-text">{{ $month->format('F Y') }}</h3>
                    <p class="mt-1 text-sm text-shell-muted">{{ $students->count() }} active per-lesson {{ Str::plural('student', $students->count()) }}</p>
                </div>
                <span class="{{ $isClosed ? 'app-badge-inactive' : 'app-badge-active' }}">{{ $isClosed ? 'Closed' : 'Open' }}</span>
            </div>

            @if ($students->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-shell-muted">No eligible students exist for this month.</div>
            @else
                <form method="POST" action="{{ route($routePrefix.'.lesson-counts.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-brand-950/40 text-xs uppercase tracking-[0.16em] text-shell-muted">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Student</th>
                                    @if ($routePrefix === 'admin')<th class="px-5 py-3 font-medium">Teacher</th>@endif
                                    <th class="px-5 py-3 font-medium">Lesson Type</th>
                                    <th class="px-5 py-3 font-medium">Lessons</th>
                                    <th class="px-5 py-3 font-medium">Charge Preview</th>
                                    <th class="px-5 py-3 font-medium">Teacher Preview</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-shell-border">
                                @foreach ($students as $student)
                                    @php($count = old('counts.'.$student->id, $student->months->first()?->lesson_count ?? 0))
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-shell-text">{{ trim($student->first_name.' '.$student->family_name) }}</td>
                                        @if ($routePrefix === 'admin')<td class="px-5 py-4 text-shell-muted">{{ trim($student->teacher->first_name.' '.$student->teacher->family_name) }}</td>@endif
                                        <td class="px-5 py-4 text-shell-muted">{{ $student->lessonType->name }}</td>
                                        <td class="px-5 py-4">
                                            <input name="counts[{{ $student->id }}]" type="number" min="0" max="999" value="{{ $count }}" class="app-input w-24" required @disabled($isClosed)>
                                        </td>
                                        <td class="px-5 py-4 text-shell-muted">{{ number_format((float) $student->lessonType->lesson_price * (int) $count, 2) }}</td>
                                        <td class="px-5 py-4 text-shell-muted">{{ number_format((float) $student->lessonType->teacher_share_per_lesson * (int) $count, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end border-t border-shell-border px-5 py-5">
                        @if ($isClosed)
                            <p class="text-sm text-shell-muted">Counts are locked because this month is closed.</p>
                        @else
                            <button type="submit" class="app-button-primary">Save Lesson Counts</button>
                        @endif
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
