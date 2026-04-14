<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Pago;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Agrupa pagos para el PDF tipo comprobante Incapor (transferencias / efectivo / pago móvil).
 */
final class MovimientosPagoInforme
{
    /**
     * @param  Collection<int, Pago>  $pagos
     * @return array{transferencias: Collection<int, Pago>, efectivo_usd: Collection<int, Pago>, pago_movil: Collection<int, Pago>}
     */
    public static function agruparPagos(Collection $pagos): array
    {
        $transferencias = $pagos->filter(fn (Pago $p) => in_array($p->metodo_pago, [
            Pago::METODO_ZELLE,
            Pago::METODO_PANAMA,
            Pago::METODO_TRANSFERENCIA,
            Pago::METODO_USDT,
        ], true))->values();

        $efectivoUsd = $pagos->filter(fn (Pago $p) => $p->metodo_pago === Pago::METODO_EFECTIVO)->values();

        $pagoMovil = $pagos->filter(fn (Pago $p) => $p->metodo_pago === Pago::METODO_PAGO_MOVIL)->values();

        return [
            'transferencias' => $transferencias,
            'efectivo_usd' => $efectivoUsd,
            'pago_movil' => $pagoMovil,
        ];
    }

    public static function formatoMontoPdf(float $monto): string
    {
        return '$'.number_format($monto, 2, ',', '.');
    }

    public static function textoPeriodoRecibos(?CarbonInterface $desde, ?CarbonInterface $hasta): string
    {
        if ($desde !== null && $hasta !== null) {
            return 'Abonos con fecha de recibo entre '.$desde->format('d/m/Y').' y '.$hasta->format('d/m/Y').'.';
        }
        if ($desde !== null) {
            return 'Abonos con fecha de recibo desde '.$desde->format('d/m/Y').'.';
        }
        if ($hasta !== null) {
            return 'Abonos con fecha de recibo hasta '.$hasta->format('d/m/Y').'.';
        }

        return '';
    }

    /**
     * @param  Collection<int, Factura>  $facturas
     * @param  Collection<int, Pago>  $pagos
     * @return array<string, mixed>
     */
    public static function datosParaVistaPdf(Cliente $cliente, Collection $facturas, Collection $pagos, string $periodoTexto = ''): array
    {
        $facturas = $facturas->values();
        $pagos = $pagos->values();

        $g = self::agruparPagos($pagos);
        $transferencias = $g['transferencias'];
        $efectivoUsd = $g['efectivo_usd'];
        $pagoMovil = $g['pago_movil'];

        $totalFacturado = round((float) $facturas->sum('total'), 2);
        $totalResta = round((float) $facturas->sum('saldo_pendiente'), 2);
        $totalAbonosUsd = round((float) $pagos->sum('monto_aplicado_usd'), 2);
        $sumaTransferenciasUsd = round((float) $transferencias->sum('monto_aplicado_usd'), 2);
        $sumaEfectivoUsd = round((float) $efectivoUsd->sum('monto_aplicado_usd'), 2);
        $sumaPagoMovilUsd = round((float) $pagoMovil->sum('monto_aplicado_usd'), 2);

        return [
            'cliente' => $cliente,
            'facturas' => $facturas,
            'pagos' => $pagos,
            'transferencias' => $transferencias,
            'efectivoUsd' => $efectivoUsd,
            'pagoMovil' => $pagoMovil,
            'totalFacturado' => $totalFacturado,
            'totalResta' => $totalResta,
            'totalAbonosUsd' => $totalAbonosUsd,
            'sumaTransferenciasUsd' => $sumaTransferenciasUsd,
            'sumaEfectivoUsd' => $sumaEfectivoUsd,
            'sumaPagoMovilUsd' => $sumaPagoMovilUsd,
            'periodoTexto' => $periodoTexto,
            'metodosPago' => Pago::metodosPago(),
            'emitidoEn' => Carbon::now(),
        ];
    }
}
