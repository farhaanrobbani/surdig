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
    <body class="font-sans text-gray-900 antialiased dark:text-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-teal-50 via-emerald-50 to-white dark:from-gray-900 dark:via-gray-900 dark:to-teal-950">
            <div>
                <a href="/">
                    @php($logoUrl = \App\Models\KuaSetting::logoUrl())
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo KUA" class="h-20 w-20 object-contain" />
                    @else
                        <x-application-logo class="w-20 h-20 fill-current text-teal-700" />
                    @endif
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white border border-teal-100 shadow-sm overflow-hidden sm:rounded-lg dark:bg-gray-800 dark:border-teal-900 animate-fade-up">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
