@extends('layouts.app')

@section('title', 'Teacher Dashboard | MICS')
@section('eyebrow', 'Teacher')
@section('page-title', 'Teacher Dashboard')
@section('page-description', 'This dashboard shares the same shell as admin, but intentionally shows a smaller and more focused teacher workspace.')

@section('content')
    <section class="grid gap-4 lg:grid-cols-[2fr_1fr]">
        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Welcome back</h3>
            <p class="mt-3 max-w-2xl text-sm text-shell-muted">
                Teachers and administrators do not share the same control surface. This page keeps the same visual system, but narrows the visible scope.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">Username</p>
                    <p class="mt-2 text-lg font-semibold text-shell-text">{{ auth()->user()->username }}</p>
                </div>

                <div class="app-surface-strong p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-shell-muted">Email</p>
                    <p class="mt-2 text-lg font-semibold text-shell-text">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </article>

        <article class="app-surface p-6">
            <h3 class="text-xl font-semibold text-shell-text">Current Scope</h3>
            <ul class="mt-4 space-y-3 text-sm text-shell-muted">
                <li>Protected login is working.</li>
                <li>The shared app shell is now in place.</li>
                <li>Teacher-specific business pages can be added here next.</li>
            </ul>
        </article>
    </section>
@endsection
