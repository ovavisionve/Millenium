{{--
    Millennium / Incapor — dashboard (PASO 6 en la guía)

    UI: el logo de marca va solo en la barra (`navigation`); aquí el encabezado es solo
    el título "Inicio"/Dashboard para no duplicar un PNG grande que tape métricas.

    Alineación con el hilo operativo (Vic / Pasos 1–6): maestros en Clientes, Categorías,
    Productos → Facturas (PASO 2) → Cobranza (PASO 3) → Reportes (PASO 5); Canceladas
    cubre el cierre PASO 4; este listado resume PASO 6 (gráficas y totales).

    Verificación de precios (propuesta Vic): implementada — `facturas.verificar`, columnas
    `verificado_por` / `fecha_verificacion`, filtro "solo sin verificar" en listados y reportes.

    Gráficas: Chart.js; colores barras/dona alineados a arena + verde cobranza (comentarios
    junto a cada `new Chart`). Datos embebidos en `<script type="application/json">` para
    que el bloque JS sea JavaScript puro (el IDE no interpreta Blade `@` / `{!!` como errores).
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-millennium-dark dark:text-millennium-sand leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Guía operativa Pasos 1–6: texto de negocio; colapsable para no empujar gráficas hacia abajo --}}
            <details class="bg-white dark:bg-gray-800 border border-millennium-dark/10 rounded-lg shadow-sm open:shadow-md group">
                <summary class="cursor-pointer list-none px-4 py-3 font-medium text-millennium-dark border-b border-millennium-dark/10 flex items-center justify-between gap-2">
                    <span>Guía Millennium / Incapor (Pasos 1 a 6)</span>
                    <span class="text-xs text-gray-500 group-open:hidden">Abrir</span>
                    <span class="text-xs text-gray-500 hidden group-open:inline">Cerrar</span>
                </summary>
                <div class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300 space-y-5 max-h-[70vh] overflow-y-auto prose prose-sm max-w-none dark:prose-invert">
                    <section>
                        <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">PASO 1: Registro de base de datos (maestros)</h3>
                        <p class="mt-1"><strong>Módulo de clientes:</strong> registro con RIF/cédula, nombre, teléfono y la <strong>zona</strong> (sector o ruta) y <strong>vendedor</strong>. Es la base para reportes posteriores (por vendedor, por zona, cartera en la calle).</p>
                        <p class="mt-2"><strong>Módulo de productos y categorías:</strong> primero se definen las <strong>categorías</strong> con un <strong>código corto</strong> (único) y un <strong>nombre</strong> claro — por ejemplo: <em>Vaca</em>, <em>Búfalo</em>, <em>Trastes</em> — de modo que en reportes puedas filtrar “todo lo que es Vaca” vs “Búfalo”, etc. Luego cada <strong>producto</strong> se crea dentro de una categoría (descripción, unidad si aplica, precio de referencia) para que en facturación y en el Paso 5 puedas cruzar <strong>vendedor + zona + tipo de producto/categoría</strong> sin mezclar conceptos.</p>
                    </section>
                    <section>
                        <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">PASO 2: Carga manual de factura (control real)</h3>
                        <p class="mt-1"><strong>Carga de datos:</strong> fecha, cliente, líneas con <strong>kilos/cantidad</strong>, <strong>precio real</strong> por producto y <strong>días de crédito</strong>.</p>
                        <p class="mt-2"><strong>Cuentas por cobrar:</strong> al guardar, la factura alimenta la deuda “en la calle”. El sistema debe reflejar <strong>vencida / por vencer / al día</strong> según crédito y fechas.</p>
                    </section>
                    <section>
                        <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">PASO 3: Cobranza y tasa</h3>
                        <p class="mt-1"><strong>Documento:</strong> buscar cliente y elegir la factura a abonar; si son varias, distribuir montos por factura dentro del saldo.</p>
                        <p class="mt-2"><strong>Tasa:</strong> elegir manualmente (p. ej. BCV o paralelo) al registrar cada pago.</p>
                        <p class="mt-2"><strong>Métodos de pago:</strong> divisas (Zelle, Panamá, USDT, efectivo) con referencia y comprobante; bolívares (pago móvil) con conciliación según reglas del negocio.</p>
                        <p class="mt-2"><strong>Registro visual:</strong> fecha de recibo y carga de captura del comprobante.</p>
                    </section>
                    <section>
                        <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">PASO 4: Rebaja y movimientos</h3>
                        <p class="mt-1"><strong>Rebaja al instante:</strong> al registrar el pago, el saldo se actualiza y queda visible para comunicar al cliente.</p>
                        <p class="mt-2"><strong>Historial:</strong> cada factura tiene pestaña de <strong>movimientos</strong> (emisión, abonos con monto/tasa/método, comprobantes). Al completarse el pago, la factura pasa al historial de <strong>canceladas</strong> con trazabilidad.</p>
                    </section>
                    <section>
                        <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">PASO 5: Reportes con filtros cruzados</h3>
                        <p class="mt-1">Por <strong>vendedor</strong>, por <strong>zona</strong>, por fechas, por producto/categoría; combinaciones (ej. vendedor A + zona X + producto tipo “Vaca” en el mes). <strong>Estado de cuenta</strong> por cliente: vencidas / no vencidas.</p>
                    </section>
                    <section>
                        <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">PASO 6: Dashboard y gráficas</h3>
                        <p class="mt-1">Resumen mensual, cobranza vs deuda, flujo por método de pago en la pantalla principal (gráficas de gestión).</p>
                    </section>
                    <p class="text-xs text-gray-500 border-t border-gray-200 dark:border-gray-600 pt-3">Resumen del flujo: la información va desde maestros y facturación manual hasta reportes por zona y vendedor para decisiones en la calle.</p>
                </div>
            </details>

            <p class="text-sm text-gray-600 dark:text-gray-300 px-1">
                Resumen de <strong>{{ $mesActualEtiqueta }}</strong> y últimos seis meses (ventas por emisión de factura; cobranza por fecha de recibo del pago).
            </p>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-millennium-dark/10 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cobrado en el mes (USD)</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">${{ number_format($cobradoMes, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-millennium-dark/10 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Deuda abierta (saldo pendiente)</p>
                    <p class="text-2xl font-semibold text-amber-800 dark:text-amber-200">${{ number_format($deudaAbierta, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-millennium-dark/10 dark:border-gray-700 sm:col-span-2 lg:col-span-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Usuario</p>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ \App\Models\User::roleLabels()[Auth::user()->role] ?? Auth::user()->role }}</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-millennium-dark/10 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Ventas vs cobranza (USD, 6 meses)</h3>
                    <div class="h-72">
                        <canvas id="chartVentasCobranza"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-millennium-dark/10 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Flujo por método de pago (mes actual)</h3>
                    @if (count($flujoLabels) > 0)
                    <div class="h-72">
                        <canvas id="chartFlujoMetodos"></canvas>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 py-8 text-center">Sin pagos registrados este mes.</p>
                    @endif
                </div>
            </div>

            {{-- Millennium: atajos — enlazan PASO 5 (reportes), PASO 3 (cobranza), PASO 4 (canceladas) --}}
            <div class="flex flex-wrap gap-3 text-sm px-1">
                <a href="{{ route('reportes.index') }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Reportes con filtros</a>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <a href="{{ route('cobranza.index') }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Cobranza</a>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <a href="{{ route('cuentas-por-cobrar.index') }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Estados de cuenta</a>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <a href="{{ route('facturas.index', ['alcance' => 'canceladas']) }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Historial canceladas</a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script id="millennium-dashboard-charts-json" type="application/json">
{!! json_encode([
    'chartLabels' => $chartLabels,
    'chartVentas' => $chartVentas,
    'chartCobranza' => $chartCobranza,
    'flujoLabels' => $flujoLabels,
    'flujoValues' => $flujoValues,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') return;

            const jsonEl = document.getElementById('millennium-dashboard-charts-json');
            if (! jsonEl) return;
            let data;
            try {
                data = JSON.parse(jsonEl.textContent);
            } catch (e) {
                return;
            }
            const labels = data.chartLabels;
            const ventas = data.chartVentas;
            const cobranza = data.chartCobranza;
            const flujoLabels = data.flujoLabels;
            const flujoValues = data.flujoValues;

            const ctx1 = document.getElementById('chartVentasCobranza');
            if (ctx1) {
                // Millennium / Incapor — barras PASO 6: ventas `millennium.sand`, cobranza verde; ejes `millennium.dark`
                const grid = 'rgba(148,163,184,0.35)';
                const text = '#321D17';
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Ventas (total facturado)',
                                data: ventas,
                                backgroundColor: 'rgba(221, 179, 135, 0.65)'
                            },
                            {
                                label: 'Cobranza (abonos)',
                                data: cobranza,
                                backgroundColor: 'rgba(16, 185, 129, 0.5)'
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: {
                                    color: text
                                },
                                grid: {
                                    color: grid
                                }
                            },
                            y: {
                                ticks: {
                                    color: text
                                },
                                grid: {
                                    color: grid
                                },
                                beginAtZero: true
                            },
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: text
                                }
                            }
                        },
                    },
                });
            }

            const ctx2 = document.getElementById('chartFlujoMetodos');
            if (ctx2 && flujoLabels.length) {
                // Millennium / Incapor — dona: paleta arena + marrón + acentos (sin SVG; Chart.js canvas)
                const text = '#321D17';
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: flujoLabels,
                        datasets: [{
                            data: flujoValues,
                            backgroundColor: [
                                'rgba(221, 179, 135, 0.85)', 'rgba(50, 29, 23, 0.75)', 'rgba(16, 185, 129, 0.65)',
                                'rgba(245, 158, 11, 0.65)', 'rgba(244, 63, 94, 0.55)', 'rgba(100, 116, 139, 0.6)',
                            ]
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: text
                                }
                            }
                        },
                    },
                });
            }
        });
    </script>
    @endpush
</x-app-layout>