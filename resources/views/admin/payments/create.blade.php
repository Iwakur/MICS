{{-- MICS HUB Blade view: admin payments create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', __('finance.record_payment').' | MICS HUB')
@section('eyebrow', __('finance.administrator_finance'))
@section('page-title', __('finance.record_payment_draft'))
@section('page-description', __('finance.payment_draft_description'))
@section('content')
    <section class="app-surface p-6"><form method="POST" action="{{ route('admin.payments.store') }}" class="grid gap-6">@csrf @include('admin.payments.partials.form')<div class="flex gap-3"><button type="submit" class="app-button-primary">{{ __('finance.create_draft') }}</button><a href="{{ route('admin.payments.index', ['month' => $selectedMonth]) }}" class="app-button-secondary">{{ __('messages.cancel') }}</a></div></form></section>
@endsection
