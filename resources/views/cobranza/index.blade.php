<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Cobranza</h2>
            <a href="{{ route('facturas.index') }}"><x-secondary-button type="button">Facturas</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Busca un cliente por nombre, zona o documento para ver sus facturas con saldo pendiente y registrar abonos.
            </p>

            <form method="get" action="{{ route('cobranza.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <x-input-label for="q" value="Buscar cliente" />
                    <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" value="{{ old('q', $q) }}" placeholder="Nombre, zona o documento" autofocus />
                </div>
                <x-primary-button type="submit" class="h-10">Buscar</x-primary-button>
            </form>

            @if ($q !== '')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    @if ($clientes->isEmpty())
                        <p class="p-6 text-sm text-gray-500">No se encontraron clientes.</p>
                    @else
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($clientes as $c)
                                <li>
                                    <a href="{{ route('cobranza.cliente', $c) }}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $c->nombre_razon_social }}</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400"> · {{ $c->full_identificacion }}</span>
                                        @if ($c->zona)
                                            <span class="text-xs text-gray-400"> · {{ $c->zona }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
