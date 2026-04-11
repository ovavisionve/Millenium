@props(['messages'])

@if ($messages)
{{-- Millennium: errores de validación — rosa/rojo legible; role=alert para lectores de pantalla --}}
<ul role="alert" {{ $attributes->merge(['class' => 'text-sm text-rose-700 dark:text-rose-300 space-y-1']) }}>
    @foreach ((array) $messages as $message)
    <li>{{ $message }}</li>
    @endforeach
</ul>
@endif
