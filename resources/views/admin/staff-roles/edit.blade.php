@extends('layouts.app')
@section('title', 'Edit Staff Role | MICS')
@section('eyebrow', 'Administrator · Staff')
@section('page-title', 'Edit Staff Role')
@section('page-description', 'Existing staff retain this role if archived; it simply stops appearing for new assignments.')
@section('content')
    <section class="app-surface max-w-3xl p-6"><form method="POST" action="{{ route('admin.staff-roles.update', $staffRole) }}" class="grid gap-6">@csrf @method('PUT') @include('admin.staff-roles.partials.form', ['staffRole' => $staffRole])<div class="flex gap-3"><button class="app-button-primary">Save Changes</button><a href="{{ route('admin.staff-roles.index') }}" class="app-button-secondary">Cancel</a></div></form></section>
@endsection
