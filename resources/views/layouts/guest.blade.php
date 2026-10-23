<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Task Tracker') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 pb-8 px-4 bg-gradient-to-br from-indigo-50/90 via-sky-50/70 to-slate-100 relative overflow-hidden">
            {{-- Decorative ambient background glows --}}
            <div class="absolute -top-32 -left-32 w-80 h-80 bg-indigo-200/50 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-sky-200/50 rounded-full blur-3xl pointer-events-none"></div>

            <div class="text-center z-10 mb-2">
                <a href="/" class="inline-flex flex-col items-center gap-3 group">
                    <div class="relative transition-transform duration-300 group-hover:scale-105">
                        <x-application-logo class="w-16 h-16 shadow-xl shadow-indigo-500/25 rounded-2xl" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center justify-center gap-1.5">
                            <span>Task<span class="text-indigo-600">Tracker</span></span>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 rounded-md border border-indigo-200/70">Pro</span>
                        </h1>
                        <p class="text-xs font-medium text-slate-500 mt-0.5">
                            Sistem Monitoring & Manajemen Tugas
                        </p>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 px-8 py-7 bg-white shadow-xl shadow-indigo-100/50 border border-slate-200/80 rounded-2xl z-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
