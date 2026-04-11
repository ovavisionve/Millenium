<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nueva categoría</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Las <strong>categorías</strong> agrupan productos (ej. Vaca, Búfalo, Trastes) con un <strong>código único</strong> para filtros y reportes.
                Cada <strong>producto</strong> lleva cantidad en <strong>kg</strong> o unidad y precio en la factura; la zona y el vendedor del <strong>cliente</strong> alimentan reportes por sector y por vendedor.
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
                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Guardar</x-primary-button>
                        <a href="{{ route('categorias.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>