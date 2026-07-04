@extends('layouts.app')

@section('title', 'Create User | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Create User')
@section('page-description', 'Create a new staff account without bypassing the current username, email, role, password, and activation rules.')

@section('content')
    <section class="max-w-3xl rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="username" class="text-sm font-medium text-stone-200">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-stone-200">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-stone-200">Password</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-stone-200">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-stone-200">Role</label>
                    <select id="role" name="role" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                        <option value="admin" @selected(old('role') === 'admin')>admin</option>
                        <option value="teacher" @selected(old('role', 'teacher') === 'teacher')>teacher</option>
                    </select>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-stone-800 bg-stone-950/80 px-4 py-3 text-sm text-stone-300">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-stone-600 bg-stone-950 text-amber-300">
                    <span>Account is active</span>
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-2xl bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-200">Create User</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-stone-700 px-5 py-3 text-sm font-semibold text-stone-200 transition hover:border-stone-500 hover:text-white">Cancel</a>
            </div>
        </form>
    </section>
@endsection
