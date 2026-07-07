{{-- MICS HUB Blade view: teacher students create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', __('messages.add_student').' | MICS HUB')
@section('eyebrow', __('messages.teacher'))
@section('page-title', __('messages.add_student'))
@section('page-description', __('messages.add_student_description'))
@section('content')
    <section class="app-surface max-w-5xl p-6"><form method="POST" action="{{ route('teacher.students.store') }}" class="grid gap-6">@csrf @include('students.partials.form', ['student' => null, 'canAssignTeacher' => false, 'canArchive' => false])<div class="flex flex-wrap gap-3"><button class="app-button-primary">{{ __('messages.add_student') }}</button><a href="{{ route('teacher.students.index') }}" class="app-button-secondary">{{ __('messages.cancel') }}</a></div></form></section>
@endsection
