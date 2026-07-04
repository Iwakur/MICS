<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'MICS')</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        @php($user = auth()->user())

        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            <aside class="border-b border-stone-800 bg-stone-900/90 lg:border-b-0 lg:border-r">
                <div class="flex h-full flex-col">
                    <div class="border-b border-stone-800 px-6 py-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300">MICS</p>
                        <h1 class="mt-3 text-xl font-semibold text-white">School Operations</h1>
                        <p class="mt-2 text-sm text-stone-400">Shared app shell for authenticated staff screens.</p>
                    </div>

                    <nav class="flex-1 space-y-2 px-4 py-6 text-sm">
                        @if ($user->isAdmin())
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-300 text-stone-950' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}"
                            >
                                Admin Dashboard
                            </a>
                            <a
                                href="{{ route('admin.users.index') }}"
                                class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.users.*') ? 'bg-amber-300 text-stone-950' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}"
                            >
                                User Management
                            </a>
                        @else
                            <a
                                href="{{ route('teacher.dashboard') }}"
                                class="block rounded-2xl px-4 py-3 transition {{ request()->routeIs('teacher.dashboard') ? 'bg-amber-300 text-stone-950' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}"
                            >
                                Teacher Dashboard
                            </a>
                        @endif
                    </nav>
                </div>
            </aside>

            <div class="min-h-screen">
                <header class="border-b border-stone-800 bg-stone-950/80 px-6 py-4 backdrop-blur">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-stone-500">@yield('eyebrow', 'Workspace')</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white">@yield('page-title', 'Dashboard')</h2>
                            <p class="mt-2 max-w-2xl text-sm text-stone-400">@yield('page-description')</p>
                        </div>

                        <div class="rounded-2xl border border-stone-800 bg-stone-900/80 px-4 py-3 text-sm text-stone-300">
                            <p class="font-semibold text-white">{{ $user->username }}</p>
                            <p class="mt-1 text-stone-400">{{ $user->role->value }} · {{ $user->is_active ? 'active' : 'inactive' }}</p>
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button
                                    type="submit"
                                    class="rounded-xl border border-stone-700 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-stone-200 transition hover:border-amber-300 hover:text-amber-200"
                                >
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="px-6 py-6 lg:px-8 lg:py-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-700/40 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-rose-700/40 bg-rose-950/40 px-4 py-3 text-sm text-rose-200">
                            <p class="font-semibold text-white">Please review the current action.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
