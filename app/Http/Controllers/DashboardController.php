<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $inicio = Carbon::now()->subMonths(5)->startOfMonth();

        $ventasPorMes = Factura::query()
            ->selectRaw('DATE_FORMAT(fecha_emision, "%Y-%m") as ym')
            ->selectRaw('SUM(total) as total_usd')
            ->where('fecha_emision', '>=', $inicio->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $cobranzaPorMes = Pago::query()
            ->selectRaw('DATE_FORMAT(fecha_recibo, "%Y-%m") as ym')
            ->selectRaw('SUM(monto_aplicado_usd) as total_usd')
            ->where('fecha_recibo', '>=', $inicio->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $mesActual = Carbon::now()->format('Y-m');
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        $cobradoMes = (float) Pago::query()
            ->whereBetween('fecha_recibo', [$inicioMes, $finMes])
            ->sum('monto_aplicado_usd');

        $deudaAbierta = (float) Factura::query()
            ->where('estado_pago', Factura::ESTADO_PAGO_ABIERTA)
            ->sum('saldo_pendiente');

        $flujoMetodosMes = Pago::query()
            ->select('metodo_pago')
            ->selectRaw('SUM(monto_aplicado_usd) as total_usd')
            ->whereBetween('fecha_recibo', [$inicioMes, $finMes])
            ->groupBy('metodo_pago')
            ->orderByDesc('total_usd')
            ->get();

        $labels = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->format('Y-m'))->values();

        $ventasMap = $ventasPorMes->pluck('total_usd', 'ym');
        $cobranzaMap = $cobranzaPorMes->pluck('total_usd', 'ym');

        $chartVentas = $labels->map(fn (string $ym) => round((float) ($ventasMap[$ym] ?? 0), 2))->values()->all();
        $chartCobranza = $labels->map(fn (string $ym) => round((float) ($cobranzaMap[$ym] ?? 0), 2))->values()->all();
        $chartLabels = $labels->map(fn (string $ym) => Carbon::createFromFormat('Y-m', $ym)->translatedFormat('M Y'))->all();

        $metodosLabels = Pago::metodosPago();
        $flujoLabels = $flujoMetodosMes->map(fn ($row) => $metodosLabels[$row->metodo_pago] ?? $row->metodo_pago)->all();
        $flujoValues = $flujoMetodosMes->map(fn ($row) => round((float) $row->total_usd, 2))->all();

        return view('dashboard', [
            'chartLabels' => $chartLabels,
            'chartVentas' => $chartVentas,
            'chartCobranza' => $chartCobranza,
            'cobradoMes' => $cobradoMes,
            'deudaAbierta' => $deudaAbierta,
            'flujoLabels' => $flujoLabels,
            'flujoValues' => $flujoValues,
            'mesActualEtiqueta' => Carbon::createFromFormat('Y-m', $mesActual)->translatedFormat('F Y'),
        ]);
    }
}
