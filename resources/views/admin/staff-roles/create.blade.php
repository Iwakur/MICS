@extends('layouts.app')
@section('title', 'Create Staff Role | MICS')
@section('eyebrow', 'Administrator · Staff')
@section('page-title', 'Create Staff Role')
@section('page-description', 'Add a reusable business role and explicitly choose whether it can receive students.')
@section('content')
    <section class="app-surface max-w-3xl p-6"><form method="POST" action="{{ route('admin.staff-roles.store') }}" class="grid gap-6">@csrf @include('admin.staff-roles.partials.form', ['staffRole' => null])<div class="flex gap-3"><button class="app-button-primary">Create Role</button><a href="{{ route('admin.staff-roles.index') }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
