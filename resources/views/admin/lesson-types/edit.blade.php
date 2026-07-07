{{-- MICS HUB Blade view: admin lesson-types edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Edit Lesson Type | MICS HUB')
@section('eyebrow', 'Administrator · Catalog')
@section('page-title', 'Edit Lesson Type')
@section('page-description', 'Rate changes affect future month closing only; historical snapshots remain unchanged.')

@section('content')
    <section class="app-surface max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.lesson-types.update', $lessonType) }}" class="grid gap-6">
            @csrf
            @method('PUT')
            @include('admin.lesson-types.partials.form', ['lessonType' => $lessonType])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Save Changes</button>
                <a href="{{ route('admin.lesson-types.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
