{{-- MICS Blade view: admin expenses create. Full responsibility is documented in docs/file-reference.md. --}}
@extends('layouts.app')
@section('title','Add Expense | MICS') @section('eyebrow','Administrator · Finance') @section('page-title','Add Manual Expense') @section('page-description','Record an irregular expense and leave it as draft or validate it immediately.')
@section('content')<section class="app-surface p-6"><form method="POST" action="{{ route('admin.expenses.store') }}" class="grid gap-6">@csrf @include('admin.expenses.partials.form',['expense'=>null])<div class="flex gap-3"><button class="app-button-primary">Create Expense</button><a href="{{ route('admin.expenses.index') }}" class="app-button-secondary">Cancel</a></div></form></section>@endsection
