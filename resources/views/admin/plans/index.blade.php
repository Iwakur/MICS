{{-- MICS HUB Blade view: admin plans index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')

@section('title', 'Plans | MICS HUB')
@section('eyebrow', 'Administrator · Catalog')
@section('page-title', 'Plans')
@section('page-description', 'Configure recurring monthly student charges and dynamic teacher earnings for plan-based students.')

@section('content')
    <section class="app-surface p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-shell-text">Monthly Plan Catalog</h3>
                <p class="mt-2 text-sm text-shell-muted">Active plan students contribute these full amounts every applicable month.</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="app-button-primary">Create Plan</a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
            <table class="min-w-full divide-y divide-shell-border text-sm">
                <thead class="bg-brand-950/70 text-left text-shell-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">Plan</th>
                        <th class="px-4 py-3 font-medium">Student Monthly</th>
                        <th class="px-4 py-3 font-medium">Teacher Monthly</th>
                        <th class="px-4 py-3 font-medium">Students</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shell-border bg-shell-panel/70 text-shell-text">
                    @forelse ($plans as $plan)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-semibold">{{ $plan->name }}</p>
                                <p class="mt-1 text-xs text-shell-muted">{{ $plan->lesson_count }} lessons · {{ $plan->duration_minutes }} minutes</p>
                            </td>
                            <td class="px-4 py-4">{{ number_format((float) $plan->plan_price, 2) }}</td>
                            <td class="px-4 py-4">{{ number_format((float) $plan->teacher_monthly_amount, 2) }}</td>
                            <td class="px-4 py-4 text-shell-muted">{{ $plan->students_count }}</td>
                            <td class="px-4 py-4"><span class="{{ $plan->is_assignable ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $plan->is_assignable ? 'assignable' : 'archived' }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">Edit</a>
                                    @if ($plan->is_assignable)
                                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="app-button-danger">Archive</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-shell-muted">No plans configured yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $plans->links() }}</div>
    </section>
@endsection
