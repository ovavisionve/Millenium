<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .hdr { text-align: center; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 6px; }
        .hdr strong { font-size: 13px; }
        .rif { font-size: 9px; }
        .cliente { border: 1px solid #333; padding: 4px 8px; margin: 8px 0; font-weight: bold; }
        h2 { font-size: 11px; margin: 10px 0 4px 0; border-bottom: 1px solid #999; }
        table.t { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.t th, table.t td { border: 1px solid #444; padding: 3px 5px; }
        table.t th { background: #eee; text-align: left; }
        .num { text-align: right; }
        .tot { font-weight: bold; background: #f5f5f5; }
        .pie { margin-top: 12px; border: 2px solid #333; padding: 6px; }
        .pie table { width: 100%; border-collapse: collapse; }
        .pie td { padding: 2px 4px; }
        .muted { font-size: 8px; color: #555; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="hdr">
        <strong>{{ config('millennium.empresa_nombre_corto') }}</strong>
        <div class="rif">{{ config('millennium.empresa_rif') }}</div>
        <div>{{ config('millennium.empresa_razon_social') }}</div>
        <div style="margin-top:4px;font-size:10px;"><strong>Comprobante de movimientos de pago</strong></div>
        @if (!empty($periodoTexto))
        <div class="muted" style="margin-top:2px;">{{ $periodoTexto }}</div>
        @endif
    </div>

    <div class="cliente">CLIENTE: {{ strtoupper($cliente->nombre_razon_social) }}</div>

    @if ($pagos->isEmpty())
    <p style="border:1px solid #999;padding:6px;margin-bottom:8px;">No hay abonos en el período seleccionado. El bloque inferior muestra igualmente los saldos pendientes actuales de los documentos listados.</p>
    @endif

    <h2>Documentos / facturas</h2>
    <table class="t">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Fact.</th>
                <th class="num">Monto (total USD)</th>
                <th class="num">Saldo pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturas as $f)
            <tr>
                <td>{{ $f->fecha_emision->format('d-m-Y') }}</td>
                <td>{{ $f->numero_factura ?? '#'.$f->id }}</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf((float) $f->total) }}</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf((float) $f->saldo_pendiente) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td colspan="2">TOTAL documentos</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($totalFacturado) }}</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($totalResta) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($transferencias->isNotEmpty())
    <h2>Transferencias / Zelle / USDT / Panamá</h2>
    <table class="t">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Banco / método</th>
                <th>Ref.</th>
                <th class="num">Monto Bs</th>
                <th class="num">Tasa</th>
                <th class="num">Total USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transferencias as $p)
            <tr>
                <td>{{ $p->fecha_recibo->format('d-m-Y') }}</td>
                <td>{{ $metodosPago[$p->metodo_pago] ?? $p->metodo_pago }}@if($p->banco_destino)<br><span style="font-size:8px;">{{ $p->banco_destino }}</span>@endif</td>
                <td>{{ $p->referencia ?? '—' }}</td>
                <td class="num">@if($p->monto_bs){{ number_format((float) $p->monto_bs, 2, ',', '.') }}@else — @endif</td>
                <td class="num">@if($p->monto_bs){{ number_format((float) $p->valor_tasa, 2, ',', '.') }}@else — @endif</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf((float) $p->monto_aplicado_usd) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td colspan="5">TOTAL transferencias (USD aplicado)</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($sumaTransferenciasUsd) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if ($efectivoUsd->isNotEmpty())
    <h2>Pago en dólares (efectivo)</h2>
    <table class="t">
        <thead>
            <tr>
                <th>Fecha recibida</th>
                <th class="num">Total recibido USD</th>
                <th>Fact.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($efectivoUsd as $p)
            <tr>
                <td>{{ $p->fecha_recibo->format('d-m-Y') }}</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf((float) $p->monto_aplicado_usd) }}</td>
                <td>{{ $p->factura->numero_factura ?? '#'.$p->factura_id }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td>TOTAL</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($sumaEfectivoUsd) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    @if ($pagoMovil->isNotEmpty())
    <h2>Pago móvil (Bs)</h2>
    <table class="t">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ref.</th>
                <th class="num">Monto Bs</th>
                <th class="num">Tasa</th>
                <th class="num">Total USD</th>
                <th>Fact.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pagoMovil as $p)
            <tr>
                <td>{{ $p->fecha_recibo->format('d-m-Y') }}</td>
                <td>{{ $p->referencia ?? '—' }}</td>
                <td class="num">@if($p->monto_bs){{ number_format((float) $p->monto_bs, 2, ',', '.') }}@else — @endif</td>
                <td class="num">{{ number_format((float) $p->valor_tasa, 2, ',', '.') }}</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf((float) $p->monto_aplicado_usd) }}</td>
                <td>{{ $p->factura->numero_factura ?? '#'.$p->factura_id }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td colspan="4">TOTAL pago móvil (USD aplicado)</td>
                <td class="num">{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($sumaPagoMovilUsd) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="pie">
        <table>
            <tr>
                <td><strong>TOTAL abonos (USD en este comprobante)</strong></td>
                <td class="num" style="font-size:12px;"><strong>{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($totalAbonosUsd) }}</strong></td>
            </tr>
            <tr>
                <td>SOBRA (si hubiera diferencia a favor del cliente)</td>
                <td class="num">—</td>
            </tr>
            <tr>
                <td><strong>RESTA (suma saldos pendientes de los documentos arriba)</strong></td>
                <td class="num" style="font-size:12px;"><strong>{{ \App\Support\MovimientosPagoInforme::formatoMontoPdf($totalResta) }}</strong></td>
            </tr>
        </table>
    </div>

    <p class="muted">Generado {{ $emitidoEn->format('d/m/Y H:i') }} · Los montos en USD aplicados corresponden a los abonos registrados en el sistema.</p>
</body>
</html>
