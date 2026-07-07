{{-- MICS HUB Blade view: admin plans create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Create Plan | MICS HUB')
@section('eyebrow', 'Administrator · Catalog')
@section('page-title', 'Create Plan')
@section('page-description', 'Define recurring monthly amounts for the school and dynamic teacher.')

@section('content')
    <section class="app-surface max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="grid gap-6">
            @csrf
            @include('admin.plans.partials.form', ['plan' => null])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Create Plan</button>
                <a href="{{ route('admin.plans.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
