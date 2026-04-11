<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Reportes</h2>
            <a href="{{ route('dashboard') }}"><x-secondary-button type="button">{{ __('Dashboard') }}</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <p class="text-sm text-gray-600 dark:text-gray-300 px-1">
                Combiná filtros (vendedor del cliente, zona, fechas, producto, categoría) para ver <strong>precio unitario</strong>, <strong>subtotal por línea</strong> y <strong>monto total de la factura</strong>.
                La columna <strong>Verificación Fatimar</strong> muestra qué usuario confirmó los precios y cuándo (mismo dato que en el detalle de la factura).
                Podés marcar <strong>solo sin verificar</strong> para revisar pendientes (por ejemplo, facturas de un día).
                El <strong>estado de cuenta</strong> lista facturas del cliente (vencidas / no vencidas) con verificación.
            </p>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Ventas y líneas (filtros cruzados)</h3>
                <form method="get" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 items-end">
                    <input type="hidden" name="generar" value="1" />
                    <div>
                        <x-input-label for="desde" value="Desde (emisión)" />
                        <x-text-input id="desde" name="desde" type="date" class="mt-1 block w-full" value="{{ request('desde') }}" />
                    </div>
                    <div>
                        <x-input-label for="hasta" value="Hasta (emisión)" />
                        <x-text-input id="hasta" name="hasta" type="date" class="mt-1 block w-full" value="{{ request('hasta') }}" />
                    </div>
                    <div>
                        <x-input-label for="vendedor_id" value="Vendedor (cliente)" />
                        <select id="vendedor_id" name="vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            @foreach ($vendedores as $v)
                            <option value="{{ $v->id }}" @selected((string) request('vendedor_id')===(string) $v->id)>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="zona" value="Zona (contiene)" />
                        <x-text-input id="zona" name="zona" type="text" class="mt-1 block w-full" value="{{ request('zona') }}" placeholder="Ej. Centro" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="producto_id" value="Producto" />
                        <select id="producto_id" name="producto_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            @foreach ($productos as $p)
                            <option value="{{ $p->id }}" @selected((string) request('producto_id')===(string) $p->id)>{{ $p->nombre }} ({{ $p->categoria->nombre }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="categoria_id" value="Categoría" />
                        <select id="categoria_id" name="categoria_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">Todas</option>
                            @foreach ($categorias as $c)
                            <option value="{{ $c->id }}" @selected((string) request('categoria_id')===(string) $c->id)>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex items-end pb-1">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="solo_sin_verificar" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(request('solo_sin_verificar')) />
                            Solo facturas sin verificar precios (Fatimar)
                        </label>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <x-primary-button type="submit" class="h-10">Generar</x-primary-button>
                        <a href="{{ route('reportes.index') }}" class="text-sm self-center text-gray-600 dark:text-gray-400 hover:underline">Limpiar</a>
                    </div>
                </form>

                @if (request()->boolean('generar') && $lineas)
                <div class="grid sm:grid-cols-3 gap-3 pt-2 text-sm border-t border-gray-200 dark:border-gray-600">
                    <div class="rounded-md bg-gray-50 dark:bg-gray-700/50 p-3">
                        <p class="text-gray-500 dark:text-gray-400">Total subtotal (filtro)</p>
                        <p class="text-lg font-semibold">${{ number_format($totales['subtotal'], 2) }}</p>
                    </div>
                    <div class="rounded-md bg-gray-50 dark:bg-gray-700/50 p-3">
                        <p class="text-gray-500 dark:text-gray-400">Kg (líneas en kg)</p>
                        <p class="text-lg font-semibold">{{ number_format($totales['kg'], 3) }} kg</p>
                    </div>
                </div>

                @if ($resumenPorVendedor->isNotEmpty())
                <div class="pt-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Resumen por vendedor</p>
                    <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-600 text-sm">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Vendedor</th>
                                    <th class="px-3 py-2 text-end">Total USD</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($resumenPorVendedor as $r)
                                <tr>
                                    <td class="px-3 py-2">{{ $r['nombre'] }}</td>
                                    <td class="px-3 py-2 text-end">${{ number_format($r['total_usd'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if ($resumenPorZona->isNotEmpty())
                <div class="pt-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Resumen por zona</p>
                    <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-600 text-sm">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Zona</th>
                                    <th class="px-3 py-2 text-end">Total USD</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($resumenPorZona as $r)
                                <tr>
                                    <td class="px-3 py-2">{{ $r['zona'] }}</td>
                                    <td class="px-3 py-2 text-end">${{ number_format($r['total_usd'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-md">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-3 py-2 font-medium">Factura</th>
                                <th class="px-3 py-2 font-medium">Emisión</th>
                                <th class="px-3 py-2 font-medium text-end">Total factura</th>
                                <th class="px-3 py-2 font-medium">Cliente / zona</th>
                                <th class="px-3 py-2 font-medium">Vendedor</th>
                                <th class="px-3 py-2 font-medium">Producto</th>
                                <th class="px-3 py-2 font-medium text-end">Cant.</th>
                                <th class="px-3 py-2 font-medium text-end">P. unit.</th>
                                <th class="px-3 py-2 font-medium text-end">Subtotal línea</th>
                                <th class="px-3 py-2 font-medium">Verificación Fatimar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($lineas as $linea)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-3 py-2 font-mono text-xs">{{ $linea->factura->numero_factura ?? '#'.$linea->factura_id }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $linea->factura->fecha_emision->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-end whitespace-nowrap">${{ number_format($linea->factura->total, 2) }}</td>
                                <td class="px-3 py-2">
                                    {{ $linea->factura->cliente->nombre_razon_social }}
                                    <div class="text-xs text-gray-500">{{ $linea->factura->cliente->zona }}</div>
                                </td>
                                <td class="px-3 py-2">{{ $linea->factura->cliente->vendedor?->name ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    {{ $linea->producto->nombre }}
                                    <div class="text-xs text-gray-500">{{ $linea->producto->categoria->nombre }}</div>
                                </td>
                                <td class="px-3 py-2 text-end">{{ number_format($linea->cantidad, 3) }} {{ $linea->producto->unidad }}</td>
                                <td class="px-3 py-2 text-end">${{ number_format($linea->precio_unitario, 4) }}</td>
                                <td class="px-3 py-2 text-end font-medium">${{ number_format($linea->subtotal, 2) }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @if ($linea->factura->textoVerificacionFatimar())
                                    <span class="text-green-700 dark:text-green-300">{{ $linea->factura->textoVerificacionFatimar() }}</span>
                                    @else
                                    <span class="text-amber-700 dark:text-amber-300">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">Sin líneas con esos filtros.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($lineas->hasPages())
                <div class="pt-2">{{ $lineas->links() }}</div>
                @endif
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Estado de cuenta (por cliente)</h3>
                <form method="get" class="flex flex-col sm:flex-row gap-3 sm:items-end flex-wrap">
                    @if (request()->boolean('generar'))
                    <input type="hidden" name="generar" value="1" />
                    @foreach (['desde', 'hasta', 'vendedor_id', 'zona', 'producto_id', 'categoria_id'] as $k)
                    @if (request()->filled($k))
                    <input type="hidden" name="{{ $k }}" value="{{ request($k) }}" />
                    @endif
                    @endforeach
                    @if (request('solo_sin_verificar'))
                    <input type="hidden" name="solo_sin_verificar" value="1" />
                    @endif
                    @endif
                    <div class="min-w-[220px] flex-1">
                        <x-input-label for="cuenta_cliente_id" value="Cliente" />
                        <select id="cuenta_cliente_id" name="cuenta_cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                            <option value="">— Elegir —</option>
                            @foreach ($clientes as $c)
                            <option value="{{ $c->id }}" @selected((string) request('cuenta_cliente_id')===(string) $c->id)>{{ $c->nombre_razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 pb-1">
                        <input type="checkbox" name="solo_vencidas" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(request('solo_vencidas')) />
                        Solo vencidas (fecha vencimiento &lt; hoy)
                    </label>
                    <x-primary-button type="submit" class="h-10">Ver estado de cuenta</x-primary-button>
                </form>

                @if ($clienteEstado && $estadoCuentaFacturas)
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <strong>{{ $clienteEstado->nombre_razon_social }}</strong>
                    · Zona: {{ $clienteEstado->zona ?: '—' }}
                    · Vendedor: {{ $clienteEstado->vendedor?->name ?? '—' }}
                </p>
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-md text-sm">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 font-medium">Nº</th>
                                <th class="px-3 py-2 font-medium">Emisión</th>
                                <th class="px-3 py-2 font-medium">Vence</th>
                                <th class="px-3 py-2 font-medium text-end">Total</th>
                                <th class="px-3 py-2 font-medium text-end">Saldo</th>
                                <th class="px-3 py-2 font-medium">Cartera</th>
                                <th class="px-3 py-2 font-medium">Pago</th>
                                <th class="px-3 py-2 font-medium">Verif. Fatimar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($estadoCuentaFacturas as $f)
                            @php $ec = $f->estadoCartera(); @endphp
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs"><a href="{{ route('facturas.show', $f) }}" class="text-millennium-dark dark:text-millennium-sand hover:underline">{{ $f->numero_factura ?? '#'.$f->id }}</a></td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_emision->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-end">${{ number_format($f->total, 2) }}</td>
                                <td class="px-3 py-2 text-end">${{ number_format($f->saldo_pendiente, 2) }}</td>
                                <td class="px-3 py-2">
                                    <span @class([ 'px-2 py-0.5 rounded text-xs' , 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'=> $ec === \App\Models\Factura::CARTERA_VENCIDA,
                                        'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => $ec === \App\Models\Factura::CARTERA_POR_VENCER,
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $ec === \App\Models\Factura::CARTERA_AL_DIA,
                                        ])>{{ $etiquetasCartera[$ec] ?? $ec }}</span>
                                </td>
                                <td class="px-3 py-2">{{ $f->estado_pago === \App\Models\Factura::ESTADO_PAGO_PAGADA ? 'Pagada' : 'Abierta' }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @if ($f->textoVerificacionFatimar())
                                    <span class="text-green-700 dark:text-green-300">{{ $f->textoVerificacionFatimar() }}</span>
                                    @else
                                    <span class="text-amber-700 dark:text-amber-300">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">Sin facturas con esos criterios.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>