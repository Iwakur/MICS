{{-- MICS HUB Blade view: teacher dashboard. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', __('messages.teacher_dashboard').' | MICS HUB')
@section('eyebrow', __('messages.teacher'))
@section('page-title', __('messages.teacher_dashboard'))
@section('page-description', __('messages.teacher_dashboard_description'))

@section('content')
    <section>
        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">{{ __('messages.welcome_back') }}</h3>
            <p class="mt-3 max-w-2xl text-sm text-shell-muted">
                {{ __('messages.teacher_scope_summary') }}
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">{{ __('messages.active_students') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-shell-text">{{ $activeStudents }}</p>
                </div>

                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">{{ __('messages.per_lesson') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-shell-text">{{ $perLessonStudents }}</p>
                </div>

                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">{{ __('messages.monthly_plans') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-shell-text">{{ $planStudents }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('teacher.lesson-counts.index') }}" class="app-button-primary">{{ __('messages.enter_lesson_counts') }}</a>
                <a href="{{ route('teacher.students.index') }}" class="app-button-secondary">{{ __('messages.open_my_students') }}</a>
            </div>
        </article>
    </section>
@endsection
