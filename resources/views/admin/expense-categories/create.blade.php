@extends('layouts.app')
@section('title', 'Create Expense Category | MICS HUB')
@section('eyebrow', 'Administrator · Finance Setup')
@section('page-title', 'Create Expense Category')
@section('page-description', 'Categories organize manual and generated expenses without changing financial history.')
@section('content')
<section class="app-surface p-6"><form method="POST" action="{{ route('admin.expense-categories.store') }}" class="grid gap-6">@csrf @include('admin.expense-categories.partials.form')<div class="flex gap-3"><button class="app-button-primary">Create Category</button><a class="app-button-secondary" href="{{ route('admin.expense-categories.index') }}">Cancel</a></div></form></section>
@endsection
