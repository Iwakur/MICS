@extends('layouts.app')

@section('title', 'Admin Dashboard | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'This is the first real authenticated home for administrators. It should become the control point for staff, users, and future school operations screens.')

@section('content')
    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
            <p class="text-sm uppercase tracking-[0.25em] text-stone-500">Users</p>
            <p class="mt-4 text-4xl font-semibold text-white">{{ $totalUsers }}</p>
            <p class="mt-2 text-sm text-stone-400">Total accounts currently stored in the Laravel users table.</p>
        </article>

        <article class="rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
            <p class="text-sm uppercase tracking-[0.25em] text-stone-500">Active Admins</p>
            <p class="mt-4 text-4xl font-semibold text-white">{{ $activeAdmins }}</p>
            <p class="mt-2 text-sm text-stone-400">At least one active administrator must always remain available.</p>
        </article>

        <article class="rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
            <p class="text-sm uppercase tracking-[0.25em] text-stone-500">Active Teachers</p>
            <p class="mt-4 text-4xl font-semibold text-white">{{ $activeTeachers }}</p>
            <p class="mt-2 text-sm text-stone-400">Teachers are staff users, but with a narrower access surface than admins.</p>
        </article>
    </section>

    <section class="mt-6 rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-white">User Management</h3>
                <p class="mt-2 max-w-2xl text-sm text-stone-400">
                    Start here to maintain usernames, emails, roles, passwords, and activation status without violating the current database rules.
                </p>
            </div>

            <a
                href="{{ route('admin.users.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-200"
            >
                Open User Management
            </a>
        </div>
    </section>
@endsection
