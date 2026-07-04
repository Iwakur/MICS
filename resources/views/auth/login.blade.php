<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | {{ config('app.name', 'MICS') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <main class="flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-md rounded-3xl border border-stone-800 bg-stone-900/90 p-8 shadow-2xl shadow-stone-950/40">
                <div class="space-y-3">
                    <p class="text-sm font-medium uppercase tracking-[0.3em] text-amber-300">MICS</p>
                    <h1 class="text-3xl font-semibold text-white">Sign in</h1>
                    <p class="text-sm text-stone-400">
                        Use your username and password. Email stays on the account for later communication, not for login.
                    </p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="username" class="text-sm font-medium text-stone-200">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 placeholder:text-stone-500 focus:border-amber-300 focus:outline-none"
                        >
                        @error('username')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-stone-200">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3 text-stone-100 placeholder:text-stone-500 focus:border-amber-300 focus:outline-none"
                        >
                        @error('password')
                            <p class="text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 text-sm text-stone-300">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-stone-600 bg-stone-950 text-amber-300 focus:ring-amber-300"
                        >
                        <span>Remember me on this device</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-amber-300 px-4 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-500"
                    >
                        Sign in
                    </button>
                </form>

                <p class="text-xs text-stone-500 my-5">
                    Default seeded account: <strong>admin</strong> / <strong>admin</strong>. Change it later through the UI once that screen exists.
                </p>
            </div>
        </main>
    </body>
</html>
