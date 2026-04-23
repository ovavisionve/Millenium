@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-millennium-sand text-sm font-medium leading-5 text-millennium-dark dark:text-millennium-sand focus:outline-none focus:border-millennium-dark transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-millennium-dark dark:hover:text-millennium-sand hover:border-millennium-sand/40 dark:hover:border-millennium-sand/30 focus:outline-none focus:text-millennium-dark dark:focus:text-millennium-sand focus:border-millennium-sand/40 transition duration-150 ease-in-out';
@endphp

{{-- Millennium: pestaña activa arena (#DDB387); texto #321D17 --}}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
