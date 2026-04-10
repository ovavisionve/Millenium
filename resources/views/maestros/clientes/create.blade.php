<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nuevo cliente</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('clientes.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label value="Identificación" />
                        <div class="mt-1 flex gap-2 items-stretch">
                            <select
                                id="tipo_documento"
                                name="tipo_documento"
                                class="shrink-0 w-[4.5rem] rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                @foreach ($tiposDocumento as $codigo => $etiqueta)
                                    <option value="{{ $codigo }}" title="{{ $etiqueta }}" @selected(old('tipo_documento', 'V') === $codigo)>{{ $codigo }}</option>
                                @endforeach
                            </select>
                            <x-text-input
                                id="documento_numero"
                                name="documento_numero"
                                type="text"
                                class="flex-1 min-w-0 font-mono"
                                :value="old('documento_numero')"
                                placeholder="Ej. 12345678 o J-12345678-9"
                                required
                                autocomplete="off"
                            />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Prefijo según tipo (V, E, J, G, P) y número. La combinación debe ser única.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('tipo_documento')" />
                        <x-input-error class="mt-2" :messages="$errors->get('documento_numero')" />
                    </div>
                    <div>
                        <x-input-label for="nombre_razon_social" value="Nombre o razón social" />
                        <x-text-input id="nombre_razon_social" name="nombre_razon_social" type="text" class="mt-1 block w-full" :value="old('nombre_razon_social')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre_razon_social')" />
                    </div>
                    <div>
                        <x-input-label for="telefono" value="Teléfono (opcional)" />
                        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono')" />
                        <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                    </div>
                    <div>
                        <x-input-label for="zona" value="Zona / sector / ruta" />
                        <x-text-input id="zona" name="zona" type="text" class="mt-1 block w-full" :value="old('zona')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('zona')" />
                    </div>
                    <div>
                        <x-input-label for="vendedor_id" value="Vendedor asignado (opcional)" />
                        <select id="vendedor_id" name="vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">— Sin asignar —</option>
                            @foreach ($vendedores as $v)
                                <option value="{{ $v->id }}" @selected(old('vendedor_id') == $v->id)>{{ $v->name }} — {{ \App\Models\User::roleLabels()[$v->role] ?? $v->role }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('vendedor_id')" />
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Guardar</x-primary-button>
                        <a href="{{ route('clientes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
