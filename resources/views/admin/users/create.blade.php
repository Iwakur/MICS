{{-- MICS HUB Blade view: admin users create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Create User | MICS HUB')
@section('eyebrow', 'Administrator')
@section('page-title', 'Create User')
@section('page-description', 'Create a new staff account without bypassing the current username, email, role, password, and activation rules.')

@section('content')
    <section class="app-surface max-w-3xl p-6">
        {{-- The create form mirrors the fields the users table currently requires. --}}
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2 md:col-span-2"><label for="staff_id" class="text-sm font-medium text-shell-text">Linked staff profile</label><select id="staff_id" name="staff_id" class="app-select"><option value="">Select staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->id }}" @selected((string)old('staff_id') === (string)$staff->id)>{{ trim($staff->first_name.' '.$staff->family_name) }} · {{ $staff->role->name }}{{ $staff->role->can_teach ? ' · can teach' : '' }}</option>@endforeach</select><p class="text-xs text-shell-muted">Required for active accounts. Teacher accounts require a teaching-capable profile.</p></div>
                <div class="space-y-2">
                    <label for="username" class="text-sm font-medium text-shell-text">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required class="app-input">
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-shell-text">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="app-input">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-shell-text">Password</label>
                    <input id="password" name="password" type="password" required class="app-input">
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-shell-text">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="app-input">
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-shell-text">Role</label>
                    <select id="role" name="role" class="app-select">
                        <option value="admin" @selected(old('role') === 'admin')>admin</option>
                        <option value="teacher" @selected(old('role', 'teacher') === 'teacher')>teacher</option>
                    </select>
                </div>

                <label class="app-surface-strong flex items-center gap-3 px-4 py-3 text-sm text-shell-muted">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent">
                    <span>Account is active</span>
                </label>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="app-button-primary">Create User</button>
                <a href="{{ route('admin.users.index') }}" class="app-button-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
