<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'AvinyaPay') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%23ffffff'/><path d='M16 16c-2.67-3.56-5.33-5.33-8-5.33a5.33 5.33 0 1 0 0 10.66c2.67 0 5.33-1.77 8-5.33Zm0 0c2.67 3.56 5.33 5.33 8 5.33a5.33 5.33 0 0 0 0-10.66c-2.67 0-5.33 1.77-8 5.33Z' fill='none' stroke='%23000000' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'/></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full text-[#fafafc] antialiased selection:bg-[#38bdf8]/20 selection:text-white relative" style="background: transparent;">
    {{-- GoCrazy 3D Neon Tube & Ambient Background across all pages --}}
    <x-background />

    @yield('body')
    @stack('scripts')
</body>
</html>
