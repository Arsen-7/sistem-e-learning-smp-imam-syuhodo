<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E-Learning Imam Syuhodo') }}</title>
        <link rel="icon" href="{{ asset('images/logo-smp.svg') }}" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-4 py-8">
            <div>
                <a href="/" class="flex flex-col items-center gap-3 text-center">
                    <x-application-logo class="h-28 w-28" />
                    <span class="space-y-1">
                        <span class="block text-lg font-extrabold text-slate-950">E-Learning Imam Syuhodo</span>
                        <span class="block text-sm font-medium text-slate-500">SMP Muhammadiyah Imam Syuhodo</span>
                    </span>
                </a>
            </div>

            <div class="mt-6 w-full max-w-md overflow-hidden rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-slate-200">
                {{ $slot }}
            </div>
        </div>

        <x-confirm-dialog />
    </body>
</html>
