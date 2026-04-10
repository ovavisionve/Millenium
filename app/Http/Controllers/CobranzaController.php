<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Http\Requests\StorePagosClienteRequest;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CobranzaController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString();
        $clientes = collect();

        if ($q !== '') {
            $clientes = Cliente::query()
                ->whereBuscarTexto($q)
                ->orderBy('nombre_razon_social')
                ->limit(50)
                ->get();
        }

        return view('cobranza.index', [
            'q' => $q,
            'clientes' => $clientes,
        ]);
    }

    public function cliente(Cliente $cliente): View
    {
        $facturas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->where('saldo_pendiente', '>', 0)
            ->with(['creadoPor'])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();

        return view('cobranza.cliente', [
            'cliente' => $cliente,
            'facturas' => $facturas,
            'etiquetasCartera' => Factura::etiquetasCartera(),
            'metodosDivisas' => Pago::metodosDivisas(),
            'metodosBolivares' => Pago::metodosBolivares(),
        ]);
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

        $msg = $count === 1
            ? 'Abono registrado correctamente.'
            : $count.' abonos registrados correctamente.';

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
            'comprobante_path' => $comprobantePath,
            'notas' => $validated['notas'] ?? null,
            'registrado_por' => $request->user()->id,
        ]);

        $factura->refresh();
        $factura->aplicarAbonoUsd($monto);
    }
}
