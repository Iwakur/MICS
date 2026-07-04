<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard | {{ config('app.name', 'MICS') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <main class="mx-auto flex min-h-screen max-w-4xl items-center px-6 py-12">
            <section class="w-full rounded-3xl border border-stone-800 bg-stone-900/90 p-8 shadow-2xl shadow-stone-950/40">
                <p class="text-sm font-medium uppercase tracking-[0.3em] text-amber-300">Dashboard</p>
                <h1 class="mt-3 text-3xl font-semibold text-white">Welcome back, {{ auth()->user()->username }}</h1>
                <p class="mt-4 max-w-2xl text-sm text-stone-400">
                    This is the first protected page. It proves the Laravel session auth flow works before we build the real school screens.
                </p>

                <dl class="mt-8 grid gap-4 text-sm text-stone-300 md:grid-cols-3">
                    <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                        <dt class="text-stone-500">Username</dt>
                        <dd class="mt-2 font-semibold text-white">{{ auth()->user()->username }}</dd>
                    </div>
                    <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                        <dt class="text-stone-500">Role</dt>
                        <dd class="mt-2 font-semibold text-white">{{ auth()->user()->role->value }}</dd>
                    </div>
                    <div class="rounded-2xl border border-stone-800 bg-stone-950/80 p-4">
                        <dt class="text-stone-500">Status</dt>
                        <dd class="mt-2 font-semibold text-white">{{ auth()->user()->is_active ? 'active' : 'inactive' }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-2xl border border-stone-700 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:border-amber-300 hover:text-amber-200"
                    >
                        Log out
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>
