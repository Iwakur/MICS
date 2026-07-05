{{-- MICS Blade view: teacher students create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Add Student | MICS')
@section('eyebrow', 'Teacher')
@section('page-title', 'Add Student')
@section('page-description', 'The new student is automatically assigned to your staff profile.')
@section('content')
    <section class="app-surface max-w-5xl p-6"><form method="POST" action="{{ route('teacher.students.store') }}" class="grid gap-6">@csrf @include('students.partials.form', ['student' => null, 'canAssignTeacher' => false, 'canArchive' => false])<div class="flex gap-3"><button class="app-button-primary">Add Student</button><a href="{{ route('teacher.students.index') }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
