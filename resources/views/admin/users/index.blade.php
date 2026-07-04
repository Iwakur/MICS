@extends('layouts.app')

@section('title', 'User Management | MICS')
@section('eyebrow', 'Administrator')
@section('page-title', 'User Management')
@section('page-description', 'Administrators can create, edit, activate, deactivate, and selectively delete user accounts here. The current safety rules prevent removing the last active admin or deleting your own account.')

@section('content')
    <section class="rounded-3xl border border-stone-800 bg-stone-900/90 p-6 shadow-2xl shadow-stone-950/30">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-white">All Users</h3>
                <p class="mt-2 max-w-2xl text-sm text-stone-400">This page is the first real admin CRUD screen. It works directly against the Laravel users table and respects the current role and activation rules.</p>
            </div>

            <a
                href="{{ route('admin.users.create') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-200"
            >
                Create User
            </a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-stone-800">
            <table class="min-w-full divide-y divide-stone-800 text-sm">
                <thead class="bg-stone-950/80 text-left text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Username</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800 bg-stone-900/60 text-stone-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-white">{{ $user->username }}</div>
                                @if (auth()->id() === $user->id)
                                    <div class="mt-1 text-xs uppercase tracking-[0.2em] text-amber-300">You</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-stone-400">{{ $user->email }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $user->isAdmin() ? 'bg-amber-300/15 text-amber-200' : 'bg-sky-300/15 text-sky-200' }}">
                                    {{ $user->role->value }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $user->is_active ? 'bg-emerald-300/15 text-emerald-200' : 'bg-stone-300/15 text-stone-300' }}">
                                    {{ $user->is_active ? 'active' : 'inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-3">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="rounded-xl border border-stone-700 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-stone-200 transition hover:border-amber-300 hover:text-amber-200"
                                    >
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-xl border border-rose-700/50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-rose-200 transition hover:border-rose-400 hover:text-rose-100"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
