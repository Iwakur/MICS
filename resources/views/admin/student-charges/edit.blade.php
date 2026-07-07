@extends('layouts.app')

@section('title', __('finance.review_charge').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('finance.review_charge'))
@section('page-description', __('finance.review_charge_description'))

@section('content')
    <section class="app-surface p-6">
        <div class="mb-6 app-surface-strong p-4">
            <p class="font-semibold">{{ trim($studentMonth->student->first_name.' '.$studentMonth->student->family_name) }}</p>
            <p class="mt-1 text-sm text-shell-muted">{{ __('finance.generated_charge') }}: {{ \App\Support\LocalizedFormat::number($studentMonth->charge_amount) }} · {{ \App\Support\LocalizedFormat::month($studentMonth->month_date) }}</p>
            @if ($studentMonth->validated_at)
                <p class="mt-2 text-xs text-shell-muted">{{ __('finance.validated_by_at', ['date' => \App\Support\LocalizedFormat::dateTime($studentMonth->validated_at), 'user' => $studentMonth->validatedBy?->username ?? __('finance.deleted_user')]) }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.student-charges.update', $studentMonth) }}" class="grid gap-6">
            @csrf
            @method('PUT')
            <div><label for="manual_adjustment" class="text-sm text-shell-muted">{{ __('finance.adjustment_plus_minus') }}</label><input id="manual_adjustment" name="manual_adjustment" type="number" step="0.01" value="{{ old('manual_adjustment', $studentMonth->manual_adjustment) }}" class="app-input mt-2" required @readonly($studentMonth->status === \App\Enums\ReviewStatus::Validated)></div>
            <div><label for="adjustment_reason" class="text-sm text-shell-muted">{{ __('finance.adjustment_reason_help') }}</label><textarea id="adjustment_reason" name="adjustment_reason" class="app-input mt-2" @readonly($studentMonth->status === \App\Enums\ReviewStatus::Validated)>{{ old('adjustment_reason', $studentMonth->adjustment_reason) }}</textarea></div>
            <div><label for="charge_note" class="text-sm text-shell-muted">{{ __('finance.internal_note') }}</label><textarea id="charge_note" name="note" class="app-input mt-2" @readonly($studentMonth->status === \App\Enums\ReviewStatus::Validated)>{{ old('note', $studentMonth->note) }}</textarea></div>
            <div><label for="charge_status" class="text-sm text-shell-muted">{{ __('messages.status') }}</label><select id="charge_status" name="status" class="app-select mt-2" @disabled($studentMonth->status === \App\Enums\ReviewStatus::Validated)><option value="draft" @selected(old('status', $studentMonth->status->value) === 'draft')>{{ __('finance.draft') }}</option><option value="validated" @selected(old('status', $studentMonth->status->value) === 'validated')>{{ __('finance.validated') }}</option></select></div>
            <div class="flex gap-3">
                @if ($studentMonth->status === \App\Enums\ReviewStatus::Draft)<button type="submit" class="app-button-primary">{{ __('finance.save_review') }}</button>@endif
                <a class="app-button-secondary" href="{{ route('admin.student-charges.index', ['month' => $studentMonth->month_date->format('Y-m')]) }}">{{ __('finance.back') }}</a>
            </div>
        </form>
    </section>
@endsection
