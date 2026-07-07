@extends('layouts.app')
@section('title', 'Edit Expense Category | MICS HUB')
@section('eyebrow', 'Administrator · Finance Setup')
@section('page-title', 'Edit Expense Category')
@section('page-description', 'Archive a used category to preserve history while removing it from new expense forms.')
@section('content')
<section class="app-surface p-6"><form method="POST" action="{{ route('admin.expense-categories.update', $expenseCategory) }}" class="grid gap-6">@csrf @method('PUT') @include('admin.expense-categories.partials.form')<div class="flex gap-3"><button class="app-button-primary">Save Category</button><a class="app-button-secondary" href="{{ route('admin.expense-categories.index') }}">Cancel</a></div></form></section>
@endsection
