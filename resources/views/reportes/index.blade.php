<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Reportes</h2>
            <a href="{{ route('dashboard') }}"><x-secondary-button type="button">{{ __('Dashboard') }}</x-secondary-button></a>
        </div>
    </x-slot>

    @php
        /** Millennium: data mínima para buscador de estado (id + nombre + ISO) */
        $estadosData = collect($estados ?? [])->map(fn ($e) => [
            'id' => (int) $e->id_estado,
            'nombre' => (string) $e->nombre_estado,
            // ISO se mantiene solo como metadata (no se muestra en UI de reportes).
            'iso' => (string) ($e->codigo_iso_3166_2 ?? ''),
        ])->values();
        $fechaDesdeReporte = old('desde', request('desde'));
        $fechaHastaReporte = old('hasta', request('hasta'));

        // Límites HTML5 para type=date: si max < value el navegador puede ocultar el input.
        $hoyReporte = \Carbon\Carbon::today();
        $reporteEmisionMaxStr = $hoyReporte->format('Y-m-d');

        $dDesde = null;
        $dHasta = null;
        try {
            if (filled($fechaDesdeReporte)) {
                $dDesde = \Carbon\Carbon::parse($fechaDesdeReporte)->startOfDay();
            }
        } catch (\Throwable $e) {
            $dDesde = null;
        }
        try {
            if (filled($fechaHastaReporte)) {
                $dHasta = \Carbon\Carbon::parse($fechaHastaReporte)->startOfDay();
            }
        } catch (\Throwable $e) {
            $dHasta = null;
        }

        $invertido = $dDesde && $dHasta && $dDesde->gt($dHasta);

        $reporteDesdeMaxStr = $reporteEmisionMaxStr;
        if ($invertido) {
            $reporteDesdeMaxStr = null;
        } elseif ($dHasta) {
            $reporteDesdeMaxStr = ($dHasta->lte($hoyReporte) ? $dHasta : $hoyReporte)->format('Y-m-d');
        }

        if ($dDesde && $dDesde->gt($hoyReporte)) {
            $reporteDesdeMaxStr = null;
        }

        if ($reporteDesdeMaxStr !== null && $dDesde && $dDesde->gt(\Carbon\Carbon::parse($reporteDesdeMaxStr)->startOfDay())) {
            $reporteDesdeMaxStr = null;
        }

        $reporteHastaMaxStr = $reporteEmisionMaxStr;
        if ($dHasta && $dHasta->gt($hoyReporte)) {
            $reporteHastaMaxStr = null;
        }

        $reporteHastaMinStr = null;
        if (! $invertido && $dDesde) {
            $reporteHastaMinStr = $dDesde->format('Y-m-d');
        }

        if ($reporteHastaMinStr !== null && $reporteHastaMaxStr !== null) {
            if (\Carbon\Carbon::parse($reporteHastaMinStr)->gt(\Carbon\Carbon::parse($reporteHastaMaxStr))) {
                $reporteHastaMinStr = null;
            }
        }
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <p class="text-sm text-gray-600 dark:text-gray-300 px-1">
                Combiná filtros (vendedor registrado en la factura, zona del cliente, fechas, categoría, <strong>estado de pago</strong>) para ver <strong>precio unitario</strong>, <strong>saldo de la factura</strong> y <strong>monto total del documento</strong>.
                Con <strong>Estado pago: Pagada</strong> obtenés el <strong>historial de ventas ya canceladas</strong> (misma idea que “Documentos cancelados”, pero con los cortes del reporte).
                La columna <strong>Verificación</strong> muestra qué usuario confirmó y cuándo.
                Podés marcar <strong>solo sin verificar</strong> para revisar pendientes.
                El <strong>estado de cuenta</strong> lista facturas del cliente (vencidas / no vencidas) con verificación.
            </p>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">Ventas y líneas (filtros cruzados)</h3>
                @if (session('error_pdf'))
                <div class="rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                    {{ session('error_pdf') }}
                </div>
                @endif
                <form
                    id="form-reporte-ventas-lineas"
                    method="get"
                    class="space-y-4"
                    x-data="{
                        estados: @js($estadosData),
                        estadoId: @js((string) request('id_estado', '')),
                        estadoTexto: '',
                        estadoOpen: false,
                        estadoActivoIdx: 0,
                        init() {
                            this.syncEstadoTexto();
                        },
                        syncEstadoTexto() {
                            const id = String(this.estadoId ?? '');
                            if (!id) {
                                this.estadoTexto = '';
                                return;
                            }
                            const row = (this.estados || []).find((e) => String(e.id) === id);
                            this.estadoTexto = row ? String(row.nombre) : '';
                        },
                        estadosFiltrados() {
                            const q = String(this.estadoTexto || '').trim().toLowerCase();
                            const base = this.estados || [];
                            const rows = q
                                ? base.filter((e) => String(e.nombre || '').toLowerCase().includes(q))
                                : base;
                            return rows.slice(0, 50);
                        },
                        estadoMover(delta) {
                            if (!this.estadoOpen) this.estadoOpen = true;
                            const lista = this.estadosFiltrados();
                            const n = lista.length;
                            if (n <= 0) return;
                            this.estadoActivoIdx = (this.estadoActivoIdx + delta + n) % n;
                        },
                        estadoConfirmarActivo() {
                            const lista = this.estadosFiltrados();
                            const e = lista[this.estadoActivoIdx];
                            if (e) this.seleccionarEstado(e);
                        },
                        seleccionarEstado(e) {
                            if (!e) return;
                            this.estadoId = String(e.id);
                            this.estadoTexto = String(e.nombre);
                            this.estadoOpen = false;
                        },
                        limpiarEstado() {
                            this.estadoId = '';
                            this.estadoTexto = '';
                            this.estadoOpen = false;
                            this.estadoActivoIdx = 0;
                        },
                        onEstadoBlur() {
                            // Si el usuario escribió pero no eligió una opción válida, no mandamos texto “a ciegas”.
                            window.setTimeout(() => {
                                if (this.estadoOpen) return;
                                if (String(this.estadoId || '').trim() !== '') return;
                                const q = String(this.estadoTexto || '').trim().toLowerCase();
                                if (!q) return;
                                const exact = (this.estados || []).some((e) => String(e.nombre || '').trim().toLowerCase() === q);
                                if (!exact) this.estadoTexto = '';
                            }, 0);
                        },
                    }"
                >
                    <input type="hidden" name="generar" value="1" />

                    {{-- Fila 1: mismas alturas (sin texto de ayuda dentro de la celda) --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-start">
                        <div>
                            <x-input-label for="desde" value="Desde (emisión)" />
                            {{-- input nativo: @if entre atributos de <x-text-input> rompe el render del componente Blade --}}
                            <input
                                id="desde"
                                name="desde"
                                type="date"
                                value="{{ $fechaDesdeReporte }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-millennium-sand dark:focus:ring-millennium-sand"
                                @if ($reporteDesdeMaxStr !== null) max="{{ $reporteDesdeMaxStr }}" @endif
                            />
                            <x-input-error class="mt-1" :messages="$errors->get('desde')" />
                        </div>
                        <div>
                            <x-input-label for="hasta" value="Hasta (emisión)" />
                            <input
                                id="hasta"
                                name="hasta"
                                type="date"
                                value="{{ $fechaHastaReporte }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-millennium-sand dark:focus:ring-millennium-sand"
                                @if ($reporteHastaMinStr) min="{{ $reporteHastaMinStr }}" @endif
                                @if ($reporteHastaMaxStr !== null) max="{{ $reporteHastaMaxStr }}" @endif
                            />
                            <x-input-error class="mt-1" :messages="$errors->get('hasta')" />
                        </div>
                        <div>
                            <x-input-label for="vendedor_id" value="Vendedor (factura)" />
                            <select id="vendedor_id" name="vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach ($vendedores as $v)
                                <option value="{{ $v->id }}" @selected((string) request('vendedor_id')===(string) $v->id)>{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="estado_buscar_reporte" value="Estado (cliente)" />
                            <div class="relative mt-1" @click.outside="estadoOpen = false">
                                <input type="hidden" id="id_estado" name="id_estado" :value="estadoId" />
                                <input
                                    id="estado_buscar_reporte"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="Escribí: Portuguesa, Aragua…"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
                                    x-model="estadoTexto"
                                    @focus="estadoOpen = true"
                                    @input="estadoId = ''; estadoOpen = true; estadoActivoIdx = 0"
                                    @keydown.down.prevent="estadoMover(1)"
                                    @keydown.up.prevent="estadoMover(-1)"
                                    @keydown.enter.prevent="estadoConfirmarActivo()"
                                    @keydown.escape.prevent="estadoOpen = false"
                                    @blur="onEstadoBlur()"
                                />
                                <div
                                    x-show="estadoOpen"
                                    x-cloak
                                    class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                    style="top: calc(100% + 0.25rem);"
                                >
                                    <div class="max-h-64 overflow-auto py-1">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                            @click="limpiarEstado()"
                                        >
                                            <span class="font-medium">Todos</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400"> · sin filtrar por estado</span>
                                        </button>
                                        <template x-for="(e, idx) in estadosFiltrados()" :key="e.id">
                                            <button
                                                type="button"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                                :class="idx === estadoActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                                @click="seleccionarEstado(e)"
                                            >
                                                <span class="font-medium" x-text="e.nombre"></span>
                                            </button>
                                        </template>
                                        <div x-show="estadosFiltrados().length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            No hay coincidencias.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        <span class="font-medium text-gray-600 dark:text-gray-300">Estado (cliente):</span>
                        solo se listan entidades con <strong>ventas/líneas</strong> según los filtros actuales (fechas, vendedor, categoría, estado de pago, sin verificar).
                    </p>

                    {{-- Fila 2: misma grilla de 4 columnas; etiquetas alineadas con la fila 1 --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-start">
                        <div>
                            <x-input-label for="categoria_id" value="Categoría" />
                            <select id="categoria_id" name="categoria_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                <option value="">Todas</option>
                                @foreach ($categorias as $c)
                                <option value="{{ $c->id }}" @selected((string) request('categoria_id')===(string) $c->id)>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="estado_pago" value="Estado pago (factura)" />
                            <select id="estado_pago" name="estado_pago" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="{{ \App\Models\Factura::ESTADO_PAGO_ABIERTA }}" @selected(request('estado_pago')===\App\Models\Factura::ESTADO_PAGO_ABIERTA)>Facturas por pagar</option>
                                <option value="{{ \App\Models\Factura::ESTADO_PAGO_PAGADA }}" @selected(request('estado_pago')===\App\Models\Factura::ESTADO_PAGO_PAGADA)>Pagadas (historial / canceladas)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="solo_sin_verificar" value="Verificación" />
                            <div class="mt-1 flex min-h-10 items-center">
                                <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input id="solo_sin_verificar" type="checkbox" name="solo_sin_verificar" value="1" class="rounded border-gray-300 dark:border-gray-700" @checked(request('solo_sin_verificar')) />
                                    Solo facturas sin verificar
                                </label>
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Acciones" />
                            <div class="mt-1 flex min-h-10 flex-wrap items-center gap-2">
                                <x-primary-button type="submit" class="h-10">Generar</x-primary-button>
                                <x-secondary-button type="submit" class="h-10" formaction="{{ route('reportes.pdf-resumen') }}" form="form-reporte-ventas-lineas">
                                    PDF resumen
                                </x-secondary-button>
                                <a href="{{ route('reportes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Limpiar</a>
                            </div>
                        </div>
                    </div>
                </form>

                @if (request()->boolean('generar') && $lineas)
                <div class="grid sm:grid-cols-3 gap-3 pt-2 text-sm border-t border-gray-200 dark:border-gray-600">
                    <div class="rounded-md bg-gray-50 dark:bg-gray-700/50 p-3">
                        <p class="text-gray-500 dark:text-gray-400">Total facturado (filtro)</p>
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
                                <th class="px-3 py-2 font-medium">Categoría</th>
                                <th class="px-3 py-2 font-medium text-end">Cant. animales</th>
                                <th class="px-3 py-2 font-medium text-end">unidad/Kilos</th>
                                <th class="px-3 py-2 font-medium text-end">P. unit.</th>
                                <th class="px-3 py-2 font-medium text-end">Saldo factura</th>
                                <th class="px-3 py-2 font-medium">Verificación</th>
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
                                <td class="px-3 py-2">{{ $linea->factura->vendedor?->name ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    {{ $linea->categoria->nombre }}
                                    <div class="text-xs text-gray-500 font-mono">{{ $linea->categoria->codigo }}</div>
                                </td>
                                <td class="px-3 py-2 text-end">{{ $linea->cantidad_animales !== null ? number_format($linea->cantidad_animales, 0, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2 text-end">{{ number_format($linea->cantidad, 3) }} {{ \App\Models\Categoria::unidadAbreviada()[$linea->categoria->unidad] ?? $linea->categoria->unidad }}</td>
                                <td class="px-3 py-2 text-end">${{ rtrim(rtrim(number_format((float) $linea->precio_unitario, 4, '.', ''), '0'), '.') }}</td>
                                <td class="px-3 py-2 text-end font-medium">${{ number_format($linea->factura->saldo_pendiente, 2) }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @if ($linea->factura->textoVerificacion())
                                    <span class="text-green-700 dark:text-green-300">{{ $linea->factura->textoVerificacion() }}</span>
                                    @else
                                    <span class="text-amber-700 dark:text-amber-300">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-gray-500">Sin líneas con esos filtros.</td>
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
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Para el <strong>total que debe cada cliente</strong>, listado por <strong>zona</strong> y el <strong>PDF para enviar al cliente</strong>, usá el módulo
                    <a href="{{ route('cuentas-por-cobrar.index') }}" class="font-medium text-millennium-dark dark:text-millennium-sand hover:underline">Estados de cuenta (por zona)</a>
                    (<span class="whitespace-nowrap">Facturación → Estados de cuenta</span>). Los datos son en vivo: al registrar un pago en Cobranza, los saldos se rebajan solos.
                </p>
                <form method="get" class="flex flex-col sm:flex-row gap-3 sm:items-end flex-wrap">
                    @if (request()->boolean('generar'))
                    <input type="hidden" name="generar" value="1" />
                    @foreach (['desde', 'hasta', 'vendedor_id', 'id_estado', 'categoria_id'] as $k)
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
                @php
                    $totalSaldoVista = round((float) $estadoCuentaFacturas->sum('saldo_pendiente'), 2);
                @endphp
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        <strong>{{ $clienteEstado->nombre_razon_social }}</strong>
                        · Zona: {{ $clienteEstado->zona ?: '—' }}
                        · Vendedor: {{ $clienteEstado->vendedor?->name ?? '—' }}
                        · <span class="font-semibold text-amber-800 dark:text-amber-200">Suma saldos (esta vista): ${{ number_format($totalSaldoVista, 2) }}</span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('cuentas-por-cobrar.show', $clienteEstado) }}"><x-secondary-button type="button">Módulo / detalle</x-secondary-button></a>
                        <a href="{{ route('cuentas-por-cobrar.estado-cuenta-pdf', $clienteEstado) }}"><x-primary-button type="button">Descargar PDF estado de cuenta</x-primary-button></a>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">El PDF incluye solo facturas <strong>por pagar con saldo pendiente</strong> (lo que aún debe el cliente). Esta tabla puede incluir también facturas pagadas si no filtrás solo vencidas.</p>
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
                                <th class="px-3 py-2 font-medium">Verif.</th>
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
                                <td class="px-3 py-2">{{ $f->estado_pago === \App\Models\Factura::ESTADO_PAGO_PAGADA ? 'Pagada' : 'Factura por pagar' }}</td>
                                <td class="px-3 py-2 text-xs">
                                    @if ($f->textoVerificacion())
                                    <span class="text-green-700 dark:text-green-300">{{ $f->textoVerificacion() }}</span>
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
                        @if ($estadoCuentaFacturas->isNotEmpty())
                        <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold">
                            <tr>
                                <td colspan="4" class="px-3 py-2 text-end">Total saldo pendiente (suma columna)</td>
                                <td class="px-3 py-2 text-end">${{ number_format($totalSaldoVista, 2) }}</td>
                                <td colspan="3" class="px-3 py-2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>