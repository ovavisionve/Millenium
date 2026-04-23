<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Aplicar saldo a favor · {{ $cliente->nombre_razon_social }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $cliente->full_identificacion }}</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('cobranza.cliente', $cliente) }}"><x-secondary-button type="button">Volver</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden text-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 font-medium">Disponible</div>
                <div class="p-4">
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                        Saldo a favor disponible: <span class="font-semibold">${{ number_format((float) ($saldoAFavorUsd ?? 0), 2) }}</span>
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden text-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 font-medium">Aplicar a facturas</div>
                <form method="post" action="{{ route('cobranza.saldo-favor.store', $cliente) }}" class="p-4 space-y-4">
                    @csrf

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="fecha_recibo" value="Fecha del recibo / aplicación" />
                            <x-text-input id="fecha_recibo" name="fecha_recibo" type="date" class="mt-1 block w-full" value="{{ old('fecha_recibo', now()->toDateString()) }}" required />
                            <x-input-error :messages="$errors->get('fecha_recibo')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="notas" value="Notas (opcional)" />
                            <x-text-input id="notas" name="notas" type="text" class="mt-1 block w-full" value="{{ old('notas') }}" maxlength="2000" />
                            <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                        </div>
                    </div>

                    <x-input-error :messages="$errors->get('abonos')" class="mt-2" />

                    @if ($facturas->isEmpty())
                    <p class="text-gray-500">No hay facturas con saldo para este cliente.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-3 py-2 font-medium">Factura</th>
                                    <th class="px-3 py-2 font-medium">Emisión</th>
                                    <th class="px-3 py-2 font-medium text-end">Saldo (USD)</th>
                                    <th class="px-3 py-2 font-medium text-end">Aplicar (USD)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($facturas as $f)
                                <tr class="text-gray-900 dark:text-gray-100">
                                    <td class="px-3 py-2 font-mono text-xs">{{ $f->numero_factura ?? '#'.$f->id }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_emision->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-end font-medium">${{ number_format((float) $f->saldo_pendiente, 2) }}</td>
                                    <td class="px-3 py-2 text-end">
                                        <input
                                            name="abonos[{{ $f->id }}]"
                                            type="text"
                                            inputmode="decimal"
                                            class="w-28 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-millennium-sand focus:ring-millennium-sand"
                                            value="{{ old('abonos.'.$f->id) }}"
                                            placeholder="0.00"
                                        />
                                        <x-input-error :messages="$errors->get('abonos.'.$f->id)" class="mt-2" />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <div class="flex justify-end gap-2 pt-2">
                        <a href="{{ route('cobranza.cliente', $cliente) }}"><x-secondary-button type="button">Cancelar</x-secondary-button></a>
                        <x-primary-button type="submit">Aplicar saldo a favor</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

