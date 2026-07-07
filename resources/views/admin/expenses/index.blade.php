{{-- MICS HUB Blade view: admin expenses index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Expenses & Salaries | MICS HUB')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', 'Expenses & Salary Drafts')
@section('page-description', 'Review generated salary drafts and maintain irregular manual expenses. Validated records are immutable.')
@section('content')
<section class="app-surface p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="flex items-end gap-3"><div><label for="month" class="text-sm text-shell-muted">Month</label><input id="month" name="month" type="month" value="{{ request('month') }}" class="app-input mt-2"></div><button class="app-button-secondary">Filter</button></form>
        <a href="{{ route('admin.expenses.create') }}" class="app-button-primary">Add Manual Expense</a>
    </div>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
        <table class="min-w-full divide-y divide-shell-border text-sm"><thead class="bg-brand-950/70 text-left text-shell-muted"><tr><th class="px-4 py-3">Month</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Category / Staff</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody class="divide-y divide-shell-border">
        @forelse($expenses as $expense)<tr><td class="px-4 py-4">{{ \App\Support\LocalizedFormat::month($expense->month_date) }}</td><td class="px-4 py-4"><span class="{{ $expense->is_auto_generated ? 'app-badge-teacher' : 'app-badge-inactive' }}">{{ $expense->is_auto_generated ? 'salary' : 'manual' }}</span></td><td class="px-4 py-4"><p>{{ $expense->category->name }}</p><p class="text-xs text-shell-muted">{{ $expense->staffMember ? trim($expense->staffMember->first_name.' '.$expense->staffMember->family_name) : 'General expense' }}@if($expense->salary_sources_count) · {{ $expense->salary_sources_count }} sources @endif</p></td><td class="px-4 py-4 font-semibold">{{ \App\Support\LocalizedFormat::number($expense->amount) }}</td><td class="px-4 py-4"><span class="{{ $expense->status === \App\Enums\ReviewStatus::Validated ? 'app-badge-active' : 'app-badge-inactive' }}">{{ $expense->status->value }}</span></td><td class="px-4 py-4"><div class="flex gap-2"><a href="{{ route('admin.expenses.edit',$expense) }}" class="app-button-secondary px-3 py-2">{{ $expense->status === \App\Enums\ReviewStatus::Draft ? 'Review' : 'View' }}</a>@if(!$expense->is_auto_generated && $expense->status === \App\Enums\ReviewStatus::Draft)<form method="POST" action="{{ route('admin.expenses.destroy',$expense) }}">@csrf @method('DELETE')<button class="app-button-danger">Delete</button></form>@endif</div></td></tr>
        @empty<tr><td colspan="6" class="px-4 py-10 text-center text-shell-muted">No expense or salary records.</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="mt-6">{{ $expenses->links() }}</div>
</section>
@endsection
