<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard — Millennium
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 px-1">
                Resumen de <strong>{{ $mesActualEtiqueta }}</strong> y últimos seis meses (ventas por emisión de factura; cobranza por fecha de recibo del pago).
            </p>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cobrado en el mes (USD)</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">${{ number_format($cobradoMes, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Deuda abierta (saldo pendiente)</p>
                    <p class="text-2xl font-semibold text-amber-800 dark:text-amber-200">${{ number_format($deudaAbierta, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-gray-200 dark:border-gray-700 sm:col-span-2 lg:col-span-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Usuario</p>
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ \App\Models\User::roleLabels()[Auth::user()->role] ?? Auth::user()->role }}</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Ventas vs cobranza (USD, 6 meses)</h3>
                    <div class="h-72">
                        <canvas id="chartVentasCobranza"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-gray-200 dark:border-gray-700">
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

            <div class="flex flex-wrap gap-3 text-sm px-1">
                <a href="{{ route('reportes.index') }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">Reportes con filtros</a>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <a href="{{ route('cobranza.index') }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">Cobranza</a>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <a href="{{ route('facturas.canceladas') }}" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">Documentos cancelados</a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Chart === 'undefined') return;

                const labels = @json($chartLabels);
                const ventas = @json($chartVentas);
                const cobranza = @json($chartCobranza);

                const ctx1 = document.getElementById('chartVentasCobranza');
                if (ctx1) {
                    const dark = document.documentElement.classList.contains('dark');
                    const grid = dark ? 'rgba(148,163,184,0.2)' : 'rgba(148,163,184,0.35)';
                    const text = dark ? '#e5e7eb' : '#374151';
                    new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                { label: 'Ventas (total facturado)', data: ventas, backgroundColor: 'rgba(79, 70, 229, 0.55)' },
                                { label: 'Cobranza (abonos)', data: cobranza, backgroundColor: 'rgba(16, 185, 129, 0.55)' },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { ticks: { color: text }, grid: { color: grid } },
                                y: { ticks: { color: text }, grid: { color: grid }, beginAtZero: true },
                            },
                            plugins: { legend: { labels: { color: text } } },
                        },
                    });
                }

                const flujoLabels = @json($flujoLabels);
                const flujoValues = @json($flujoValues);
                const ctx2 = document.getElementById('chartFlujoMetodos');
                if (ctx2 && flujoLabels.length) {
                    const dark = document.documentElement.classList.contains('dark');
                    const text = dark ? '#e5e7eb' : '#374151';
                    new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: flujoLabels,
                            datasets: [{ data: flujoValues, backgroundColor: [
                                'rgba(79, 70, 229, 0.75)', 'rgba(16, 185, 129, 0.75)', 'rgba(245, 158, 11, 0.75)',
                                'rgba(236, 72, 153, 0.75)', 'rgba(59, 130, 246, 0.75)', 'rgba(100, 116, 139, 0.75)',
                            ] }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom', labels: { color: text } } },
                        },
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
