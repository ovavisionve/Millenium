<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Estados de cuenta</h2>
            <a href="{{ route('dashboard') }}"><x-secondary-button type="button">{{ __('Inicio') }}</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 px-1">
                Tocá una <strong>zona</strong> a la izquierda y se listan los <strong>clientes</strong> con saldo en esa zona. En cada fila: <strong>Detalle</strong> (facturas y total que debe) o <strong>PDF</strong> para enviar al cliente.
                Al registrar un pago en <strong>Cobranza</strong>, los saldos y este listado se actualizan solos (no requiere mantenimiento manual).
            </p>

            {{-- Grid + minmax(0,1fr): evita que en pantallas muy anchas la columna de la tabla colapse (bug típico flex + min-w-0) y deje el total “flotando” a la derecha --}}
            <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:items-start">
                <aside class="w-full max-w-full lg:sticky lg:top-4 lg:self-start">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-millennium-dark/10 dark:border-gray-700 overflow-hidden">
                        <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-600 font-medium text-sm text-gray-800 dark:text-gray-100">Zona</div>
                        <nav class="p-2 space-y-0.5 text-sm">
                            <a href="{{ route('cuentas-por-cobrar.index') }}"
                               class="block rounded px-2 py-1.5 {{ $zonaSeleccionada === '' ? 'bg-millennium-sand/25 text-millennium-dark dark:text-millennium-sand font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                Todas las zonas
                            </a>
                            @foreach ($zonas as $z)
                            @php
                                $key = (string) $z;
                                $isSin = $key === '';
                                $hrefZ = $isSin ? '_sin_zona' : $key;
                                $label = $isSin ? 'Sin zona' : $key;
                                $zonaActiva = $isSin ? $zonaSeleccionada === '_sin_zona' : $zonaSeleccionada === $key;
                            @endphp
                            <a href="{{ route('cuentas-por-cobrar.index', ['zona' => $hrefZ]) }}"
                               class="block rounded px-2 py-1.5 {{ $zonaActiva ? 'bg-millennium-sand/25 text-millennium-dark dark:text-millennium-sand font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                {{ $label }}
                            </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <div class="w-full min-w-0 max-w-full">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-millennium-dark/10 dark:border-gray-700 w-full max-w-full">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">Clientes con saldo</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $clientes->count() }} cliente(s) · en cada fila: Detalle o PDF</p>
                        </div>
                        <div class="overflow-x-auto w-full max-w-full">
                            <table class="w-full min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Cliente</th>
                                        <th class="px-4 py-2 text-left">Zona</th>
                                        <th class="px-4 py-2 text-left">Vendedor</th>
                                        <th class="px-4 py-2 text-end">Saldo USD</th>
                                        <th class="px-4 py-2 text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse ($clientes as $c)
                                    <tr>
                                        <td class="px-4 py-2 font-medium">{{ $c->nombre_razon_social }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $c->zona ?: '—' }}</td>
                                        <td class="px-4 py-2">{{ $c->vendedor?->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-end font-semibold">${{ number_format($c->deuda_total, 2) }}</td>
                                        <td class="px-4 py-2 text-end whitespace-nowrap space-x-2">
                                            <a href="{{ route('cuentas-por-cobrar.show', $c) }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Detalle</a>
                                            <a href="{{ route('cuentas-por-cobrar.estado-cuenta-pdf', $c) }}" class="text-gray-600 dark:text-gray-400 hover:underline">PDF</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay clientes con saldo en este filtro.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 px-4 py-4 bg-gray-50/80 dark:bg-gray-700/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total cartera (filtro actual)</p>
                                <p class="text-2xl font-semibold text-amber-800 dark:text-amber-200">${{ number_format($totalCartera, 2) }}</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 sm:max-w-md sm:text-end">
                                Suma de saldos pendientes de los clientes listados arriba.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
