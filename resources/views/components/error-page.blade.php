@props(['code', 'title', 'message'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $code }} · {{ $title }} | MICS HUB</title>@vite(['resources/css/app.css'])</head>
<body class="app-page grid min-h-screen place-items-center p-6"><main class="app-surface w-full max-w-xl p-8 text-center"><p class="text-sm font-semibold tracking-[0.3em] text-brand-300">MICS HUB · {{ $code }}</p><h1 class="mt-4 text-3xl font-semibold">{{ $title }}</h1><p class="mt-4 text-shell-muted">{{ $message }}</p><a class="app-button-primary mt-6" href="{{ url('/') }}">{{ __('messages.return_to_hub') }}</a></main></body>
</html>
