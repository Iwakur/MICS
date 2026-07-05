{{-- MICS Blade view: admin students edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Edit Student | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Edit Student')
@section('page-description', 'Changes configure future month closing and do not rewrite historical monthly rows.')
@section('content')
    <section class="app-surface max-w-5xl p-6"><form method="POST" action="{{ route('admin.students.update', $student) }}" class="grid gap-6">@csrf @method('PUT') @include('students.partials.form', ['student' => $student, 'canAssignTeacher' => true, 'canArchive' => true])<div class="flex gap-3"><button class="app-button-primary">Save Changes</button><a href="{{ route('admin.students.index') }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
