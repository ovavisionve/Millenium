<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Http\Requests\StorePagosClienteRequest;
use App\Models\Banco;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use App\Support\MovimientosPagoInforme;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CobranzaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->filled('cliente_id')) {
            $cid = $request->integer('cliente_id');
            if ($cid > 0) {
                $cliente = Cliente::query()->find($cid);
                if ($cliente) {
                    return redirect()->route('cobranza.cliente', $cliente);
                }
            }
        }

        $q = $request->string('q')->trim()->toString();
        $clientes = collect();

        if ($q !== '') {
            $clientes = Cliente::query()
                ->whereBuscarTexto($q)
                ->orderBy('nombre_razon_social')
                ->limit(50)
                ->get();
        }

        $clientesTodos = Cliente::query()
            ->orderBy('nombre_razon_social')
            ->get();

        return view('cobranza.index', [
            'q' => $q,
            'clientes' => $clientes,
            'clientesTodos' => $clientesTodos,
        ]);
    }

    public function cliente(Request $request, Cliente $cliente): View
    {
        $facturas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->where('saldo_pendiente', '>', 0)
            ->with(['creadoPor'])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();

        $facturasComprobante = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->limit(120)
            ->get(['id', 'cliente_id', 'numero_factura', 'fecha_emision', 'total', 'saldo_pendiente', 'estado_pago']);

        $destacarIds = collect(explode(',', $request->string('destacar')->toString()))
            ->map(fn (string $s) => (int) trim($s))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        return view('cobranza.cliente', [
            'cliente' => $cliente,
            'facturas' => $facturas,
            'facturasComprobante' => $facturasComprobante,
            'facturasDestacarIds' => $destacarIds,
            'etiquetasCartera' => Factura::etiquetasCartera(),
            'bancos' => Banco::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'metodosDivisas' => Pago::metodosDivisas(),
            'metodosBolivares' => Pago::metodosBolivares(),
        ]);
    }

    /**
     * Comprobante PDF de movimientos para varias facturas del mismo cliente (justificar abonos).
     *
     * @queryParam facturas[] int Ids de facturas del cliente.
     * @queryParam desde date|null Filtra pagos por fecha_recibo.
     * @queryParam hasta date|null
     */
    public function movimientosPagoPdf(Request $request, Cliente $cliente): Response
    {
        $raw = $request->input('facturas', []);
        $ids = is_array($raw)
            ? array_values(array_filter(array_map('intval', $raw), fn (int $i) => $i > 0))
            : array_values(array_filter(array_map('intval', explode(',', (string) $raw)), fn (int $i) => $i > 0));

        if ($ids === [] || count($ids) > 50) {
            abort(422, 'Seleccioná entre 1 y 50 facturas.');
        }

        $facturas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->whereIn('id', $ids)
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get();

        if ($facturas->count() !== count(array_unique($ids))) {
            abort(404);
        }

        $desde = $request->filled('desde') ? $request->date('desde')->startOfDay() : null;
        $hasta = $request->filled('hasta') ? $request->date('hasta')->endOfDay() : null;

        $pagos = Pago::query()
            ->whereIn('factura_id', $facturas->pluck('id'))
            ->with(['registradoPor', 'factura'])
            ->when($desde, fn ($q) => $q->whereDate('fecha_recibo', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_recibo', '<=', $hasta))
            ->orderBy('fecha_recibo')
            ->orderBy('id')
            ->get();

        $periodo = MovimientosPagoInforme::textoPeriodoRecibos($desde, $hasta);
        $data = MovimientosPagoInforme::datosParaVistaPdf($cliente, $facturas, $pagos, $periodo);
        $pdf = Pdf::loadView('pdf.movimientos-pago', $data)->setPaper('a4', 'landscape');

        return $pdf->download('movimientos-pago-cliente-'.$cliente->id.'.pdf');
    }

    public function create(Factura $factura): View|RedirectResponse
    {
        if ((float) $factura->saldo_pendiente <= 0) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('status', 'Esta factura no tiene saldo pendiente.');
        }

        $factura->load('cliente');

        return view('cobranza.pago-create', [
            'factura' => $factura,
            'bancos' => Banco::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'tiposTasa' => Pago::tiposTasa(),
            'metodosDivisas' => Pago::metodosDivisas(),
            'metodosBolivares' => Pago::metodosBolivares(),
        ]);
    }

    public function store(StorePagoRequest $request, Factura $factura): RedirectResponse
    {
        if ((float) $factura->saldo_pendiente <= 0) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('status', 'Esta factura no tiene saldo pendiente.');
        }

        $validated = $request->validated();
        $monto = round((float) $validated['monto_aplicado_usd'], 2);

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');
        }

        DB::transaction(function () use ($request, $validated, $factura, $monto, $comprobantePath): void {
            $this->crearPagoYRebajar($request, $factura, $validated, $monto, $comprobantePath);
        });

        $factura->refresh();

        if ((float) $factura->saldo_pendiente <= 0) {
            return redirect()
                ->route('facturas.movimientos-pago.pdf', $factura)
                ->with('status', 'Factura pagada completamente. Se generó el comprobante de movimientos para imprimir.');
        }

        return redirect()
            ->route('facturas.show', $factura)
            ->with('status', 'Pago registrado correctamente.');
    }

    public function storeCliente(StorePagosClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validated();
        $abonos = $validated['abonos'];
        unset($validated['abonos']);

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');
        }

        $count = 0;
        $facturaUnicaPagada = null;
        DB::transaction(function () use ($request, $validated, $cliente, $abonos, $comprobantePath, &$count): void {
            foreach ($abonos as $facturaId => $raw) {
                $monto = round((float) ($raw ?? 0), 2);
                if ($monto <= 0) {
                    continue;
                }
                $factura = Factura::query()
                    ->where('cliente_id', $cliente->id)
                    ->whereKey($facturaId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->crearPagoYRebajar($request, $factura, $validated, $monto, $comprobantePath);
                $count++;
            }
        });

        $facturasPagadas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->whereIn('id', array_keys(array_filter($abonos, fn ($raw) => round((float) ($raw ?? 0), 2) > 0)))
            ->where('saldo_pendiente', '<=', 0)
            ->orderBy('id')
            ->get();

        if ($count === 1 && $facturasPagadas->count() === 1) {
            $facturaUnicaPagada = $facturasPagadas->first();
        }

        $msg = $count === 1
            ? 'Abono registrado correctamente.'
            : $count.' abonos registrados correctamente.';

        if ($facturaUnicaPagada) {
            return redirect()
                ->route('facturas.movimientos-pago.pdf', $facturaUnicaPagada)
                ->with('status', 'Factura pagada completamente. Se generó el comprobante de movimientos para imprimir.');
        }

        return redirect()
            ->route('cobranza.cliente', $cliente)
            ->with('status', $msg);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function crearPagoYRebajar(Request $request, Factura $factura, array $validated, float $monto, ?string $comprobantePath): void
    {
        $montoBs = isset($validated['monto_bs']) && $validated['monto_bs'] !== null && $validated['monto_bs'] !== ''
            ? round((float) $validated['monto_bs'], 2)
            : null;

        $estadoBanco = ($validated['metodo_pago'] ?? null) === Pago::METODO_PAGO_MOVIL
            ? Pago::VALIDACION_BANCO_PENDIENTE
            : null;

        Pago::create([
            'factura_id' => $factura->id,
            'fecha_recibo' => $validated['fecha_recibo'],
            'monto_aplicado_usd' => $monto,
            'tipo_tasa' => $validated['tipo_tasa'],
            'valor_tasa' => $validated['valor_tasa'],
            'monto_bs' => $montoBs,
            'metodo_pago' => $validated['metodo_pago'],
            'estado_validacion_banco' => $estadoBanco,
            'referencia' => $validated['referencia'] ?? null,
            'banco_destino' => $validated['banco_destino'] ?? null,
            'recibido_por' => $validated['recibido_por'] ?? null,
            'comprobante_path' => $comprobantePath,
            'notas' => $validated['notas'] ?? null,
            'registrado_por' => $request->user()->id,
        ]);

        $factura->refresh();
        $factura->aplicarAbonoUsd($monto);
    }
}
