<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased font-sans text-gray-900 bg-gray-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#10b981">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="HTMS">
        <title inertia>CMS (Church Manage System)</title>
        <link rel="icon" type="image/png" href="{{ asset('LOGO.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
        <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50 overflow-y-auto">
        @inertia
    </body>
</html>
