{{-- MICS HUB Blade view: admin plans edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Edit Plan | MICS HUB')
@section('eyebrow', 'Administrator · Catalog')
@section('page-title', 'Edit Plan')
@section('page-description', 'Amount changes affect future month closing only; historical snapshots remain unchanged.')

@section('content')
    <section class="app-surface max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="grid gap-6">
            @csrf
            @method('PUT')
            @include('admin.plans.partials.form', ['plan' => $plan])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Save Changes</button>
                <a href="{{ route('admin.plans.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
