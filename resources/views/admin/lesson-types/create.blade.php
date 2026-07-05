{{-- MICS Blade view: admin lesson-types create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Create Lesson Type | MICS')
@section('eyebrow', 'Administrator · Catalog')
@section('page-title', 'Create Lesson Type')
@section('page-description', 'Define the per-lesson amounts for the school and dynamic teacher.')

@section('content')
    <section class="app-surface max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.lesson-types.store') }}" class="grid gap-6">
            @csrf
            @include('admin.lesson-types.partials.form', ['lessonType' => null])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Create Lesson Type</button>
                <a href="{{ route('admin.lesson-types.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
