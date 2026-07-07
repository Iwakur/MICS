{{-- MICS HUB Blade view: admin staff edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Edit Staff | MICS HUB')
@section('eyebrow', 'Administrator')
@section('page-title', 'Edit Staff')
@section('page-description', 'Update staff identity, active state, and the linked login account if one exists.')

@section('content')
    <section class="app-surface max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.staff.update', $staffMember) }}" class="grid gap-6">
            @csrf
            @method('PUT')
            @include('admin.staff.partials.form', ['staffMember' => $staffMember, 'withUser' => true])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Save Changes</button>
                <a href="{{ route('admin.staff.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
