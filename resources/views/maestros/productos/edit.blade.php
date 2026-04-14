<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar producto</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('productos.update', $producto) }}" class="space-y-4">
                    @csrf @method('patch')
                    <div>
                        <x-input-label for="categoria_id" value="Categoría" />
                        <select id="categoria_id" name="categoria_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categoria_id', $producto->categoria_id) == $cat->id)>{{ $cat->nombre }} ({{ $cat->codigo }})</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('categoria_id')" />
                    </div>
                    <div>
                        <x-input-label value="Código único" />
                        <p class="mt-1 text-sm font-mono rounded-md border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 px-3 py-2 text-gray-800 dark:text-gray-200">{{ $producto->codigo }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Asignado por el sistema; no se modifica.</p>
                    </div>
                    <div>
                        <x-input-label for="nombre" value="Nombre estándar" />
                        <select id="nombre" name="nombre" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            @php
                                $nombreActual = old('nombre', $producto->nombre);
                                $enLista = array_key_exists($nombreActual, $nombresPredeterminados);
                            @endphp
                            @if (! $enLista && $nombreActual !== '')
                                <option value="{{ $nombreActual }}" selected>— Valor anterior (no estándar): {{ $nombreActual }} —</option>
                            @endif
                            @foreach ($nombresPredeterminados as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected($nombreActual === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Podés pasar un producto viejo a un nombre estándar eligiendo otra opción.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>
                    <div>
                        <x-input-label for="descripcion" value="Descripción detallada (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                    </div>
                    <div>
                        <x-input-label for="unidad" value="Cómo se factura" />
                        <select id="unidad" name="unidad" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            @foreach ($unidades as $u)
                            <option value="{{ $u }}" @selected(old('unidad', $producto->unidad) === $u)>{{ $unidadLabels[$u] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><strong>Pieza/unidad</strong> para cantidad normal; <strong>kg</strong> solo si la línea es por balanza.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('unidad')" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="activo" value="0">
                        <input id="activo" name="activo" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700 text-millennium-dark shadow-sm focus:ring-millennium-sand" {{ old('activo', $producto->activo) ? 'checked' : '' }}>
                        <x-input-label for="activo" value="Producto activo" class="!mb-0" />
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Guardar cambios</x-primary-button>
                        <a href="{{ route('productos.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>