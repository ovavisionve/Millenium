<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Estado de cuenta — {{ $cliente->nombre_razon_social }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('cuentas-por-cobrar.estado-cuenta-pdf', $cliente) }}"><x-primary-button type="button">Descargar PDF</x-primary-button></a>
                <a href="{{ route('cobranza.cliente', $cliente) }}"><x-secondary-button type="button">Cobranza</x-secondary-button></a>
                <a href="{{ route('cuentas-por-cobrar.index') }}"><x-secondary-button type="button">Por zona</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border border-millennium-dark/10 dark:border-gray-700 space-y-2 text-sm">
                <p><span class="text-gray-500 dark:text-gray-400">Identificación:</span> <strong>{{ $cliente->full_identificacion }}</strong></p>
                <p><span class="text-gray-500 dark:text-gray-400">Zona:</span> {{ $cliente->zona ?: '—' }}</p>
                @if ($cliente->vendedor)
                <p><span class="text-gray-500 dark:text-gray-400">Vendedor:</span> {{ $cliente->vendedor->name }}</p>
                @endif
                <p class="pt-2 text-lg font-semibold text-amber-800 dark:text-amber-200">Total saldo pendiente: ${{ number_format($totalSaldo, 2) }} USD</p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden border border-millennium-dark/10 dark:border-gray-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 font-medium">Facturas con saldo</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Factura</th>
                                <th class="px-4 py-2 text-left">Emisión</th>
                                <th class="px-4 py-2 text-left">Vence</th>
                                <th class="px-4 py-2 text-end">Total</th>
                                <th class="px-4 py-2 text-end">Saldo</th>
                                <th class="px-4 py-2 text-left">Cartera</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($facturas as $f)
                            @php $ec = $f->estadoCartera(); @endphp
                            <tr>
                                <td class="px-4 py-2">
                                    <a href="{{ route('facturas.show', $f) }}" class="font-medium text-millennium-dark dark:text-millennium-sand hover:underline">{{ $f->numero_factura ?? '#'.$f->id }}</a>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $f->fecha_emision->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-end">${{ number_format($f->total, 2) }}</td>
                                <td class="px-4 py-2 text-end font-medium">${{ number_format($f->saldo_pendiente, 2) }}</td>
                                <td class="px-4 py-2">
                                    <span @class([
                                        'text-xs px-2 py-0.5 rounded',
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' => $ec === \App\Models\Factura::CARTERA_VENCIDA,
                                        'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => $ec === \App\Models\Factura::CARTERA_POR_VENCER,
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $ec === \App\Models\Factura::CARTERA_AL_DIA,
                                    ])>{{ $etiquetasCartera[$ec] ?? $ec }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">Sin facturas con saldo (si registraste un pago reciente, la cartera ya quedó al día).</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold">
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-end">Total</td>
                                <td class="px-4 py-2 text-end">${{ number_format($totalSaldo, 2) }}</td>
                                <td class="px-4 py-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
