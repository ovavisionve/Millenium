<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\SaldoAFavor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaldoAFavorController extends Controller
{
    public function create(Cliente $cliente): View
    {
        $facturas = Factura::query()
            ->where('cliente_id', $cliente->id)
            ->where('saldo_pendiente', '>', 0)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();

        return view('cobranza.saldo-favor', [
            'cliente' => $cliente,
            'facturas' => $facturas,
            'saldoAFavorUsd' => $cliente->saldoAFavorDisponibleUsd(),
        ]);
    }

    public function store(Request $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validate([
            'fecha_recibo' => ['required', 'date'],
            'abonos' => ['required', 'array', 'min:1'],
            'abonos.*' => ['nullable', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);

        $abonos = $validated['abonos'];
        $sumaUsd = 0.0;
        foreach ($abonos as $facturaId => $raw) {
            $monto = round((float) ($raw ?? 0), 2);
            if ($monto > 0) {
                $sumaUsd += $monto;
            }
        }
        $sumaUsd = round($sumaUsd, 2);
        if ($sumaUsd <= 0) {
            return back()->withErrors(['abonos' => 'Indica al menos un monto a aplicar mayor que cero.'])->withInput();
        }

        $disponible = $cliente->saldoAFavorDisponibleUsd();
        if ($sumaUsd > $disponible + 0.009) {
            return back()->withErrors([
                'abonos' => 'La suma a aplicar (USD '.number_format($sumaUsd, 2).') supera el saldo a favor disponible (USD '.number_format($disponible, 2).').',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $validated, $cliente, $abonos, $sumaUsd): void {
            // 1) Aplicar pagos a facturas (rebaja saldo) y crear registros en pagos.
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

                if ($monto > (float) $factura->saldo_pendiente + 0.009) {
                    abort(422, 'El monto a aplicar supera el saldo de una factura seleccionada.');
                }

                Pago::create([
                    'factura_id' => $factura->id,
                    'fecha_recibo' => $validated['fecha_recibo'],
                    'monto_aplicado_usd' => $monto,
                    'tipo_tasa' => Pago::TIPO_TASA_BCV,
                    'valor_tasa' => 1,
                    'monto_bs' => null,
                    'metodo_pago' => Pago::METODO_SALDO_A_FAVOR,
                    'estado_validacion_banco' => null,
                    'referencia' => null,
                    'banco_destino' => null,
                    'recibido_por' => null,
                    'comprobante_path' => null,
                    'notas' => $validated['notas'] ?? null,
                    'registrado_por' => $request->user()->id,
                ]);

                $factura->refresh();
                $factura->aplicarAbonoUsd($monto);
            }

            // 2) Descontar FIFO del saldo a favor.
            $restante = $sumaUsd;
            $saldos = SaldoAFavor::query()
                ->where('cliente_id', $cliente->id)
                ->where('saldo_usd', '>', 0)
                ->orderBy('fecha_recibo')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($saldos as $s) {
                if ($restante <= 0) {
                    break;
                }
                $disp = round((float) $s->saldo_usd, 2);
                if ($disp <= 0) {
                    continue;
                }
                $usar = min($disp, $restante);
                $s->saldo_usd = round($disp - $usar, 2);
                $s->save();
                $restante = round($restante - $usar, 2);
            }

            if ($restante > 0.009) {
                abort(422, 'No se pudo descontar el saldo a favor completo (verificá saldos).');
            }
        });

        return redirect()
            ->route('cobranza.cliente', $cliente)
            ->with('status', 'Saldo a favor aplicado correctamente.');
    }
}
