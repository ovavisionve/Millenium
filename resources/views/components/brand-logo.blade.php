@props([
    'variant' => 'nav',
])

{{--
    Millennium / Incapor — componente de marca (logo)

    Qué hicimos: sustituir el texto del nombre del aplicativo por el logotipo Incapor
    en barra (nav), login (guest) y, si se usa, variantes compactas. No usamos SVG
    embebido en Blade: los activos son PNG vectoriales sin fondo en
    `public/images/millennium/` (ver README en esa carpeta).

    Límites solo con Tailwind (sin `style=""`): el validador CSS del editor se confundía
    con `{{ $style }}` dentro del atributo. El contenedor + `max-h`/`max-w` equivalentes
    mantienen el mismo comportamiento que el inline anterior.

    Variantes: `nav` (barra), `guest` (auth centrado), `inline` (cabeceras si se reactiva),
    `on-dark` (trazo blanco sobre fondo oscuro).
--}}
@php
    $useWhite = $variant === 'on-dark';
    $src = $useWhite
        ? asset('images/millennium/millenium-vectores-blanco.png')
        : asset('images/millennium/millenium-vectores.png');

    $box = match ($variant) {
        'guest' => 'max-h-24 max-w-[260px]',
        'inline' => 'max-h-8 max-w-[120px]',
        'on-dark' => 'max-h-9 max-w-[170px]',
        default => 'max-h-9 max-w-[148px]',
    };

    $imgClass = match ($variant) {
        'guest' => 'max-h-24 max-w-[260px] w-auto h-auto',
        'inline' => 'max-h-8 max-w-[120px] w-auto h-auto',
        'on-dark' => 'max-h-9 max-w-[170px] w-auto h-auto',
        default => 'max-h-9 max-w-[148px] w-auto h-auto',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center overflow-hidden', $box]) }}>
    <img
        src="{{ $src }}"
        alt="Incapor"
        class="block object-contain object-left {{ $imgClass }}"
        loading="lazy"
        decoding="async"
    />
</span>
