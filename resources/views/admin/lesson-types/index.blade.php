@extends('layouts.app')

@section('title', 'Lesson Types | MICS')
@section('eyebrow', 'Administrator · Catalog')
@section('page-title', 'Lesson Types')
@section('page-description', 'Configure what students pay and what dynamic teachers earn for each completed lesson.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-shell-text">Per-Lesson Catalog</h3>
                <p class="mt-2 text-sm text-shell-muted">Historical monthly calculations will snapshot these rates when the month is closed.</p>
            </div>
            <a href="{{ route('admin.lesson-types.create') }}" class="app-button-primary">Create Lesson Type</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">Lesson</th>
                        <th class="px-4 py-3 font-medium">Student Price</th>
                        <th class="px-4 py-3 font-medium">Teacher Earning</th>
                        <th class="px-4 py-3 font-medium">Students</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shell-border bg-shell-panel/70 text-shell-text">
                    @forelse ($lessonTypes as $lessonType)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-semibold">{{ $lessonType->name }}</p>
                                <p class="mt-1 text-xs text-shell-muted">{{ $lessonType->duration_minutes }} minutes</p>
                            </td>
                            <td class="px-4 py-4">{{ number_format((float) $lessonType->lesson_price, 2) }}</td>
                            <td class="px-4 py-4">{{ number_format((float) $lessonType->teacher_share_per_lesson, 2) }}</td>
                            <td class="px-4 py-4 text-shell-muted">{{ $lessonType->students_count }}</td>
                            <td class="px-4 py-4"><span class="{{ $lessonType->is_assignable ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $lessonType->is_assignable ? 'assignable' : 'archived' }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.lesson-types.edit', $lessonType) }}" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">Edit</a>
                                    @if ($lessonType->is_assignable)
                                        <form method="POST" action="{{ route('admin.lesson-types.destroy', $lessonType) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="app-button-danger">Archive</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-shell-muted">No lesson types configured yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
