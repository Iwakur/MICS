@extends('layouts.app')

@section('title', 'Edit User | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Edit User')
@section('page-description', 'Update an existing user while keeping usernames and emails unique and preserving at least one active administrator.')

@section('content')
    <section class="max-w-3xl rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
        <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="grid gap-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="username" class="text-sm font-medium text-stone-200">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $managedUser->username) }}" required class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-stone-200">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" required class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-stone-200">New Password</label>
                    <input id="password" name="password" type="password" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                    <p class="text-xs text-stone-500">Leave blank to keep the current password.</p>
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-stone-200">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-stone-200">Role</label>
                    <select id="role" name="role" class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 focus:border-amber-300 focus:outline-none">
                        <option value="admin" @selected(old('role', $managedUser->role->value) === 'admin')>admin</option>
                        <option value="teacher" @selected(old('role', $managedUser->role->value) === 'teacher')>teacher</option>
                    </select>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-stone-800 bg-stone-950/80 px-4 py-3 text-sm text-stone-300">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $managedUser->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-stone-600 bg-stone-950 text-amber-300">
                    <span>Account is active</span>
                </label>
            </div>

            @if (auth()->id() === $managedUser->id)
                <p class="rounded-2xl border border-amber-700/40 bg-amber-950/30 px-4 py-3 text-sm text-amber-200">
                    You are editing your own account. The safety rule will block changes that would remove the last active administrator.
                </p>
            @endif

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-2xl bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-200">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-stone-700 px-5 py-3 text-sm font-semibold text-stone-200 transition hover:border-stone-500 hover:text-white">Cancel</a>
            </div>
        </form>
    </section>
@endsection
