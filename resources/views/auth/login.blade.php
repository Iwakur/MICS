{{-- MICS Blade view: auth login. Full responsibility is documented in docs/file-reference.md. --}}
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
    <body class="app-page">
        <main class="flex min-h-screen items-center justify-center px-6 py-12">
            {{-- The login card stays intentionally simple because auth should feel clear, not busy. --}}
            <div class="app-surface w-full max-w-md p-8">
                <div class="space-y-3">
                    <p class="text-sm font-medium uppercase tracking-[0.3em] text-brand-300">MICS</p>
                    <h1 class="text-3xl font-semibold text-shell-text">Sign in</h1>
                    <p class="text-sm text-shell-muted">
                        Use your username and password. Email stays on the account for later communication, not for login.
                    </p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="username" class="text-sm font-medium text-shell-text">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="app-input"
                        >
                        @error('username')
                            <p class="text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-shell-text">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="app-input"
                        >
                        @error('password')
                            <p class="text-sm text-red-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 text-sm text-shell-muted">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent"
                        >
                        <span>Remember me on this device</span>
                    </label>

                    <button type="submit" class="app-button-primary w-full">
                        Sign in
                    </button>
                </form>

                <p class="mt-5 text-xs text-shell-muted">
                    Default seeded account: <strong>admin</strong> / <strong>admin</strong>. Change it later through the UI once that screen exists.
                </p>
            </div>
        </main>
    </body>
</html>
