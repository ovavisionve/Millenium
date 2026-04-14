<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar cliente</h2>
    </x-slot>

    {{-- Millennium — mismo partial que create; cliente_id para ignorar duplicado propio --}}
    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @include('maestros.clientes.partials.form-fields', [
                    'cliente' => $cliente,
                    'vendedores' => $vendedores,
                    'tiposDocumento' => $tiposDocumento,
                    'estados' => $estados,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
