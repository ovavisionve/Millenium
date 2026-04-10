<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nuevo producto</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('productos.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="categoria_id" value="Categoría" />
                        <select id="categoria_id" name="categoria_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">— Seleccione —</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}" @selected(old('categoria_id') == $cat->id)>{{ $cat->nombre }} ({{ $cat->codigo }})</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('categoria_id')" />
                    </div>
                    <div>
                        <x-input-label for="codigo" value="Código único" />
                        <x-text-input id="codigo" name="codigo" type="text" class="mt-1 block w-full font-mono" :value="old('codigo')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('codigo')" />
                    </div>
                    <div>
                        <x-input-label for="nombre" value="Nombre" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>
                    <div>
                        <x-input-label for="descripcion" value="Descripción detallada (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="4" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">{{ old('descripcion') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                    </div>
                    <div>
                        <x-input-label for="unidad" value="Unidad" />
                        <select id="unidad" name="unidad" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            @foreach ($unidades as $u)
                                <option value="{{ $u }}" @selected(old('unidad', 'kg') === $u)>{{ $unidadLabels[$u] }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('unidad')" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="activo" value="0">
                        <input id="activo" name="activo" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm" {{ old('activo', true) ? 'checked' : '' }}>
                        <x-input-label for="activo" value="Producto activo" class="!mb-0" />
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Guardar</x-primary-button>
                        <a href="{{ route('productos.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
