<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', kua_setting('instansi', 'Surat Digital KUA'))</title>

        <link rel="icon" href="{{ \App\Models\KuaSetting::logoUrl() ?: asset('favicon.ico') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')
    </head>
    <body class="min-h-screen flex flex-col bg-gradient-to-br from-teal-50 via-emerald-50 to-white text-[#1b1b18] font-sans antialiased">
        @include('partials.public-header')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.public-footer')
    </body>
</html>
