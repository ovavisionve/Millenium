<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categorias.index') }}"
               class="inline-flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-millennium-sand">
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 4.158a.75.75 0 1 1-1.06 1.06l-5.5-5.5a.75.75 0 0 1 0-1.06l5.5-5.5a.75.75 0 1 1 1.06 1.06L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                </svg>
                <span>Atrás</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nueva categoría</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Cada <strong>categoría</strong> representa una línea de venta (p. ej. <em>Vaca</em>, <em>Búfalo</em>) con <strong>código único</strong> para filtros y reportes.
                En la factura elegís la categoría, <strong>unidad/Kilos</strong> (piezas o kg según cómo facturás esa línea) y <strong>precio</strong>; la zona y el vendedor del <strong>cliente</strong> siguen alimentando reportes por sector y por vendedor.
            </p>
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('categorias.store') }}" class="space-y-4">
                    @csrf
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
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand">{{ old('descripcion') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
                    </div>
                    <div>
                        <x-input-label for="unidad" value="Cómo se factura esta línea" />
                        <select id="unidad" name="unidad" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                            @foreach (\App\Models\Categoria::unidadLabels() as $val => $label)
                                <option value="{{ $val }}" @selected(old('unidad', \App\Models\Categoria::UNIDAD_UNIDAD) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Por defecto pieza/unidad. Solo elegí kg si esa línea se factura por peso en balanza.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('unidad')" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="activo" name="activo" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(old('activo', true)) />
                        <x-input-label for="activo" value="Categoría activa (aparece al facturar)" class="!mb-0" />
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Guardar</x-primary-button>
                        <a href="{{ route('categorias.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>