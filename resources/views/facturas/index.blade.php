<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ $tituloPagina ?? 'Facturas' }}</h2>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('facturas.canceladas') }}"><x-secondary-button type="button">Canceladas</x-secondary-button></a>
                <a href="{{ route('facturas.create') }}"><x-primary-button type="button">Nueva factura</x-primary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <form method="get" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 items-end">
                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" class="mt-1 block w-full" value="{{ request('desde') }}" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="mt-1 block w-full" value="{{ request('hasta') }}" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="cliente_id" value="Cliente" />
                    <select id="cliente_id" name="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        @foreach ($clientes as $c)
                        <option value="{{ $c->id }}" @selected((string) request('cliente_id')===(string) $c->id)>{{ $c->nombre_razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="cartera" value="Cartera" />
                    <select id="cartera" name="cartera" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">Todas</option>
                        @foreach ($etiquetasCartera as $key => $label)
                        <option value="{{ $key }}" @selected(request('cartera')===$key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="estado_pago" value="Estado pago" />
                    <select id="estado_pago" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        <option value="{{ \App\Models\Factura::ESTADO_PAGO_ABIERTA }}" @selected(request('estado_pago')===\App\Models\Factura::ESTADO_PAGO_ABIERTA)>Abierta</option>
                        <option value="{{ \App\Models\Factura::ESTADO_PAGO_PAGADA }}" @selected(request('estado_pago')===\App\Models\Factura::ESTADO_PAGO_PAGADA)>Pagada</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 pb-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="solo_sin_verificar" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(request('solo_sin_verificar')) />
                        Sin verificar
                    </label>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <x-secondary-button type="submit" class="h-10">Filtrar</x-secondary-button>
                    @if (request()->anyFilled(['desde', 'hasta', 'cliente_id', 'cartera', 'estado_pago']) || request('solo_sin_verificar'))
                    <a href="{{ route('facturas.index') }}" class="text-sm self-center text-gray-600 dark:text-gray-400 hover:underline">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden text-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-3 py-2 font-medium">Nº</th>
                                <th class="px-3 py-2 font-medium">Cliente</th>
                                <th class="px-3 py-2 font-medium">Emisión</th>
                                <th class="px-3 py-2 font-medium">Vence</th>
                                <th class="px-3 py-2 font-medium text-end">Total</th>
                                <th class="px-3 py-2 font-medium text-end">Saldo</th>
                                <th class="px-3 py-2 font-medium">Pago</th>
                                <th class="px-3 py-2 font-medium">Cartera</th>
                                <th class="px-3 py-2 font-medium" title="Verificación de precios (Fatimar)">Fatimar</th>
                                <th class="px-3 py-2 font-medium text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($facturas as $f)
                            @php $ec = $f->estadoCartera(); @endphp
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-3 py-2 font-mono text-xs">{{ $f->numero_factura ?? '#'.$f->id }}</td>
                                <td class="px-3 py-2">{{ $f->cliente->nombre_razon_social }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_emision->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-end">${{ number_format($f->total, 2) }}</td>
                                <td class="px-3 py-2 text-end">${{ number_format($f->saldo_pendiente, 2) }}</td>
                                <td class="px-3 py-2">
                                    @if ($f->estado_pago === \App\Models\Factura::ESTADO_PAGO_PAGADA)
                                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Pagada</span>
                                    @else
                                    <span class="px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-800 dark:bg-slate-600 dark:text-slate-100">Abierta</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <span @class([ 'px-2 py-0.5 rounded text-xs' , 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'=> $ec === \App\Models\Factura::CARTERA_VENCIDA,
                                        'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => $ec === \App\Models\Factura::CARTERA_POR_VENCER,
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $ec === \App\Models\Factura::CARTERA_AL_DIA,
                                        ])>{{ $etiquetasCartera[$ec] ?? $ec }}</span>
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    @if ($f->verificadoPor)
                                    <span class="text-green-700 dark:text-green-300">{{ $f->verificadoPor->name }}</span>
                                    @if ($f->fecha_verificacion)
                                    <span class="block text-gray-500 dark:text-gray-400">{{ $f->fecha_verificacion->format('d/m H:i') }}</span>
                                    @endif
                                    @else
                                    <span class="text-amber-700 dark:text-amber-300">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-end whitespace-nowrap">
                                    <a href="{{ route('facturas.show', $f) }}" class="text-millennium-dark dark:text-millennium-sand hover:underline">Ver</a>
                                    <a href="{{ route('facturas.edit', $f) }}" class="text-millennium-dark dark:text-millennium-sand hover:underline ms-2">Editar</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">No hay facturas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($facturas->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">{{ $facturas->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>