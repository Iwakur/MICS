{{-- MICS HUB Blade view: admin payments create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title', 'Record Payment | MICS HUB')
@section('eyebrow', 'Administrator · Finance')
@section('page-title', 'Record Payment Draft')
@section('page-description', 'The payment does not affect debt until an administrator validates it.')
@section('content')
    <section class="app-surface p-6"><form method="POST" action="{{ route('admin.payments.store') }}" class="grid gap-6">@csrf @include('admin.payments.partials.form')<div class="flex gap-3"><button class="app-button-primary">Create Draft</button><a href="{{ route('admin.payments.index', ['month' => $selectedMonth]) }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
