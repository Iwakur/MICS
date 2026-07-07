{{-- MICS HUB Blade view: teacher students index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', __('messages.my_students').' | MICS HUB')
@section('eyebrow', __('messages.teacher'))
@section('page-title', __('messages.my_students'))
@section('page-description', __('messages.my_students_description'))
@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h3 class="text-xl font-semibold text-shell-text">{{ __('messages.assigned_students') }}</h3><p class="mt-2 text-sm text-shell-muted">{{ __('messages.students_in_scope', ['count' => $students->count()]) }}</p></div><a href="{{ route('teacher.students.create') }}" class="app-button-primary self-start sm:self-auto">{{ __('messages.add_student') }}</a></div>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($students as $student)
                <article class="app-surface-strong p-5"><div class="flex items-start justify-between gap-4"><div><h4 class="font-semibold text-shell-text">{{ trim($student->first_name.' '.$student->family_name) }}</h4><p class="mt-1 text-sm text-shell-muted">{{ $student->lessonType?->name ?? $student->plan?->name }}</p></div><span class="{{ $student->status->value === 'active' ? 'app-badge-active' : 'app-badge-inactive' }}">{{ __('messages.'.$student->status->value) }}</span></div><p class="mt-4 text-xs uppercase tracking-[0.2em] text-shell-muted">{{ __('messages.'.($student->billing_type->value === 'per_lesson' ? 'per_lesson_label' : 'monthly_plan')) }}</p><a href="{{ route('teacher.students.edit', $student) }}" class="app-button-secondary mt-4 inline-flex">{{ __('messages.edit_student') }}</a></article>
            @empty
                <p class="text-sm text-shell-muted">{{ __('messages.no_assigned_students') }}</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $students->links() }}</div>
    </section>
@endsection
