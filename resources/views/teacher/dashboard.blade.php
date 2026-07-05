@extends('layouts.app')

@section('title', 'Teacher Dashboard | MICS')
@section('eyebrow', 'Teacher')
@section('page-title', 'Teacher Dashboard')
@section('page-description', 'This dashboard shares the same shell as admin, but intentionally shows a smaller and more focused teacher workspace.')

@section('content')
    <section class="grid gap-4 lg:grid-cols-[2fr_1fr]">
        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Welcome back</h3>
            <p class="mt-3 max-w-2xl text-sm text-shell-muted">
                Teachers and administrators do not share the same control surface. This page keeps the same visual system, but narrows the visible scope.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">Active Students</p>
                    <p class="mt-2 text-3xl font-semibold text-shell-text">{{ $activeStudents }}</p>
                </div>

                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">Per Lesson</p>
                    <p class="mt-2 text-3xl font-semibold text-shell-text">{{ $perLessonStudents }}</p>
                </div>

                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">Monthly Plans</p>
                    <p class="mt-2 text-3xl font-semibold text-shell-text">{{ $planStudents }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('teacher.lesson-counts.index') }}" class="app-button-primary">Enter Lesson Counts</a>
                <a href="{{ route('teacher.students.index') }}" class="app-button-secondary">Open My Students</a>
            </div>
        </article>

        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Current Scope</h3>
            <ul class="mt-4 space-y-3 text-sm text-shell-muted">
                <li>View and edit only assigned students.</li>
                <li>Create students directly under your profile.</li>
                <li>Enter monthly counts for active per-lesson students.</li>
            </ul>
        </article>
    </section>
@endsection
