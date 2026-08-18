<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#f8fafc">
        <title>{{ $title ?? 'Mi Rutina' }} · {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-dvh bg-slate-50 text-slate-900 antialiased">
        <div class="mx-auto flex min-h-dvh max-w-xl flex-col">
            {{ $slot }}
        </div>

        <x-toast />

        @livewireScripts
    </body>
</html>
