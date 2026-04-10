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
                                        @php $ec = $f->estadoCartera(); @endphp
                                        <tr class="text-gray-900 dark:text-gray-100">
                                            <td class="px-3 py-2 font-mono text-xs">{{ $f->numero_factura ?? '#'.$f->id }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_emision->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap">{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2 text-end">${{ number_format($f->total, 2) }}</td>
                                            <td class="px-3 py-2 text-end font-medium">${{ number_format($f->saldo_pendiente, 2) }}</td>
                                            <td class="px-3 py-2">
                                                <span @class([
                                                    'px-2 py-0.5 rounded text-xs',
                                                    'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' => $ec === \App\Models\Factura::CARTERA_VENCIDA,
                                                    'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' => $ec === \App\Models\Factura::CARTERA_POR_VENCER,
                                                    'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' => $ec === \App\Models\Factura::CARTERA_AL_DIA,
                                                ])>{{ $etiquetasCartera[$ec] ?? $ec }}</span>
                                            </td>
                                            <td class="px-3 py-2 text-end">
                                                <input type="text" name="abonos[{{ $f->id }}]" value="{{ old('abonos.'.$f->id) }}" inputmode="decimal" placeholder="0.00" class="w-28 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm text-sm text-end" />
                                            </td>
                                            <td class="px-3 py-2 text-end whitespace-nowrap">
                                                <a href="{{ route('cobranza.pagos.create', $f) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Solo esta</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 border-t border-gray-200 dark:border-gray-600 space-y-4 bg-gray-50/80 dark:bg-gray-900/30">
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Podés distribuir un mismo recibo en <strong>varias facturas</strong>: completá los montos en USD (no más que el saldo de cada fila). Tasa y método valen para todos los abonos de este envío.
                            </p>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="fecha_recibo" value="Fecha del recibo" />
                                    <x-text-input id="fecha_recibo" name="fecha_recibo" type="date" class="mt-1 block w-full" value="{{ old('fecha_recibo', now()->toDateString()) }}" required />
                                    <x-input-error :messages="$errors->get('fecha_recibo')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tipo_tasa" value="Tipo de tasa" />
                                    <select id="tipo_tasa" name="tipo_tasa" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                        @foreach (\App\Models\Pago::tiposTasa() as $val => $label)
                                            <option value="{{ $val }}" @selected(old('tipo_tasa', \App\Models\Pago::TIPO_TASA_BCV) === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="valor_tasa" value="Valor tasa (Bs/USD)" />
                                    <x-text-input id="valor_tasa" name="valor_tasa" type="text" inputmode="decimal" class="mt-1 block w-full" value="{{ old('valor_tasa') }}" required />
                                    <x-input-error :messages="$errors->get('valor_tasa')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="monto_bs" value="Monto Bs (referencia)" />
                                    <x-text-input id="monto_bs" name="monto_bs" type="text" inputmode="decimal" class="mt-1 block w-full" value="{{ old('monto_bs') }}" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="metodo_pago" value="Método de pago" />
                                    <select id="metodo_pago" name="metodo_pago" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                        <optgroup label="Divisas / efectivo / transferencia">
                                            @foreach ($metodosDivisas as $val => $label)
                                                <option value="{{ $val }}" @selected(old('metodo_pago', \App\Models\Pago::METODO_ZELLE) === $val)>{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Bolívares">
                                            @foreach ($metodosBolivares as $val => $label)
                                                <option value="{{ $val }}" @selected(old('metodo_pago') === $val)>{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="referencia" value="Referencia" />
                                    <x-text-input id="referencia" name="referencia" type="text" class="mt-1 block w-full" value="{{ old('referencia') }}" />
                                </div>
                                <div>
                                    <x-input-label for="banco_destino" value="Banco destino" />
                                    <x-text-input id="banco_destino" name="banco_destino" type="text" class="mt-1 block w-full" value="{{ old('banco_destino') }}" placeholder="Ej. Banesco" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="comprobante" value="Comprobante (archivo o foto desde la cámara)" />
                                    <input id="comprobante" name="comprobante" type="file" accept="image/*,.pdf" capture="environment" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 dark:file:bg-indigo-900/40 dark:file:text-indigo-200" />
                                    <p class="mt-1 text-xs text-gray-500">En el móvil podés usar la cámara. Máx. 5 MB.</p>
                                    <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="notas" value="Notas" />
                                    <textarea id="notas" name="notas" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">{{ old('notas') }}</textarea>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('abonos')" class="mt-2" />
                            <div class="flex gap-3 flex-wrap">
                                <x-primary-button type="submit">Registrar abonos</x-primary-button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 px-1">
                <strong>Pago móvil (Bs):</strong> el sistema marca la operación como pendiente de validación con el banco; hoy no hay enlace automático con entidades (se concilia manualmente).
            </p>
        </div>
    </div>
</x-app-layout>
