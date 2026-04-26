<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Cobranza · {{ $cliente->nombre_razon_social }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $cliente->full_identificacion }} @if($cliente->zona) · {{ $cliente->zona }} @endif</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('cobranza.index') }}"><x-secondary-button type="button">Buscar otro</x-secondary-button></a>
                <a href="{{ route('clientes.edit', $cliente) }}"><x-secondary-button type="button">Cliente</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            @if (($saldoAFavorUsd ?? 0) > 0)
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden text-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 font-medium">Saldo a favor</div>
                <div class="p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-200">
                            Disponible: <span class="font-semibold">${{ number_format((float) $saldoAFavorUsd, 2) }}</span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Podés aplicarlo a futuras facturas cuando vos decidas.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('cobranza.saldo-favor.create', $cliente) }}"><x-primary-button type="button">Aplicar a facturas</x-primary-button></a>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden text-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600 font-medium">Facturas con saldo</div>
                @if ($facturas->isEmpty())
                <p class="p-6 text-gray-500">No hay facturas pendientes de cobro para este cliente.</p>
                @else
                <form method="post" action="{{ route('cobranza.cliente.pagos.store', $cliente) }}" enctype="multipart/form-data" class="space-y-0">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-2 py-2 font-medium text-center w-12" title="Incluir esta factura en el pago">Abonar</th>
                                    <th class="px-3 py-2 font-medium">Nº</th>
                                    <th class="px-3 py-2 font-medium">Emisión</th>
                                    <th class="px-3 py-2 font-medium">Vence</th>
                                    <th class="px-3 py-2 font-medium text-end">Total</th>
                                    <th class="px-3 py-2 font-medium text-end">Saldo</th>
                                    <th class="px-3 py-2 font-medium">Cartera</th>
                                    <th class="px-3 py-2 font-medium text-end">Abono USD</th>
                                    <th class="px-3 py-2 font-medium text-end">Una factura</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($facturas as $f)
                                @php
                                $ec = $f->estadoCartera();
                                $destacar = in_array($f->id, $facturasDestacarIds ?? [], true);
                                $oldAb = old('abonos.'.$f->id);
                                $tieneOldAbono = $oldAb !== null && $oldAb !== '' && (float) $oldAb > 0;
                                $incluirIni = $tieneOldAbono || $destacar;
                                @endphp
                                <tr id="factura-cobranza-{{ $f->id }}" x-data="{ incluir: @json($incluirIni) }" @class([ 'text-gray-900 dark:text-gray-100' , 'ring-2 ring-inset ring-millennium-dark/40 dark:ring-millennium-sand/60 bg-millennium-sand/10 dark:bg-millennium-dark/20'=> $destacar,
                                    ])>
                                    <td class="px-2 py-2 text-center align-middle">
                                        <input type="checkbox" x-model="incluir" @change="if (!incluir) { $refs.abono.value = '' }" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-millennium-dark shadow-sm" title="Incluir en este registro" />
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $f->numero_factura ?? '#'.$f->id }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_emision->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-end">${{ number_format($f->total, 2) }}</td>
                                    <td class="px-3 py-2 text-end font-medium">${{ number_format($f->saldo_pendiente, 2) }}</td>
                                    <td class="px-3 py-2">
                                        <span @class([ 'px-2 py-0.5 rounded text-xs' , 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'=> $ec === \App\Models\Factura::CARTERA_VENCIDA,
                                            'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => $ec === \App\Models\Factura::CARTERA_POR_VENCER,
                                            'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $ec === \App\Models\Factura::CARTERA_AL_DIA,
                                            ])>{{ $etiquetasCartera[$ec] ?? $ec }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-end">
                                        <input x-ref="abono" type="text" name="abonos[{{ $f->id }}]" value="{{ old('abonos.'.$f->id) }}" inputmode="decimal" placeholder="0.00" :disabled="!incluir" title="Marcá «Abonar» en esta fila para poder escribir el monto" class="min-w-[6.5rem] w-32 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm text-sm text-end disabled:opacity-50" @input="window.millenniumOnInputMontoUsdField($event.target)" @blur="window.millenniumOnBlurMontoUsdField($event.target)" />
                                    </td>
                                    <td class="px-3 py-2 text-end whitespace-nowrap">
                                        <a href="{{ route('cobranza.pagos.create', $f) }}" class="text-millennium-dark dark:text-millennium-sand hover:underline">Solo esta</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="p-4 border-t border-gray-200 dark:border-gray-600 space-y-4 bg-gray-50/80 dark:bg-gray-900/30"
                        x-data="cobranzaPagoPorMetodo({
                            metodoInicial: @js(old('metodo_pago', \App\Models\Pago::METODO_ZELLE)),
                            pagoMovil: @js(\App\Models\Pago::METODO_PAGO_MOVIL),
                            transferencia: @js(\App\Models\Pago::METODO_TRANSFERENCIA),
                            efectivo: @js(\App\Models\Pago::METODO_EFECTIVO),
                            usdt: @js(\App\Models\Pago::METODO_USDT),
                            sinComprobanteInicial: @json((bool) old('sin_comprobante')),
                            oldValorTasa: @js(old('valor_tasa')),
                            oldMontoBs: @js(old('monto_bs')),
                        })">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Marcá <strong>Abonar</strong> en cada factura que entre en este pago y escribí el monto en <strong>Abono USD</strong> (no más que el saldo de la fila). Un mismo recibo puede repartirse en varias filas. Elegí primero el <strong>método de pago</strong>: solo se muestran los datos que corresponden.
                        </p>

                        <div class="sm:col-span-2">
                            <x-input-label for="metodo_pago" value="Método de pago" />
                            <select id="metodo_pago" name="metodo_pago" x-model="metodo" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-sand focus:ring-millennium-sand" required>
                                <optgroup label="Divisas / efectivo / transferencia">
                                    @foreach ($metodosDivisas as $val => $label)
                                    <option value="{{ $val }}" @selected((string) old('metodo_pago', \App\Models\Pago::METODO_ZELLE)===(string) $val)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Bolívares">
                                    @foreach ($metodosBolivares as $val => $label)
                                    <option value="{{ $val }}" @selected((string) old('metodo_pago', \App\Models\Pago::METODO_ZELLE)===(string) $val)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
                        </div>

                        <div x-show="grupo() === 'transferencia' || grupo() === 'usdt'" x-cloak class="rounded-md bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 p-3 text-sm text-slate-700 dark:text-slate-200">
                            <strong>Zelle / Panamá / transferencia / USDT:</strong> cuenta destino, referencia y <strong>comprobante obligatorio</strong>.
                        </div>
                        <div x-show="grupo() === 'efectivo'" x-cloak class="rounded-md bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 p-3 text-sm text-slate-700 dark:text-slate-200">
                            <strong>Efectivo:</strong> quién recibió el dinero y <strong>foto del efectivo</strong> (obligatorio).
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="fecha_recibo" value="Fecha del recibo / abono" />
                                {{-- Texto AAAA-MM-DD: se puede borrar y escribir como en un campo normal. Valida el servidor. --}}
                                <x-text-input id="fecha_recibo" name="fecha_recibo" type="text" inputmode="numeric" autocomplete="off" placeholder="AAAA-MM-DD" class="mt-1 block w-full font-mono" value="{{ old('fecha_recibo', now()->format('Y-m-d')) }}" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Formato: año-mes-día (ej. {{ now()->format('Y-m-d') }}).</p>
                                <x-input-error :messages="$errors->get('fecha_recibo')" class="mt-2" />
                            </div>
                        </div>

                        <div x-show="grupo() === 'efectivo'" x-cloak class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="recibido_por" value="Recibido por (quién tiene el efectivo)" />
                                <x-text-input id="recibido_por" name="recibido_por" type="text" class="mt-1 block w-full" value="{{ old('recibido_por') }}" placeholder="Nombre y apellido" x-bind:disabled="grupo() !== 'efectivo'" x-bind:required="grupo() === 'efectivo'" />
                                <x-input-error :messages="$errors->get('recibido_por')" class="mt-2" />
                            </div>
                        </div>

                        <div x-show="permiteTasaBs()" x-cloak class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="tipo_tasa" value="Tipo de tasa" />
                                <select id="tipo_tasa" name="tipo_tasa" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" :disabled="!permiteTasaBs()" :required="grupo() === 'pago_movil'">
                                    @foreach (\App\Models\Pago::tiposTasa() as $val => $label)
                                    <option value="{{ $val }}" @selected(old('tipo_tasa', \App\Models\Pago::TIPO_TASA_BCV)===$val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tipo_tasa')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="valor_tasa" value="Valor tasa (Bs/USD)" />
                                <x-text-input id="valor_tasa" name="valor_tasa" type="text" inputmode="decimal" class="mt-1 block w-full" x-bind:value="valorTasa" @input="onValorTasaInput($event)" @blur="onValorTasaBlur($event)" x-bind:disabled="!permiteTasaBs()" x-bind:required="grupo() === 'pago_movil'" />
                                <x-input-error :messages="$errors->get('valor_tasa')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="monto_bs" value="Monto Bs acreditado (banco)" />
                                <input type="hidden" name="monto_bs" :value="montoBsRaw" />
                                <x-text-input
                                    id="monto_bs"
                                    type="text"
                                    inputmode="text"
                                    class="mt-1 block w-full"
                                    x-bind:value="montoBsDisplay"
                                    @input="onMontoBsInput($event)"
                                    @blur="onMontoBsBlur($event)"
                                    x-bind:disabled="!permiteTasaBs()"
                                    x-bind:required="grupo() === 'pago_movil'"
                                    placeholder="Ej. 1210 o 1.210.000,50"
                                />
                                <x-input-error :messages="$errors->get('monto_bs')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-3 rounded-md bg-white/80 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-600 px-3 py-2 text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Equivalente USD (Bs / tasa):</span>
                                <strong class="ms-1 text-millennium-dark dark:text-millennium-sand" x-text="equivUsd() || '—'"></strong>
                                <span class="text-xs text-gray-500 ms-2" x-show="grupo() === 'pago_movil'">Debe cubrir la suma de <strong>Abono USD</strong> arriba.</span>
                            </div>
                        </div>

                        <div x-show="grupo() === 'transferencia' || grupo() === 'usdt' || grupo() === 'pago_movil'" x-cloak class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="referencia" value="Referencia / ID de operación" />
                                <x-text-input id="referencia" name="referencia" type="text" class="mt-1 block w-full" value="{{ old('referencia') }}" x-bind:disabled="grupo() === 'efectivo'" x-bind:required="grupo() === 'transferencia' || grupo() === 'usdt' || grupo() === 'pago_movil'" />
                                <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="banco_destino" value="Banco" />
                                <select id="banco_destino" name="banco_destino" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" x-bind:disabled="grupo() === 'efectivo'" x-bind:required="grupo() === 'pago_movil'">
                                    <option value="">Elegir banco</option>
                                    @foreach ($bancos as $banco)
                                    <option value="{{ $banco->nombre }}" @selected(old('banco_destino')===$banco->nombre)>{{ $banco->nombre }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se carga desde Datos maestros → Bancos.</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'pago_movil'">Obligatorio para pago móvil. Opcional en transferencias.</p>
                                <x-input-error :messages="$errors->get('banco_destino')" class="mt-2" />
                            </div>
                        </div>

                        <div x-show="grupo() === 'transferencia' || grupo() === 'usdt' || grupo() === 'efectivo' || grupo() === 'pago_movil'" x-cloak class="sm:col-span-2">
                            <x-input-label for="comprobante" value="Comprobante (archivo o foto)" />
                            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="sin_comprobante" value="1" class="rounded border-gray-300 dark:border-gray-700" x-model="sinComprobante" />
                                No tengo comprobante
                            </label>
                            <input id="comprobante"
                                x-ref="comprobante"
                                name="comprobante"
                                type="file"
                                accept="image/*,.pdf"
                                capture="environment"
                                class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:rounded file:border-0 file:bg-millennium-sand/25 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-millennium-dark dark:file:bg-millennium-dark/40 dark:file:text-millennium-sand"
                                x-bind:disabled="sinComprobante"
                                x-bind:required="(grupo() === 'transferencia' || grupo() === 'usdt' || grupo() === 'efectivo') && !sinComprobante" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'efectivo'">Subí foto de los billetes o del recibo de caja.</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'transferencia' || grupo() === 'usdt'">Captura del comprobante bancario. En el móvil podés usar la cámara. Máx. 5 MB.</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'pago_movil'">Opcional hasta integrar API bancaria.</p>
                            <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="notas" value="Notas (opcional)" />
                            <textarea id="notas" name="notas" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">{{ old('notas') }}</textarea>
                            <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                        </div>

                        <x-input-error :messages="$errors->get('abonos')" class="mt-2" />
                        <div class="flex gap-3 flex-wrap">
                            <x-primary-button type="submit">Registrar abonos</x-primary-button>
                        </div>
                    </div>
                </form>
                @endif
            </div>

            @if (isset($facturasComprobante) && $facturasComprobante->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-millennium-dark/10 dark:border-gray-700 p-4 sm:p-6 text-sm">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Comprobante de movimientos de pago (PDF)</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                    Para justificar un pago sobre <strong>una o varias facturas</strong> (como en operación): marcá los documentos, opcionalmente filtrá abonos por <strong>fecha de recibo</strong>, y descargá el PDF estilo comprobante.
                </p>
                <form id="form-movimientos-pdf" method="get" action="{{ route('cobranza.cliente.movimientos-pago.pdf', $cliente) }}" target="_blank" class="space-y-4" onsubmit="if (!this.querySelectorAll('input[name=\'facturas[]\']:checked').length) { alert('Seleccioná al menos una factura.'); return false; } return true;">
                    <div class="overflow-x-auto max-h-56 overflow-y-auto rounded border border-gray-200 dark:border-gray-600">
                        <table class="min-w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1 w-10"></th>
                                    <th class="px-2 py-1">Nº</th>
                                    <th class="px-2 py-1">Emisión</th>
                                    <th class="px-2 py-1 text-end">Total</th>
                                    <th class="px-2 py-1 text-end">Saldo</th>
                                    <th class="px-2 py-1">Pago</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($facturasComprobante as $fc)
                                <tr>
                                    <td class="px-2 py-1 text-center">
                                        <input type="checkbox" name="facturas[]" value="{{ $fc->id }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900" />
                                    </td>
                                    <td class="px-2 py-1 font-mono">{{ $fc->numero_factura ?? '#'.$fc->id }}</td>
                                    <td class="px-2 py-1 whitespace-nowrap">{{ $fc->fecha_emision->format('d/m/Y') }}</td>
                                    <td class="px-2 py-1 text-end">${{ number_format($fc->total, 2) }}</td>
                                    <td class="px-2 py-1 text-end font-medium">${{ number_format($fc->saldo_pendiente, 2) }}</td>
                                    <td class="px-2 py-1">{{ $fc->estado_pago === \App\Models\Factura::ESTADO_PAGO_PAGADA ? 'Pagada' : 'Factura por pagar' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3 max-w-md">
                        <div>
                            <x-input-label for="mov_desde" value="Desde (fecha recibo abono)" />
                            <x-text-input id="mov_desde" name="desde" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="mov_hasta" value="Hasta (fecha recibo abono)" />
                            <x-text-input id="mov_hasta" name="hasta" type="date" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <x-primary-button type="submit">Descargar PDF movimientos</x-primary-button>
                </form>
            </div>
            @endif

            <p class="text-xs text-gray-500 dark:text-gray-400 px-1">
                Cobranza separada del resto del flujo para poder integrar APIs bancarias más adelante. <strong>Pago móvil (Bs):</strong> pendiente de validación con el banco hasta conciliar manualmente.
            </p>
        </div>
    </div>
    @if (! empty($facturasDestacarIds[0] ?? null))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('factura-cobranza-{{ (int) $facturasDestacarIds[0] }}');
            if (el) el.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        });
    </script>
    @endpush
    @endif
</x-app-layout>