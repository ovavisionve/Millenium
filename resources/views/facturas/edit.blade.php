<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar factura {{ $factura->numero_factura ?? '#'.$factura->id }}</h2>
    </x-slot>

    @php
        $clienteIdInicial = old('cliente_id') !== null && old('cliente_id') !== '' ? (string) old('cliente_id') : (string) $factura->cliente_id;
    @endphp

    <div
        class="py-6"
        x-data="{
            lineas: @js($lineasForm),
            clientes: @js($clientesResumen),
            clienteId: @js($clienteIdInicial),
            clienteActual() {
                const id = this.clienteId;
                if (id === '' || id === null || id === undefined) return null;
                return this.clientes[String(id)] ?? null;
            },
            totalUsd() {
                let t = 0;
                for (const l of this.lineas) {
                    const c = parseFloat(l.cantidad);
                    const p = parseFloat(l.precio_unitario);
                    if (!isNaN(c) && !isNaN(p)) t += c * p;
                }
                return Math.round(t * 100) / 100;
            },
            subtotalLinea(l) {
                const c = parseFloat(l.cantidad);
                const p = parseFloat(l.precio_unitario);
                if (isNaN(c) || isNaN(p)) return null;
                return Math.round(c * p * 100) / 100;
            },
        }"
    >
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('facturas.update', $factura) }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Datos generales</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="cliente_id" value="Cliente" />
                            <select
                                id="cliente_id"
                                name="cliente_id"
                                x-model="clienteId"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"
                            >
                                <option value="">— Seleccione —</option>
                                @foreach ($clientes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre_razon_social }} — {{ $c->full_identificacion }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Al elegir un cliente se despliegan el <strong>RIF</strong>, <strong>dirección</strong>, <strong>vendedor</strong>, <strong>zona</strong> y <strong>ubicación</strong>.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('cliente_id')" />
                        </div>

                        <div class="sm:col-span-2" x-show="clienteActual()" x-cloak x-transition.opacity.duration.200ms>
                            <div class="rounded-lg border border-millennium-dark/15 dark:border-millennium-sand/25 bg-millennium-sand/5 dark:bg-gray-900/30 p-4 text-sm space-y-2">
                                <p class="font-semibold text-millennium-dark dark:text-millennium-sand">Datos del cliente seleccionado</p>
                                <dl class="grid gap-2 sm:grid-cols-2 text-gray-700 dark:text-gray-300">
                                    <div>
                                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">RIF / documento</dt>
                                        <dd class="font-mono" x-text="clienteActual()?.rif ?? '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Vendedor asignado</dt>
                                        <dd x-text="clienteActual()?.vendedor ?? '—'"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Correo</dt>
                                        <dd x-text="clienteActual()?.email ?? '—'"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Dirección</dt>
                                        <dd x-text="clienteActual()?.direccion ?? '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Zona / sector / ruta</dt>
                                        <dd x-text="clienteActual()?.zona ?? '—'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase text-gray-500 dark:text-gray-400">Ubicación</dt>
                                        <dd x-text="clienteActual()?.ubicacion ?? '—'"></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label value="Nº factura" />
                            <p class="mt-1 text-sm font-mono rounded-md border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 px-3 py-2 text-gray-800 dark:text-gray-200">{{ $factura->numero_factura ?? '—' }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Asignado por el sistema al crear la factura; no se modifica.</p>
                        </div>
                        <div>
                            <x-input-label for="fecha_emision" value="Fecha de emisión" />
                            <x-text-input id="fecha_emision" name="fecha_emision" type="date" class="mt-1 block w-full" :value="old('fecha_emision', $factura->fecha_emision->toDateString())" required />
                            <x-input-error class="mt-2" :messages="$errors->get('fecha_emision')" />
                        </div>
                        <div>
                            <x-input-label for="dias_credito" value="Días de crédito" />
                            <x-text-input id="dias_credito" name="dias_credito" type="number" min="0" class="mt-1 block w-full" :value="old('dias_credito', $factura->dias_credito)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('dias_credito')" />
                        </div>
                    </div>
                    <p class="text-xs text-amber-700 dark:text-amber-300">Al guardar, si la factura estaba verificada, deberá volver a verificarse.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Líneas de producto</h3>
                        <button type="button" @click="lineas.push({ producto_id: '', cantidad: '', precio_unitario: '' })" class="text-sm text-millennium-dark dark:text-millennium-sand hover:underline">+ Agregar línea</button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-2 py-2 text-left">Producto</th>
                                    <th class="px-2 py-2 text-left w-28">Cantidad</th>
                                    <th class="px-2 py-2 text-left w-32">Precio unit.</th>
                                    <th class="px-2 py-2 text-end w-28">Subtotal</th>
                                    <th class="px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(linea, index) in lineas" :key="index">
                                    <tr class="border-t border-gray-200 dark:border-gray-600">
                                        <td class="px-2 py-2">
                                            <select class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][producto_id]'" x-model="linea.producto_id" required>
                                                <option value="">— Producto —</option>
                                                @foreach ($productos as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->categoria->nombre }}) — {{ $p->codigo }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="0.001" min="0.001" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][cantidad]'" x-model="linea.cantidad" required />
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="0.0001" min="0" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][precio_unitario]'" x-model="linea.precio_unitario" required />
                                        </td>
                                        <td class="px-2 py-2 text-end font-mono text-gray-800 dark:text-gray-200" x-text="subtotalLinea(linea) !== null ? ('$' + subtotalLinea(linea).toFixed(2)) : '—'"></td>
                                        <td class="px-1 py-2">
                                            <button type="button" class="text-red-600 text-xs" title="Quitar línea" @click="if (lineas.length > 1) lineas.splice(index, 1)">x</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-millennium-dark/20 dark:border-millennium-sand/30 bg-gray-50/80 dark:bg-gray-900/50">
                                    <td colspan="3" class="px-2 py-3 text-end font-semibold text-gray-800 dark:text-gray-100">Total USD (nuevo total de factura)</td>
                                    <td class="px-2 py-3 text-end font-mono font-semibold text-millennium-dark dark:text-millennium-sand" x-text="'$' + totalUsd().toFixed(2)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">El servidor recalcula total y saldo respetando cobros ya registrados.</p>
                    <x-input-error :messages="$errors->get('lineas')" class="mt-2" />
                </div>

                <div class="flex items-center gap-2">
                    <x-primary-button>Actualizar factura</x-primary-button>
                    <a href="{{ route('facturas.show', $factura) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Ver detalle</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
