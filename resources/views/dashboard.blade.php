@extends('layouts.app')

@section('title', 'Legacy Dashboard | MICS')
@section('eyebrow', 'Reference')
@section('page-title', 'Legacy Temporary Dashboard')
@section('page-description', 'This view is no longer the active dashboard route. It remains only as a reference point from the earlier auth phase, while the real flow now redirects to role-specific dashboards.')

@section('content')
    {{-- This page is intentionally retained as documentation of the earlier temporary dashboard step. --}}
    <section class="app-surface max-w-4xl p-6">
        <h3 class="text-xl font-semibold text-shell-text">Earlier Auth Checkpoint</h3>
        <p class="mt-3 max-w-2xl text-sm text-shell-muted">
            Before the shared shell and role-aware routing were added, this temporary page proved that login, logout, and session auth were working.
        </p>

        <dl class="mt-6 grid gap-4 text-sm md:grid-cols-3">
            <div class="app-surface-strong p-4">
                <dt class="text-shell-muted">Username</dt>
                <dd class="mt-2 font-semibold text-shell-text">{{ auth()->user()->username }}</dd>
            </div>
            <div class="app-surface-strong p-4">
                <dt class="text-shell-muted">Role</dt>
                <dd class="mt-2 font-semibold text-shell-text">{{ auth()->user()->role->value }}</dd>
            </div>
            <div class="app-surface-strong p-4">
                <dt class="text-shell-muted">Status</dt>
                <dd class="mt-2 font-semibold text-shell-text">{{ auth()->user()->is_active ? 'active' : 'inactive' }}</dd>
            </div>
        </dl>
    </section>
@endsection
