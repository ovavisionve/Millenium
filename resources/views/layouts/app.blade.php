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
    @stack('head')
</head>

{{--
    Millennium / Incapor — layout autenticado

    Shell Breeze conservado: `@include navigation`, header de página, `main`. Estilos
    de fondo y bordes usan paleta `millennium.*` para coherencia con login y dashboard.
--}}

<body class="font-sans antialiased text-millennium-dark bg-gradient-to-b from-white to-millennium-sand/10 min-h-screen">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white/90 border-b border-millennium-dark/10 shadow-sm">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main class="flex-1 w-full min-w-0">
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
</body>

</html>