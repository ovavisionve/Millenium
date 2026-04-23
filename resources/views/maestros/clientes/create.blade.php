<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}"
               class="inline-flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-millennium-sand">
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 4.158a.75.75 0 1 1-1.06 1.06l-5.5-5.5a.75.75 0 0 1 0-1.06l5.5-5.5a.75.75 0 1 1 1.06 1.06L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                </svg>
                <span>Atrás</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nuevo cliente</h2>
        </div>
    </x-slot>

    {{-- Millennium — formulario unificado con validación en vivo (ver partial) --}}
    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @include('maestros.clientes.partials.form-fields', [
                    'cliente' => null,
                    'vendedores' => $vendedores,
                    'tiposDocumento' => $tiposDocumento,
                    'estados' => $estados,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
