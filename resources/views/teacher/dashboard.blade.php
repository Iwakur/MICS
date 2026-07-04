@extends('layouts.app')

@section('title', 'Teacher Dashboard | MICS')
@section('eyebrow', 'Teacher')
@section('page-title', 'Teacher Dashboard')
@section('page-description', 'This is the teacher-facing version of the authenticated shell. It uses the same structure as admin, but with a narrower navigation surface and simpler starting information.')

@section('content')
    <section class="grid gap-4 lg:grid-cols-[2fr_1fr]">
        <article class="rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
            <h3 class="text-xl font-semibold text-white">Welcome back</h3>
            <p class="mt-3 max-w-2xl text-sm text-stone-400">
                The teacher dashboard is intentionally smaller than the admin dashboard. That teaches the real MICS rule that teachers and administrators do not share the same control surface.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-stone-500">Username</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ auth()->user()->username }}</p>
                </div>

                <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                    <p class="text-sm uppercase tracking-[0.2em] text-stone-500">Email</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </article>

        <article class="rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
            <h3 class="text-xl font-semibold text-white">Current Scope</h3>
            <ul class="mt-4 space-y-3 text-sm text-stone-400">
                <li>Protected login is working.</li>
                <li>The shared app shell is now in place.</li>
                <li>Teacher-specific business pages can be added here next.</li>
            </ul>
        </article>
    </section>
@endsection
