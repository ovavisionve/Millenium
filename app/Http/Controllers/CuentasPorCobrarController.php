<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CuentasPorCobrarController extends Controller
{
    public function index(Request $request): View
    {
        $deudaSub = Factura::query()
            ->selectRaw('cliente_id, SUM(saldo_pendiente) as deuda_total')
            ->where('estado_pago', Factura::ESTADO_PAGO_ABIERTA)
            ->where('saldo_pendiente', '>', 0)
            ->groupBy('cliente_id');

        $baseClientes = Cliente::query()
            ->joinSub($deudaSub, 'd', 'clientes.id', '=', 'd.cliente_id')
            ->with('vendedor')
            ->select('clientes.*', 'd.deuda_total');

        $zonas = (clone $baseClientes)
            ->selectRaw("COALESCE(NULLIF(TRIM(clientes.zona), ''), '') as znorm")
            ->distinct()
            ->orderBy('znorm')
            ->pluck('znorm')
            ->values();

        $q = clone $baseClientes;
        if ($request->filled('zona')) {
            $param = $request->string('zona')->toString();
            if ($param === '_sin_zona') {
                $q->where(function ($w): void {
                    $w->whereNull('clientes.zona')->orWhereRaw("TRIM(clientes.zona) = ''");
                });
            } else {
                $q->whereRaw('TRIM(clientes.zona) = ?', [$param]);
            }
        }

        $clientes = $q->orderBy('clientes.nombre_razon_social')->get();

        $totalCartera = (float) $clientes->sum('deuda_total');

        return view('cuentas-por-cobrar.index', [
            'clientes' => $clientes,
            'zonas' => $zonas,
            'zonaSeleccionada' => $request->string('zona')->toString(),
            'totalCartera' => $totalCartera,
        ]);
    }

    public function show(Cliente $cliente): View
    {
        $facturas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->where('estado_pago', Factura::ESTADO_PAGO_ABIERTA)
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get();

        $totalSaldo = round((float) $facturas->sum('saldo_pendiente'), 2);

        return view('cuentas-por-cobrar.show', [
            'cliente' => $cliente->load('vendedor'),
            'facturas' => $facturas,
            'totalSaldo' => $totalSaldo,
            'etiquetasCartera' => Factura::etiquetasCartera(),
        ]);
    }

    public function estadoCuentaPdf(Cliente $cliente): Response
    {
        $facturas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->where('estado_pago', Factura::ESTADO_PAGO_ABIERTA)
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get();

        $totalSaldo = round((float) $facturas->sum('saldo_pendiente'), 2);
        $cliente->load('vendedor');

        $nombreArchivo = 'estado-cuenta-'.preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) $cliente->id).'.pdf';

        $pdf = Pdf::loadView('pdf.estado-cuenta', [
            'cliente' => $cliente,
            'facturas' => $facturas,
            'totalSaldo' => $totalSaldo,
            'emitidoEn' => now(),
            'etiquetasCartera' => Factura::etiquetasCartera(),
        ])->setPaper('a4');

        return $pdf->download($nombreArchivo);
    }
}
