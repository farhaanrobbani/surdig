<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            (function () {
                const stored = localStorage.getItem('theme');
                const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            })();
        </script>

        <link rel="icon" href="{{ \App\Models\KuaSetting::logoUrl() ?: asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === '1' }" class="min-h-screen bg-gradient-to-br from-teal-50 via-emerald-50 to-white dark:from-gray-900 dark:via-gray-900 dark:to-teal-950">
            @include('layouts.navigation')

            <!-- Mobile overlay -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" x-transition.opacity
                 class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden"></div>

            <div class="flex min-h-screen flex-col transition-[padding] duration-200 ease-in-out"
                 :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'">
                <!-- Mobile topbar -->
                <div class="sticky top-0 z-10 flex h-16 items-center justify-between bg-teal-700 px-4 lg:hidden">
                    <button @click="sidebarOpen = ! sidebarOpen" class="p-2 text-teal-200 hover:text-white">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2">
                        @if (\App\Models\KuaSetting::logoUrl())
                            <img src="{{ \App\Models\KuaSetting::logoUrl() }}" alt="Logo {{ kua_setting('instansi', 'KUA') }}"
                                 class="h-8 w-8 shrink-0 rounded-md bg-white p-0.5 object-contain" />
                        @endif
                        <span class="truncate text-sm font-semibold text-white">{{ kua_setting('instansi', config('app.name')) }}</span>
                    </a>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white/80 backdrop-blur border-b border-teal-100 dark:bg-gray-900/80 dark:border-teal-900">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex h-16 items-center">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 animate-fade-up">
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('editor')
    </body>
</html>
