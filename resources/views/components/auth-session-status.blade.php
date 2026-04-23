@props(['status'])

@if ($status)
{{-- Millennium: mensaje de sesión positivo — verde discreto (no es color de marca) --}}
<div {{ $attributes->merge(['class' => 'font-medium text-sm text-emerald-700 dark:text-emerald-400']) }}>
    {{ $status }}
</div>
@endif
