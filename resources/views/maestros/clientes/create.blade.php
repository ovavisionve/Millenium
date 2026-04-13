<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nuevo cliente</h2>
    </x-slot>

    {{-- Millennium — formulario unificado con validación en vivo (ver partial) --}}
    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @include('maestros.clientes.partials.form-fields', [
                    'cliente' => null,
                    'vendedores' => $vendedores,
                    'tiposDocumento' => $tiposDocumento,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
