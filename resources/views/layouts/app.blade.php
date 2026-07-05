{{-- MICS Blade view: layouts app. Full responsibility is documented in docs/file-reference.md. --}}
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
    <body class="app-page">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 app-button-primary">Skip to content</a>
        @php($user = auth()->user())

        <div class="app-shell">
            {{-- Left navigation stays shared so admin and teacher areas feel like one product. --}}
            <aside class="app-sidebar">
                <div class="flex h-full flex-col">
                    <div class="border-b border-shell-border px-6 py-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-300">MICS</p>
                        <h1 class="mt-3 text-xl font-semibold text-shell-text">School Operations</h1>
                        <p class="mt-2 text-sm text-shell-muted">Shared workspace for authenticated staff screens.</p>
                    </div>

                    <nav class="flex-1 space-y-2 px-4 py-6 text-sm">
                        @if ($user->isAdmin())
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="app-nav-link {{ request()->routeIs('admin.dashboard') ? 'app-nav-link-active' : '' }}"
                            >
                                Admin Dashboard
                            </a>

                            <div class="pt-5">
                                <p class="px-3 text-xs font-semibold uppercase tracking-[0.25em] text-shell-muted">People</p>
                                <div class="mt-2 space-y-2">
                                    <a href="{{ route('admin.students.index') }}" class="app-nav-link {{ request()->routeIs('admin.students.*') ? 'app-nav-link-active' : '' }}">Students</a>
                                    <a href="{{ route('admin.staff.index') }}" class="app-nav-link {{ request()->routeIs('admin.staff.*') ? 'app-nav-link-active' : '' }}">Staff</a>
                                    <a href="{{ route('admin.staff-roles.index') }}" class="app-nav-link {{ request()->routeIs('admin.staff-roles.*') ? 'app-nav-link-active' : '' }}">Staff Roles</a>
                                </div>
                            </div>

                            <div class="pt-5">
                                <p class="px-3 text-xs font-semibold uppercase tracking-[0.25em] text-shell-muted">Billing Setup</p>
                                <div class="mt-2 space-y-2">
                                    <a href="{{ route('admin.finance-summary') }}" class="app-nav-link {{ request()->routeIs('admin.finance-summary') ? 'app-nav-link-active' : '' }}">Finance Summary</a>
                                    <a href="{{ route('admin.lesson-counts.index') }}" class="app-nav-link {{ request()->routeIs('admin.lesson-counts.*') ? 'app-nav-link-active' : '' }}">Monthly Lesson Counts</a>
                                    <a href="{{ route('admin.month-closing.index') }}" class="app-nav-link {{ request()->routeIs('admin.month-closing.*') ? 'app-nav-link-active' : '' }}">Month Closing</a>
                                    <a href="{{ route('admin.student-charges.index') }}" class="app-nav-link {{ request()->routeIs('admin.student-charges.*') ? 'app-nav-link-active' : '' }}">Student Charges</a>
                                    <a href="{{ route('admin.payments.index') }}" class="app-nav-link {{ request()->routeIs('admin.payments.*') ? 'app-nav-link-active' : '' }}">Student Payments</a>
                                    <a href="{{ route('admin.expenses.index') }}" class="app-nav-link {{ request()->routeIs('admin.expenses.*') ? 'app-nav-link-active' : '' }}">Expenses & Salaries</a>
                                    <a href="{{ route('admin.expense-categories.index') }}" class="app-nav-link {{ request()->routeIs('admin.expense-categories.*') ? 'app-nav-link-active' : '' }}">Expense Categories</a>
                                    <a href="{{ route('admin.bank-months.index') }}" class="app-nav-link {{ request()->routeIs('admin.bank-months.*') ? 'app-nav-link-active' : '' }}">Bank Reconciliation</a>
                                    <a href="{{ route('admin.lesson-types.index') }}" class="app-nav-link {{ request()->routeIs('admin.lesson-types.*') ? 'app-nav-link-active' : '' }}">Lesson Types</a>
                                    <a href="{{ route('admin.plans.index') }}" class="app-nav-link {{ request()->routeIs('admin.plans.*') ? 'app-nav-link-active' : '' }}">Plans</a>
                                </div>
                            </div>

                            <div class="pt-5">
                                <p class="px-3 text-xs font-semibold uppercase tracking-[0.25em] text-shell-muted">Access</p>
                                <div class="mt-2 space-y-2">
                                    <a href="{{ route('admin.users.index') }}" class="app-nav-link {{ request()->routeIs('admin.users.*') ? 'app-nav-link-active' : '' }}">User Management</a>
                                </div>
                            </div>
                        @else
                            <a
                                href="{{ route('teacher.dashboard') }}"
                                class="app-nav-link {{ request()->routeIs('teacher.dashboard') ? 'app-nav-link-active' : '' }}"
                            >
                                Teacher Dashboard
                            </a>
                            <a
                                href="{{ route('teacher.students.index') }}"
                                class="app-nav-link {{ request()->routeIs('teacher.students.*') ? 'app-nav-link-active' : '' }}"
                            >
                                My Students
                            </a>
                            <a href="{{ route('teacher.lesson-counts.index') }}" class="app-nav-link {{ request()->routeIs('teacher.lesson-counts.*') ? 'app-nav-link-active' : '' }}">
                                Monthly Lesson Counts
                            </a>
                        @endif
                    </nav>
                </div>
            </aside>

            <div class="min-h-screen">
                {{-- The header explains where the user is and exposes the current account state. --}}
                <header class="app-topbar px-6 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-shell-muted">@yield('eyebrow', 'Workspace')</p>
                            <h2 class="mt-2 text-2xl font-semibold text-shell-text">@yield('page-title', 'Dashboard')</h2>
                            <p class="mt-2 max-w-2xl text-sm text-shell-muted">@yield('page-description')</p>
                        </div>

                        <div class="app-surface-strong px-4 py-3 text-sm text-shell-muted">
                            <p class="font-semibold text-shell-text">{{ $user->username }}</p>
                            <p class="mt-1">{{ $user->role->value }} · {{ $user->is_active ? 'active' : 'inactive' }}</p>
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="app-button-secondary px-3 py-2 text-xs uppercase tracking-[0.2em]">
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main id="main-content" class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8" tabindex="-1">
                    {{-- Shared flash area for success messages coming from CRUD actions. --}}
                    @if (session('status'))
                        <div class="app-flash-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Shared validation area so every form does not repeat the same summary block. --}}
                    @if ($errors->any())
                        <div class="app-flash-error">
                            <p class="font-semibold text-shell-text">Please review the current action.</p>
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
