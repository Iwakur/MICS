{{-- MICS Blade view: teacher students index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'My Students | MICS')
@section('eyebrow', 'Teacher')
@section('page-title', 'My Students')
@section('page-description', 'Manage only students assigned to your staff profile. Monthly lesson entry will be added as a separate workflow.')
@section('content')
    <section class="app-surface p-6">
        <div class="flex items-center justify-between gap-4"><div><h3 class="text-xl font-semibold text-shell-text">Assigned Students</h3><p class="mt-2 text-sm text-shell-muted">{{ $students->count() }} students in your current scope.</p></div><a href="{{ route('teacher.students.create') }}" class="app-button-primary">Add Student</a></div>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($students as $student)
                <article class="app-surface-strong p-5"><div class="flex items-start justify-between gap-4"><div><h4 class="font-semibold text-shell-text">{{ trim($student->first_name.' '.$student->family_name) }}</h4><p class="mt-1 text-sm text-shell-muted">{{ $student->lessonType?->name ?? $student->plan?->name }}</p></div><span class="{{ $student->status->value === 'active' ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $student->status->value }}</span></div><p class="mt-4 text-xs uppercase tracking-[0.2em] text-shell-muted">{{ str_replace('_', ' ', $student->billing_type->value) }}</p><a href="{{ route('teacher.students.edit', $student) }}" class="app-button-secondary mt-4 inline-flex">Edit Student</a></article>
            @empty
                <p class="text-sm text-shell-muted">No students are assigned to you.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $students->links() }}</div>
    </section>
@endsection
