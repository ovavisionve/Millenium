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

    <div class="py-6" x-data="{ metodo: @js(old('metodo_pago', \App\Models\Pago::METODO_ZELLE)) }">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('cobranza.pagos.store', $factura) }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-5">
                @csrf

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="fecha_recibo" value="Fecha del recibo / abono" />
                        <x-text-input id="fecha_recibo" name="fecha_recibo" type="date" class="mt-1 block w-full" value="{{ old('fecha_recibo', now()->toDateString()) }}" required />
                        <x-input-error :messages="$errors->get('fecha_recibo')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="monto_aplicado_usd" value="Monto aplicado (USD)" />
                        <x-text-input id="monto_aplicado_usd" name="monto_aplicado_usd" type="text" inputmode="decimal" class="mt-1 block w-full" value="{{ old('monto_aplicado_usd') }}" placeholder="0.00" required />
                        <x-input-error :messages="$errors->get('monto_aplicado_usd')" class="mt-2" />
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="tipo_tasa" value="Tipo de tasa (manual)" />
                        <select id="tipo_tasa" name="tipo_tasa" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                            @foreach ($tiposTasa as $val => $label)
                                <option value="{{ $val }}" @selected(old('tipo_tasa', \App\Models\Pago::TIPO_TASA_BCV) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('tipo_tasa')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="valor_tasa" value="Valor de tasa al momento del pago (Bs/USD)" />
                        <x-text-input id="valor_tasa" name="valor_tasa" type="text" inputmode="decimal" class="mt-1 block w-full" value="{{ old('valor_tasa') }}" placeholder="Ej. 36.50" required />
                        <x-input-error :messages="$errors->get('valor_tasa')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="monto_bs" value="Monto en bolívares (opcional, referencia)" />
                    <x-text-input id="monto_bs" name="monto_bs" type="text" inputmode="decimal" class="mt-1 block w-full" value="{{ old('monto_bs') }}" />
                    <x-input-error :messages="$errors->get('monto_bs')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="metodo_pago" value="Método de pago" />
                    <select id="metodo_pago" name="metodo_pago" x-model="metodo" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                        <optgroup label="Divisas / efectivo / transferencia">
                            @foreach ($metodosDivisas as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Bolívares">
                            @foreach ($metodosBolivares as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
                </div>

                <div x-show="metodo === '{{ \App\Models\Pago::METODO_PAGO_MOVIL }}'" x-cloak class="rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 p-3 text-sm text-amber-900 dark:text-amber-100">
                    <strong>Pago móvil:</strong> la validación automática con el banco no está disponible en esta versión. El abono queda <strong>pendiente de conciliación</strong> hasta verificar el movimiento en el banco.
                </div>

                <div x-show="metodo !== '{{ \App\Models\Pago::METODO_PAGO_MOVIL }}'" x-cloak class="rounded-md bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-600 p-3 text-sm text-slate-700 dark:text-slate-200">
                    <strong>Divisas / efectivo / transferencia:</strong> indicá referencia (o últimos dígitos) y subí la captura del comprobante cuando aplique.
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="referencia" value="Referencia / últimos dígitos" />
                        <x-text-input id="referencia" name="referencia" type="text" class="mt-1 block w-full" value="{{ old('referencia') }}" />
                        <x-input-error :messages="$errors->get('referencia')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="banco_destino" value="Banco destino (opcional)" />
                        <x-text-input id="banco_destino" name="banco_destino" type="text" class="mt-1 block w-full" value="{{ old('banco_destino') }}" placeholder="Ej. Banesco" />
                        <x-input-error :messages="$errors->get('banco_destino')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="comprobante" value="Comprobante (galería o cámara)" />
                    <input id="comprobante" name="comprobante" type="file" accept="image/*,.pdf" capture="environment" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 dark:file:bg-indigo-900/40 dark:file:text-indigo-200" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">En móvil: suele abrir la cámara directamente. Máx. 5 MB.</p>
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
