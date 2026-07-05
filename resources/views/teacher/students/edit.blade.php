@extends('layouts.app')
@section('title', 'Edit Student | MICS')
@section('eyebrow', 'Teacher')
@section('page-title', 'Edit Student')
@section('page-description', 'You can update assigned students but cannot reassign or archive them.')
@section('content')
    <section class="app-surface max-w-5xl p-6"><form method="POST" action="{{ route('teacher.students.update', $student) }}" class="grid gap-6">@csrf @method('PUT') @include('students.partials.form', ['student' => $student, 'canAssignTeacher' => false, 'canArchive' => false])<div class="flex gap-3"><button class="app-button-primary">Save Changes</button><a href="{{ route('teacher.students.index') }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
