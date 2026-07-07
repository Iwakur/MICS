{{-- MICS HUB Blade view: admin students create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Create Student | MICS HUB')
@section('eyebrow', 'Administrator')
@section('page-title', 'Create Student')
@section('page-description', 'Assign the student and choose exactly one billing model.')
@section('content')
    <section class="app-surface max-w-5xl p-6"><form method="POST" action="{{ route('admin.students.store') }}" class="grid gap-6">@csrf @include('students.partials.form', ['student' => null, 'canAssignTeacher' => true, 'canArchive' => true])<div class="flex gap-3"><button class="app-button-primary">Create Student</button><a href="{{ route('admin.students.index') }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
