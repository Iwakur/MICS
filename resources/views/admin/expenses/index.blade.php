{{-- MICS HUB Blade view: admin expenses index. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', __('messages.expenses_salaries').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('finance.expenses_salary_drafts'))
@section('page-description', __('finance.expenses_page_description'))
@section('content')
<section class="app-surface p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="flex items-end gap-3"><div><label for="month" class="text-sm text-shell-muted">{{ __('finance.month') }}</label><input id="month" name="month" type="month" value="{{ request('month') }}" class="app-input mt-2"></div><button type="submit" class="app-button-secondary">{{ __('finance.filter') }}</button></form>
        <a href="{{ route('admin.expenses.create', ['month' => request('month', now()->format('Y-m'))]) }}" class="app-button-primary">{{ __('finance.add_manual_expense') }}</a>
    </div>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-shell-border">
        <table class="min-w-full divide-y divide-shell-border text-sm"><thead class="bg-brand-950/70 text-left text-shell-muted"><tr><th class="px-4 py-3">{{ __('finance.month') }}</th><th class="px-4 py-3">{{ __('finance.type') }}</th><th class="px-4 py-3">{{ __('finance.category_staff') }}</th><th class="px-4 py-3">{{ __('finance.amount') }}</th><th class="px-4 py-3">{{ __('messages.status') }}</th><th class="px-4 py-3">{{ __('finance.actions') }}</th></tr></thead>
        <tbody class="divide-y divide-shell-border">
        @forelse($expenses as $expense)<tr><td class="px-4 py-4">{{ \App\Support\LocalizedFormat::month($expense->month_date) }}</td><td class="px-4 py-4"><span class="{{ $expense->is_auto_generated ? 'app-badge-teacher' : 'app-badge-inactive' }}">{{ __('finance.'.($expense->is_auto_generated ? 'salary' : 'manual')) }}</span></td><td class="px-4 py-4"><p>{{ $expense->category->name }}</p><p class="text-xs text-shell-muted">{{ $expense->staffMember ? trim($expense->staffMember->first_name.' '.$expense->staffMember->family_name) : __('finance.general_expense') }}@if($expense->salary_sources_count) · {{ trans_choice('finance.sources_count', $expense->salary_sources_count, ['count' => $expense->salary_sources_count]) }} @endif</p></td><td class="px-4 py-4 font-semibold">{{ \App\Support\LocalizedFormat::number($expense->amount) }}</td><td class="px-4 py-4"><span class="{{ $expense->status === \App\Enums\ReviewStatus::Validated ? 'app-badge-active' : 'app-badge-inactive' }}">{{ __('finance.'.$expense->status->value) }}</span></td><td class="px-4 py-4"><div class="flex gap-2"><a href="{{ route('admin.expenses.edit',$expense) }}" class="app-button-secondary px-3 py-2">{{ $expense->status === \App\Enums\ReviewStatus::Draft ? __('finance.review') : __('finance.view') }}</a>@if(!$expense->is_auto_generated && $expense->status === \App\Enums\ReviewStatus::Draft)<form method="POST" action="{{ route('admin.expenses.destroy',$expense) }}" data-confirm="{{ __('finance.confirm_delete_expense') }}">@csrf @method('DELETE')<button type="submit" class="app-button-danger">{{ __('finance.delete') }}</button></form>@endif</div></td></tr>
        @empty<tr><td colspan="6" class="px-4 py-10 text-center text-shell-muted">{{ __('finance.no_expense_records') }}</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="mt-6">{{ $expenses->links() }}</div>
</section>
@endsection
