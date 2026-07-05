{{-- MICS Blade view: admin staff create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Create Staff | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Create Staff')
@section('page-description', 'Create a staff profile, choose fixed or dynamic compensation, and optionally link an existing account.')

@section('content')
    <section class="app-surface max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.staff.store') }}" class="grid gap-6">
            @csrf
            @include('admin.staff.partials.form', ['staffMember' => null, 'withUser' => true])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Create Staff</button>
                <a href="{{ route('admin.staff.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
