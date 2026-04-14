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
                Elegí un cliente en la lista o buscalo por texto. En la siguiente pantalla verás sus facturas con saldo: marcá en cuáles abonás e indicá el monto en USD por cada una (un mismo recibo puede repartirse en varias filas).
            </p>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-gray-200 dark:border-gray-600 space-y-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Filtrar por cliente</p>
                <form method="get" action="{{ route('cobranza.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1 min-w-0">
                        <x-input-label for="cliente_id" value="Cliente" />
                        <select id="cliente_id" name="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-sand focus:ring-millennium-sand">
                            <option value="">— Elegir cliente —</option>
                            @foreach ($clientesTodos as $c)
                            <option value="{{ $c->id }}" @selected((string) old('cliente_id', request('cliente_id')) === (string) $c->id)>{{ $c->nombre_razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button type="submit" class="h-10 shrink-0">Ver facturas y abonar</x-primary-button>
                </form>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400">O buscá por nombre, zona o documento:</p>

            <form method="get" action="{{ route('cobranza.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <x-input-label for="q" value="Buscar cliente" />
                    <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" value="{{ old('q', $q) }}" placeholder="Nombre, zona o documento" />
                </div>
                <x-secondary-button type="submit" class="h-10">Buscar</x-secondary-button>
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
