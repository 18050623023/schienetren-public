<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <script>
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Reparasi</title>
    <!-- x-cloak removed for debugging -->
    <link href="{{ asset('css/filament/filament/app.css') }}" rel="stylesheet" />
    @vite('resources/css/app.css')
    @filamentStyles
</head>
<body class="fi-body antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
    {{ $slot }}
    @filamentScripts
    @vite('resources/js/app.js')
</body>
</html>
