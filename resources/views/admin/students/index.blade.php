@extends('layouts.app')

@section('title', 'Students | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'Students')
@section('page-description', 'Manage student ownership and future billing configuration. Monthly charges remain historical snapshots.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <form method="GET" class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <input name="search" value="{{ request('search') }}" placeholder="Name or phone" class="app-input">
                <select name="status" class="app-select">
                    <option value="">All statuses</option>
                    @foreach (['active', 'paused', 'archived'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="billing_type" class="app-select">
                    <option value="">All billing types</option>
                    <option value="per_lesson" @selected(request('billing_type') === 'per_lesson')>Per lesson</option>
                    <option value="plan_based" @selected(request('billing_type') === 'plan_based')>Monthly plan</option>
                </select>
                <select name="staff_id" class="app-select">
                    <option value="">All teachers</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string) request('staff_id') === (string) $teacher->id)>{{ trim($teacher->first_name.' '.$teacher->family_name) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-3 md:col-span-2 xl:col-span-4">
                    <button class="app-button-secondary" type="submit">Apply Filters</button>
                    <a href="{{ route('admin.students.index') }}" class="app-button-secondary">Clear</a>
                </div>
            </form>
            <a href="{{ route('admin.students.create') }}" class="app-button-primary">Create Student</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">Student</th><th class="px-4 py-3 font-medium">Teacher</th><th class="px-4 py-3 font-medium">Billing</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shell-border bg-shell-panel/70 text-shell-text">
                    @forelse ($students as $student)
                        <tr>
                            <td class="px-4 py-4"><p class="font-semibold">{{ trim($student->first_name.' '.$student->family_name) }}</p><p class="mt-1 text-xs text-shell-muted">{{ $student->phone ?: $student->email ?: 'No contact' }}</p></td>
                            <td class="px-4 py-4 text-shell-muted">{{ trim($student->teacher->first_name.' '.$student->teacher->family_name) }}</td>
                            <td class="px-4 py-4"><p>{{ $student->billing_type->value === 'per_lesson' ? 'Per lesson' : 'Monthly plan' }}</p><p class="mt-1 text-xs text-shell-muted">{{ $student->lessonType?->name ?? $student->plan?->name }}</p></td>
                            <td class="px-4 py-4"><span class="{{ $student->status->value === 'active' ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $student->status->value }}</span></td>
                            <td class="px-4 py-4"><div class="flex flex-wrap gap-3"><a href="{{ route('admin.students.edit', $student) }}" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">Edit</a>@if ($student->status->value !== 'archived')<form method="POST" action="{{ route('admin.students.destroy', $student) }}">@csrf @method('DELETE')<button class="app-button-danger" type="submit">Archive</button></form>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-shell-muted">No students match the current filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
