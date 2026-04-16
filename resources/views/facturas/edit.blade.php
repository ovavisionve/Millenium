<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Editar factura {{ $factura->numero_factura ?? '#'.$factura->id }}</h2>
    </x-slot>

    @php
    $clienteIdInicial = old('cliente_id') !== null && old('cliente_id') !== '' ? (string) old('cliente_id') : (string) $factura->cliente_id;
    $vendedorIdInicial = old('vendedor_id') !== null && old('vendedor_id') !== '' ? (string) old('vendedor_id') : ($factura->vendedor_id !== null ? (string) $factura->vendedor_id : '');
    @endphp

    <div
        class="py-6"
        x-data="{
            lineas: @js($lineasForm),
            clientes: @js($clientesResumen),
            clienteId: @js($clienteIdInicial),
            clienteTexto: '',
            clienteOpen: false,
            clienteActivoIdx: 0,
            vendedorId: @js($vendedorIdInicial),
            init() {
                this.syncClienteTexto();
                this.$watch('clienteId', () => {
                    const c = this.clienteActual();
                    if (!c) {
                        this.vendedorId = '';
                        this.syncClienteTexto();
                        return;
                    }
                    const vid = c.vendedor_id;
                    this.vendedorId = (vid !== undefined && vid !== null && String(vid) !== '') ? String(vid) : '';
                    this.syncClienteTexto();
                });
            },
            clienteActual() {
                const id = this.clienteId;
                if (id === '' || id === null || id === undefined) return null;
                return this.clientes[String(id)] ?? null;
            },
            syncClienteTexto() {
                const c = this.clienteActual();
                this.clienteTexto = c ? ((c?.nombre ?? '') + ' — ' + (c?.rif ?? '')) : '';
            },
            clientesLista() {
                return Object.entries(this.clientes || {}).map(([id, c]) => ({
                    id: String(id),
                    nombre: c?.nombre ?? '',
                    rif: c?.rif ?? '',
                }));
            },
            clientesFiltrados() {
                const q = (this.clienteTexto || '').trim().toLowerCase();
                const lista = this.clientesLista();
                if (!q) return lista.slice(0, 200);
                return lista
                    .filter((c) => {
                        const nombre = String(c?.nombre ?? '').toLowerCase();
                        const rif = String(c?.rif ?? '').toLowerCase();
                        return nombre.includes(q) || rif.includes(q) || String(c?.id ?? '').includes(q);
                    })
                    .slice(0, 200);
            },
            onClienteTextoInput(ev) {
                this.clienteTexto = ev?.target?.value ?? '';
                this.clienteOpen = true;
                this.clienteActivoIdx = 0;
            },
            seleccionarCliente(c) {
                if (!c) return;
                this.clienteId = String(c.id);
                this.clienteTexto = (c?.nombre ?? '') + ' — ' + (c?.rif ?? '');
                this.clienteOpen = false;
            },
            clienteMover(delta) {
                if (!this.clienteOpen) this.clienteOpen = true;
                const n = this.clientesFiltrados().length;
                if (n <= 0) return;
                this.clienteActivoIdx = (this.clienteActivoIdx + delta + n) % n;
            },
            clienteConfirmarActivo() {
                const lista = this.clientesFiltrados();
                const c = lista[this.clienteActivoIdx];
                if (c) this.seleccionarCliente(c);
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
        }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="post" action="{{ route('facturas.update', $factura) }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Datos generales</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="cliente_id" value="Cliente" />
                            <div class="relative mt-1" @click.outside="clienteOpen = false">
                                <input type="hidden" id="cliente_id" name="cliente_id" :value="clienteId" />
                                <input
                                    id="cliente_buscar"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="Escribí nombre o RIF…"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
                                    :value="clienteTexto"
                                    @focus="clienteOpen = true"
                                    @input="onClienteTextoInput($event)"
                                    @keydown.down.prevent="clienteMover(1)"
                                    @keydown.up.prevent="clienteMover(-1)"
                                    @keydown.enter.prevent="clienteConfirmarActivo()"
                                    @keydown.escape.prevent="clienteOpen = false"
                                />
                                <div
                                    x-show="clienteOpen"
                                    x-cloak
                                    class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                    style="top: calc(100% + 0.25rem);"
                                >
                                    <div class="max-h-64 overflow-auto py-1">
                                        <template x-for="(c, idx) in clientesFiltrados()" :key="c.id">
                                            <button
                                                type="button"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                                :class="idx === clienteActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                                @click="seleccionarCliente(c)"
                                            >
                                                <span class="font-medium" x-text="c.nombre"></span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="' — ' + c.rif"></span>
                                            </button>
                                        </template>
                                        <div x-show="clientesFiltrados().length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            No hay coincidencias.
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <x-input-label for="vendedor_id" value="Vendedor (factura)" />
                            <select
                                id="vendedor_id"
                                name="vendedor_id"
                                x-model="vendedorId"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                <option value="">— Sin asignar —</option>
                                @foreach ($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ \App\Models\User::roleLabels()[$v->role] ?? $v->role }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Al cambiar de cliente se sugiere el vendedor del maestro; podés corregirlo.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('vendedor_id')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="numero_factura" value="Nº factura" />
                            <x-text-input
                                id="numero_factura"
                                name="numero_factura"
                                type="text"
                                class="mt-1 block w-full font-mono"
                                maxlength="64"
                                :value="old('numero_factura', $factura->numero_factura)"
                                required
                                autocomplete="off" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Debe seguir siendo único en el sistema (no puede coincidir con otra factura).</p>
                            <x-input-error class="mt-2" :messages="$errors->get('numero_factura')" />
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
                        <div class="sm:col-span-2">
                            <x-input-label for="metodo_pago_previsto" value="¿Cómo va a pagar el cliente?" />
                            <select id="metodo_pago_previsto" name="metodo_pago_previsto" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                <option value="">— Sin indicar (definir luego en cobranza) —</option>
                                @foreach ($metodosPagoFactura as $val => $label)
                                <option value="{{ $val }}" @selected((string) old('metodo_pago_previsto', $factura->metodo_pago_previsto ?? '') === (string) $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Referencia al facturar; los abonos reales siguen en <strong>Cobranza</strong>.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('metodo_pago_previsto')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="observaciones" value="Observaciones" />
                            <textarea
                                id="observaciones"
                                name="observaciones"
                                rows="3"
                                maxlength="5000"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm text-sm"
                                placeholder="Notas al cliente, condiciones acordadas, etc. (opcional)">{{ old('observaciones', $factura->observaciones) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Visible en el detalle y en el documento de deuda (PDF).</p>
                            <x-input-error class="mt-2" :messages="$errors->get('observaciones')" />
                        </div>
                    </div>
                    <p class="text-xs text-amber-700 dark:text-amber-300">Al guardar, si la factura estaba verificada, deberá volver a verificarse.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Líneas de factura</h3>
                        <button type="button" @click="lineas.push({ categoria_id: '', cantidad_animales: '', cantidad: '', precio_unitario: '' })" class="text-sm text-millennium-dark dark:text-millennium-sand hover:underline">+ Agregar línea</button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-2 py-2 text-left">Categoría</th>
                                    <th class="px-2 py-2 text-end w-24">Cant. animales</th>
                                    <th class="px-2 py-2 text-left w-28">unidad/Kilos</th>
                                    <th class="px-2 py-2 text-left w-32">Precio unit.</th>
                                    <th class="px-2 py-2 text-end w-28">Subtotal</th>
                                    <th class="px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(linea, index) in lineas" :key="index">
                                    <tr class="border-t border-gray-200 dark:border-gray-600">
                                        <td class="px-2 py-2">
                                            <select class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][categoria_id]'" x-model="linea.categoria_id" required>
                                                <option value="">— Categoría —</option>
                                                @foreach ($categorias as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->nombre }} — {{ $cat->codigo }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="1" min="0" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm text-end" x-bind:name="'lineas[' + index + '][cantidad_animales]'" x-model="linea.cantidad_animales" placeholder="—" />
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
                                    <td colspan="4" class="px-2 py-3 text-end font-semibold text-gray-800 dark:text-gray-100">Total USD (nuevo total de factura)</td>
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