<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{--
    Millennium / Incapor — layout invitado (login, registro, etc.)

    Cambios respecto a Breeze: gradiente arena (`millennium.sand`), borde y texto marca;
    arriba del formulario va el logo Incapor en variante `guest` (sustituye título texto).
--}}

<body class="font-sans text-millennium-dark antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-white via-millennium-sand/10 to-millennium-sand/20">
        <div class="px-4 text-center">
            <a href="/" class="inline-block focus:outline-none focus:ring-2 focus:ring-millennium-sand focus:ring-offset-2 rounded-md">
                <x-brand-logo variant="guest" />
                <span class="sr-only">{{ config('app.name', 'Millennium') }}</span>
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-4 sm:px-6 py-4 bg-white/95 border border-millennium-dark/10 shadow-sm overflow-hidden sm:rounded-xl">
            {{ $slot }}
        </div>
    </div>
</body>

</html>