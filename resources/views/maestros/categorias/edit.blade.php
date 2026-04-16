<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar categoría</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('categorias.update', $categoria) }}" class="space-y-4">
                    @csrf @method('patch')
                    <div>
                        <x-input-label for="codigo" value="Código único" />
                        <x-text-input id="codigo" name="codigo" type="text" class="mt-1 block w-full font-mono" :value="old('codigo', $categoria->codigo)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('codigo')" />
                    </div>
                    <div>
                        <x-input-label for="nombre" value="Nombre" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $categoria->nombre)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>
                    <div>
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                    </div>
                    <div>
                        <x-input-label for="unidad" value="Cómo se factura esta línea" />
                        <select id="unidad" name="unidad" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                            @foreach (\App\Models\Categoria::unidadLabels() as $val => $label)
                                <option value="{{ $val }}" @selected(old('unidad', $categoria->unidad) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Por defecto pieza/unidad. Solo elegí kg si esa línea se factura por peso en balanza.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('unidad')" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="activo" name="activo" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(old('activo', $categoria->activo)) />
                        <x-input-label for="activo" value="Categoría activa (aparece al facturar)" class="!mb-0" />
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Actualizar</x-primary-button>
                        <a href="{{ route('categorias.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>