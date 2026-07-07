{{-- MICS HUB Blade view: teacher students edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', __('messages.edit_student').' | MICS HUB')
@section('eyebrow', __('messages.teacher'))
@section('page-title', __('messages.edit_student'))
@section('page-description', __('messages.edit_student_description'))
@section('content')
    <section class="app-surface max-w-5xl p-6"><form method="POST" action="{{ route('teacher.students.update', $student) }}" class="grid gap-6">@csrf @method('PUT') @include('students.partials.form', ['student' => $student, 'canAssignTeacher' => false, 'canArchive' => false])<div class="flex flex-wrap gap-3"><button class="app-button-primary">{{ __('messages.save_changes') }}</button><a href="{{ route('teacher.students.index') }}" class="app-button-secondary">{{ __('messages.cancel') }}</a></div></form></section>
@endsection
