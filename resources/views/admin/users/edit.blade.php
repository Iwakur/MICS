{{-- MICS Blade view: admin users edit. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Edit User | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Edit User')
@section('page-description', 'Update an existing user while keeping usernames and emails unique and preserving at least one active administrator.')

@section('content')
    <section class="app-surface max-w-3xl p-6">
        {{-- The edit form shares the same structure as create, but keeps password optional. --}}
        <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="grid gap-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="username" class="text-sm font-medium text-shell-text">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $managedUser->username) }}" required class="app-input">
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-shell-text">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" required class="app-input">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-shell-text">New Password</label>
                    <input id="password" name="password" type="password" class="app-input">
                    <p class="text-xs text-shell-muted">Leave blank to keep the current password.</p>
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-shell-text">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="app-input">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-shell-text">Role</label>
                    <select id="role" name="role" class="app-select">
                        <option value="admin" @selected(old('role', $managedUser->role->value) === 'admin')>admin</option>
                        <option value="teacher" @selected(old('role', $managedUser->role->value) === 'teacher')>teacher</option>
                    </select>
                </div>

                <label class="app-surface-strong flex items-center gap-3 px-4 py-3 text-sm text-shell-muted">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $managedUser->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent">
                    <span>Account is active</span>
                </label>
            </div>

            {{-- Editing yourself is allowed, but the backend still protects the last active admin rule. --}}
            @if (auth()->id() === $managedUser->id)
                <p class="rounded-2xl border border-brand-400/20 bg-brand-400/10 px-4 py-3 text-sm text-brand-100">
                    You are editing your own account. The safety rule will block changes that would remove the last active administrator.
                </p>
            @endif

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
