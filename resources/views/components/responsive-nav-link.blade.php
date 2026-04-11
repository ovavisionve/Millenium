@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-3 sm:py-2 border-l-4 border-millennium-sand text-start text-base font-medium text-millennium-dark dark:text-millennium-sand bg-millennium-sand/20 dark:bg-millennium-dark/30 focus:outline-none focus:text-millennium-dark dark:focus:text-millennium-sand focus:bg-millennium-sand/25 focus:border-millennium-dark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-3 sm:py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-millennium-dark dark:hover:text-millennium-sand hover:bg-millennium-sand/10 dark:hover:bg-gray-700 hover:border-millennium-sand/30 dark:hover:border-millennium-sand/20 focus:outline-none focus:text-millennium-dark dark:focus:text-millennium-sand focus:bg-millennium-sand/10 focus:border-millennium-sand/30 transition duration-150 ease-in-out';
@endphp

{{-- Millennium: menú móvil — mismo criterio que nav-link (áreas táctiles amplias) --}}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
