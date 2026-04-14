<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Factura {{ $factura->numero_factura ?? '#'.$factura->id }}
            </h2>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('facturas.nota-entrega', $factura) }}" target="_blank" rel="noopener"><x-secondary-button type="button">Nota de entrega</x-secondary-button></a>
                <a href="{{ route('facturas.nota-entrega.pdf', $factura) }}"><x-secondary-button type="button">Nota PDF</x-secondary-button></a>
                @if ((float) $factura->saldo_pendiente > 0)
                <a href="{{ route('cobranza.pagos.create', $factura) }}"><x-primary-button type="button">Registrar pago</x-primary-button></a>
                @endif
                <a href="{{ route('cobranza.cliente', $factura->cliente) }}"><x-secondary-button type="button">Cobranza cliente</x-secondary-button></a>
                <a href="{{ route('facturas.edit', $factura) }}"><x-secondary-button type="button">Editar</x-secondary-button></a>
                <a href="{{ route('facturas.index') }}"><x-secondary-button type="button">Lista</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    @php
    $ec = $factura->estadoCartera();
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif
            @if (session('abrir_nota_entrega'))
            <div class="rounded-lg border border-millennium-sand bg-millennium-sand/15 dark:bg-millennium-dark/40 p-4 text-sm text-millennium-dark dark:text-millennium-sand space-y-2">
                <p class="font-medium">Enviá al cliente la mercancía con la nota de entrega (imprimir o PDF).</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('facturas.nota-entrega', $factura) }}" target="_blank" rel="noopener"><x-primary-button type="button">Abrir nota de entrega</x-primary-button></a>
                    <a href="{{ route('facturas.nota-entrega.pdf', $factura) }}"><x-secondary-button type="button">Descargar PDF</x-secondary-button></a>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cliente</p>
                        <p class="font-medium text-lg">{{ $factura->cliente->nombre_razon_social }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Documento: {{ $factura->cliente->full_identificacion }} · Zona: {{ $factura->cliente->zona }}</p>
                        @if ($factura->cliente->vendedor)
                        <p class="text-sm">Vendedor asignado: {{ $factura->cliente->vendedor->name }}</p>
                        @endif
                    </div>
                    <div class="text-end space-y-2">
                        @if ($factura->estado_pago === \App\Models\Factura::ESTADO_PAGO_PAGADA)
                        <span class="inline-block px-3 py-1 rounded text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Pagada</span>
                        @else
                        <span class="inline-block px-3 py-1 rounded text-sm font-medium bg-slate-200 text-slate-800 dark:bg-slate-600 dark:text-slate-100">Pago abierto</span>
                        @endif
                        <div>
                            <span @class([ 'inline-block px-3 py-1 rounded text-sm font-medium' , 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'=> $ec === \App\Models\Factura::CARTERA_VENCIDA,
                                'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => $ec === \App\Models\Factura::CARTERA_POR_VENCER,
                                'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $ec === \App\Models\Factura::CARTERA_AL_DIA,
                                ])>{{ $etiquetasCartera[$ec] ?? $ec }}</span>
                        </div>
                    </div>
                </div>

                <dl class="grid sm:grid-cols-2 gap-4 text-sm border-t border-gray-200 dark:border-gray-600 pt-4">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Emisión</dt>
                        <dd class="font-medium">{{ $factura->fecha_emision->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Vencimiento</dt>
                        <dd class="font-medium">{{ $factura->fecha_vencimiento->format('d/m/Y') }} ({{ $factura->dias_credito }} días crédito)</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Total</dt>
                        <dd class="font-semibold text-lg">${{ number_format($factura->total, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Saldo pendiente</dt>
                        <dd class="font-medium">${{ number_format($factura->saldo_pendiente, 2) }}</dd>
                    </div>
                </dl>

                <div class="border-t border-gray-200 dark:border-gray-600 pt-4 space-y-2 text-sm">
                    <p><span class="text-gray-500">Registrada por:</span> <strong>{{ $factura->creadoPor->name }}</strong> · {{ $factura->created_at->format('d/m/Y H:i') }}</p>
                    @if ($factura->verificadoPor)
                    <p class="text-green-700 dark:text-green-300">
                        <span class="text-gray-500 dark:text-gray-400">Verificación de precios (Fatimar):</span>
                        <strong>{{ $factura->verificadoPor->name }}</strong>
                        @if ($factura->verificadoPor->email)
                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $factura->verificadoPor->email }})</span>
                        @endif
                        @if ($factura->fecha_verificacion)
                        · {{ $factura->fecha_verificacion->format('d/m/Y H:i') }}
                        @endif
                    </p>
                    @else
                    <p class="text-amber-700 dark:text-amber-300">Pendiente de verificación de precios (Fatimar). En reportes podrás filtrar las que faltan por revisar.</p>
                    @endif
                </div>

                @if ($factura->puedeVerificar(auth()->user()))
                <form method="post" action="{{ route('facturas.verificar', $factura) }}" class="pt-2">
                    @csrf
                    <x-primary-button type="submit" onclick="return confirm('¿Confirmar con tu usuario que los precios y montos de esta factura son correctos? Quedará registrado como verificación Fatimar.');">
                        Verificar precios con mi usuario (Fatimar)
                    </x-primary-button>
                </form>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden" x-data="{ tab: 'lineas' }">
                <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-600 flex gap-4">
                    <button type="button" @click="tab = 'lineas'" :class="tab === 'lineas' ? 'font-semibold text-millennium-dark dark:text-millennium-sand border-b-2 border-millennium-sand -mb-px pb-1' : 'text-gray-500 dark:text-gray-400'" class="text-sm">Líneas</button>
                    <button type="button" @click="tab = 'movimientos'" :class="tab === 'movimientos' ? 'font-semibold text-millennium-dark dark:text-millennium-sand border-b-2 border-millennium-sand -mb-px pb-1' : 'text-gray-500 dark:text-gray-400'" class="text-sm">Movimientos (cobranza)</button>
                </div>
                <div x-show="tab === 'lineas'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Producto</th>
                                <th class="px-4 py-2 text-end">Cantidad</th>
                                <th class="px-4 py-2 text-end">P. unit.</th>
                                <th class="px-4 py-2 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($factura->lineas as $linea)
                            <tr>
                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ $linea->producto->nombre }}</div>
                                    <div class="text-xs text-gray-500">{{ $linea->producto->categoria->nombre }} · {{ $linea->producto->codigo }}</div>
                                </td>
                                <td class="px-4 py-2 text-end">{{ number_format($linea->cantidad, 3) }} {{ \App\Models\Producto::unidadAbreviada()[$linea->producto->unidad] ?? $linea->producto->unidad }}</td>
                                <td class="px-4 py-2 text-end">${{ number_format($linea->precio_unitario, 4) }}</td>
                                <td class="px-4 py-2 text-end font-medium">${{ number_format($linea->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div x-show="tab === 'movimientos'" class="overflow-x-auto">
                    <p class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600">
                        <span class="text-gray-500 dark:text-gray-400">Fecha de emisión del documento:</span>
                        <strong>{{ $factura->fecha_emision->format('d/m/Y') }}</strong>
                    </p>
                    <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-600 flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3 bg-gray-50/50 dark:bg-gray-900/20">
                        <form method="get" action="{{ route('facturas.movimientos-pago.pdf', $factura) }}" target="_blank" class="flex flex-wrap items-end gap-2">
                            <div>
                                <label for="mov_pdf_desde" class="block text-xs text-gray-500 dark:text-gray-400">Desde (recibo)</label>
                                <input id="mov_pdf_desde" name="desde" type="date" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm" />
                            </div>
                            <div>
                                <label for="mov_pdf_hasta" class="block text-xs text-gray-500 dark:text-gray-400">Hasta (recibo)</label>
                                <input id="mov_pdf_hasta" name="hasta" type="date" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm" />
                            </div>
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-millennium-dark dark:bg-millennium-sand text-white dark:text-millennium-dark rounded-md text-sm font-medium hover:opacity-90 h-10">Descargar PDF movimientos</button>
                        </form>
                        <p class="text-xs text-gray-500 dark:text-gray-400 sm:ms-auto max-w-md">Opcional: limitá el PDF a abonos cuya <strong>fecha de recibo</strong> cae en el rango. Sin fechas, incluye todos los abonos de esta factura.</p>
                    </div>
                    @if ($factura->pagos->isEmpty())
                    <p class="px-6 py-8 text-sm text-gray-500 text-center">Sin abonos registrados.</p>
                    @else
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Fecha recibo</th>
                                <th class="px-4 py-2 text-end">USD</th>
                                <th class="px-4 py-2 text-left">Tasa</th>
                                <th class="px-4 py-2 text-left">Método</th>
                                <th class="px-4 py-2 text-left">Ref. / banco</th>
                                <th class="px-4 py-2 text-left">Validación</th>
                                <th class="px-4 py-2 text-left">Comprobante</th>
                                <th class="px-4 py-2 text-left">Registró</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($factura->pagos as $pago)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $pago->fecha_recibo->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-end font-medium">${{ number_format($pago->monto_aplicado_usd, 2) }}</td>
                                <td class="px-4 py-2">{{ $tiposTasaPago[$pago->tipo_tasa] ?? $pago->tipo_tasa }} · {{ number_format($pago->valor_tasa, 4) }}@if($pago->monto_bs) <span class="text-gray-500">· Bs {{ number_format($pago->monto_bs, 2) }}</span>@endif</td>
                                <td class="px-4 py-2">{{ $metodosPago[$pago->metodo_pago] ?? $pago->metodo_pago }}</td>
                                <td class="px-4 py-2">
                                    {{ $pago->referencia ?? '—' }}
                                    @if ($pago->banco_destino)
                                    <div class="text-xs text-gray-500">{{ $pago->banco_destino }}</div>
                                    @endif
                                    @if ($pago->cuenta_destino)
                                    <div class="text-xs text-gray-500">Cuenta: {{ $pago->cuenta_destino }}</div>
                                    @endif
                                    @if ($pago->recibido_por)
                                    <div class="text-xs text-gray-500">Recibió: {{ $pago->recibido_por }}</div>
                                    @endif
                                    @if ($pago->fecha_publicacion)
                                    <div class="text-xs text-gray-500">Publicación: {{ $pago->fecha_publicacion->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs">
                                    @if ($pago->metodo_pago === \App\Models\Pago::METODO_PAGO_MOVIL)
                                    @if ($pago->estado_validacion_banco === \App\Models\Pago::VALIDACION_BANCO_PENDIENTE)
                                    <span class="text-amber-700 dark:text-amber-300">Pendiente banco</span>
                                    @elseif ($pago->estado_validacion_banco === \App\Models\Pago::VALIDACION_BANCO_VERIFICADO_MANUAL)
                                    <span class="text-green-700 dark:text-green-300">Verificado manual</span>
                                    @else
                                    —
                                    @endif
                                    @else
                                    —
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    @if ($pago->comprobante_path)
                                    <a href="{{ asset('storage/'.$pago->comprobante_path) }}" target="_blank" rel="noopener" class="text-millennium-dark dark:text-millennium-sand hover:underline">Ver captura</a>
                                    @else
                                    —
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs">{{ $pago->registradoPor->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

            @if ($factura->pagos->isEmpty())
            <form method="post" action="{{ route('facturas.destroy', $factura) }}" onsubmit="return confirm('¿Eliminar esta factura?');" class="text-end">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline">Eliminar factura</button>
            </form>
            @else
            <p class="text-end text-sm text-gray-500 dark:text-gray-400">No se puede eliminar: hay pagos registrados.</p>
            @endif
        </div>
    </div>
</x-app-layout>