<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Registrar pago</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $factura->cliente->nombre_razon_social }} · Factura {{ $factura->numero_factura ?? '#'.$factura->id }}
                    · Saldo: <strong>${{ number_format($factura->saldo_pendiente, 2) }}</strong>
                </p>
            </div>
            <a href="{{ route('facturas.show', $factura) }}"><x-secondary-button type="button">Volver</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('cobranza.pagos.store', $factura) }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-5"
                x-data="cobranzaPagoPorMetodo({
                    metodoInicial: @js(old('metodo_pago', \App\Models\Pago::METODO_ZELLE)),
                    pagoMovil: @js(\App\Models\Pago::METODO_PAGO_MOVIL),
                    efectivo: @js(\App\Models\Pago::METODO_EFECTIVO),
                    usdt: @js(\App\Models\Pago::METODO_USDT),
                    oldValorTasa: @js(old('valor_tasa')),
                    oldMontoBs: @js(old('monto_bs')),
                    oldMontoAplicadoUsd: @js(old('monto_aplicado_usd')),
                    sincronizarUsdDesdeBs: true,
                })">
                @csrf

                <div>
                    <x-input-label for="metodo_pago" value="Método de pago" />
                    <select id="metodo_pago" name="metodo_pago" x-model="metodo" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
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

                <div x-show="grupo() === 'pago_movil'" x-cloak class="rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 p-3 text-sm text-amber-900 dark:text-amber-100">
                    <strong>Pago móvil:</strong> tasa y Bs acreditados; el monto USD debe coincidir con Bs / tasa.
                </div>
                <div x-show="grupo() === 'transferencia' || grupo() === 'usdt'" x-cloak class="rounded-md bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 p-3 text-sm text-slate-700 dark:text-slate-200">
                    Datos de transferencia / divisas y comprobante obligatorio.
                </div>
                <div x-show="grupo() === 'efectivo'" x-cloak class="rounded-md bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 p-3 text-sm text-slate-700 dark:text-slate-200">
                    Efectivo: quién recibió y foto obligatoria.
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="fecha_recibo" value="Fecha del recibo / abono" />
                        <x-text-input id="fecha_recibo" name="fecha_recibo" type="date" class="mt-1 block w-full" value="{{ old('fecha_recibo', now()->toDateString()) }}" required />
                        <x-input-error :messages="$errors->get('fecha_recibo')" class="mt-2" />
                    </div>
                </div>

                <div x-show="grupo() === 'efectivo'" x-cloak>
                    <x-input-label for="recibido_por" value="Recibido por" />
                    <x-text-input id="recibido_por" name="recibido_por" type="text" class="mt-1 block w-full" value="{{ old('recibido_por') }}" placeholder="Nombre y apellido" x-bind:disabled="grupo() !== 'efectivo'" x-bind:required="grupo() === 'efectivo'" />
                    <x-input-error :messages="$errors->get('recibido_por')" class="mt-2" />
                </div>

                <div x-show="grupo() === 'pago_movil'" x-cloak class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="tipo_tasa" value="Tipo de tasa" />
                        <select id="tipo_tasa" name="tipo_tasa" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" :disabled="grupo() !== 'pago_movil'" :required="grupo() === 'pago_movil'">
                            @foreach ($tiposTasa as $val => $label)
                            <option value="{{ $val }}" @selected(old('tipo_tasa', \App\Models\Pago::TIPO_TASA_BCV)===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('tipo_tasa')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="valor_tasa" value="Valor tasa (Bs/USD)" />
                        <x-text-input id="valor_tasa" name="valor_tasa" type="text" inputmode="decimal" class="mt-1 block w-full" x-model="valorTasa" x-bind:disabled="grupo() !== 'pago_movil'" x-bind:required="grupo() === 'pago_movil'" />
                        <x-input-error :messages="$errors->get('valor_tasa')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="monto_bs" value="Monto Bs acreditado" />
                        <x-text-input id="monto_bs" name="monto_bs" type="text" inputmode="decimal" class="mt-1 block w-full" x-model="montoBs" x-bind:disabled="grupo() !== 'pago_movil'" x-bind:required="grupo() === 'pago_movil'" />
                        <x-input-error :messages="$errors->get('monto_bs')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="monto_aplicado_usd" value="Monto aplicado (USD)" />
                    <x-text-input id="monto_aplicado_usd" name="monto_aplicado_usd" type="text" inputmode="decimal" class="mt-1 block w-full" x-model="montoUsdPm" placeholder="0.00" required />
                    <p x-show="grupo() === 'pago_movil'" x-cloak class="mt-1 text-xs text-gray-500 dark:text-gray-400">En pago móvil el monto se <strong>calcula solo</strong> a partir de Bs y tasa (podés corregirlo si hace falta).</p>
                    <x-input-error :messages="$errors->get('monto_aplicado_usd')" class="mt-2" />
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
                        <x-input-error :messages="$errors->get('banco_destino')" class="mt-2" />
                    </div>
                </div>

                <div x-show="grupo() === 'transferencia' || grupo() === 'usdt' || grupo() === 'efectivo' || grupo() === 'pago_movil'" x-cloak>
                    <x-input-label for="comprobante" value="Comprobante (archivo o foto)" />
                    <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="sin_comprobante" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(old('sin_comprobante')) />
                        No tengo comprobante
                    </label>
                    <input id="comprobante"
                        name="comprobante"
                        type="file"
                        accept="image/*,.pdf"
                        capture="environment"
                        class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:rounded file:border-0 file:bg-millennium-sand/25 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-millennium-dark dark:file:bg-millennium-dark/40 dark:file:text-millennium-sand"
                        x-bind:disabled="$el.form?.querySelector('input[name=sin_comprobante]')?.checked"
                        :required="(grupo() === 'transferencia' || grupo() === 'usdt' || grupo() === 'efectivo') && !$el.form?.querySelector('input[name=sin_comprobante]')?.checked" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'efectivo'">Subí foto de los billetes o del recibo de caja.</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'transferencia' || grupo() === 'usdt'">Captura del comprobante bancario. En el móvil podés usar la cámara. Máx. 5 MB.</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="grupo() === 'pago_movil'">Opcional hasta integrar API bancaria.</p>
                    <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notas" value="Notas (opcional)" />
                    <textarea id="notas" name="notas" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">{{ old('notas') }}</textarea>
                    <x-input-error :messages="$errors->get('notas')" class="mt-2" />
                </div>

                <div class="flex gap-3">
                    <x-primary-button type="submit">Guardar pago</x-primary-button>
                    <a href="{{ route('facturas.show', $factura) }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>