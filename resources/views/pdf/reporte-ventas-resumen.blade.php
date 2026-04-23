<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; margin: 0; padding: 12px 14px 12px 14px; }
        .hdr { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .hdr td { vertical-align: middle; }
        .hdr .logo-main img { max-height: 42px; width: auto; display: block; }
        .hdr h1 { font-size: 15px; margin: 0 0 4px 0; color: #3d2b1f; }
        .hdr .sub { font-size: 8px; color: #555; margin: 0; }
        .box-filtros { border: 1px solid #ccc; background: #fafafa; padding: 6px 8px; margin: 0 0 10px 0; font-size: 8px; }
        .box-filtros ul { margin: 4px 0 0 16px; padding: 0; }
        .kpis { width: 100%; border-collapse: collapse; margin: 0 0 10px 0; }
        .kpis td { border: 1px solid #ddd; padding: 6px 8px; background: #f7f7f7; width: 33%; }
        .kpis .lab { font-size: 8px; color: #555; }
        .kpis .val { font-size: 12px; font-weight: bold; }
        h2 { font-size: 11px; margin: 12px 0 6px 0; color: #3d2b1f; border-bottom: 1px solid #ddd; padding-bottom: 2px; }
        table.tbl { width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 8px; }
        table.tbl th, table.tbl td { border: 1px solid #ccc; padding: 3px 4px; vertical-align: top; }
        table.tbl th { background: #eee; text-align: left; font-weight: bold; }
        .num { text-align: right; white-space: nowrap; }
        .muted { color: #666; font-size: 7px; }
        .foot { margin-top: 14px; background: #1a1a1a; color: #fff; }
        .foot-inner { padding: 8px 10px; font-size: 7px; }
        .foot-inner table { width: 100%; border-collapse: collapse; }
        .foot-inner td { vertical-align: middle; }
        .foot .logo-foot img { max-height: 22px; width: auto; display: block; }
        .note { font-size: 7px; color: #555; margin-top: 6px; }
    </style>
</head>
<body>
    <table class="hdr">
        <tr>
            <td class="logo-main" style="width: 140px;">
                @if (!empty($logoColor))
                    <img src="{{ $logoColor }}" alt="Millennium" />
                @endif
            </td>
            <td>
                <h1>Reporte de ventas y líneas (resumen ejecutivo)</h1>
                <p class="sub">Generado: {{ $generadoEn->format('d/m/Y H:i') }} · {{ $nFacturas }} facturas · {{ $nLineas }} líneas en este corte</p>
            </td>
        </tr>
    </table>

    <div class="box-filtros">
        <strong>Filtros aplicados</strong>
        <ul>
            @foreach ($filtros as $f)
                <li>{{ $f }}</li>
            @endforeach
        </ul>
    </div>

    <table class="kpis">
        <tr>
            <td>
                <div class="lab">Total facturado (suma subtotales de líneas en filtro)</div>
                <div class="val">${{ number_format($totales['subtotal'], 2) }}</div>
            </td>
            <td>
                <div class="lab">Kg (líneas en categoría kg)</div>
                <div class="val">{{ number_format($totales['kg'], 2) }} kg</div>
            </td>
            <td>
                <div class="lab">Formato</div>
                <div class="val" style="font-size:10px;">Resumen por factura + detalle por línea</div>
            </td>
        </tr>
    </table>

    @if ($resumenPorVendedor->isNotEmpty())
        <h2>Resumen por vendedor (USD)</h2>
        <table class="tbl">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th class="num">Total USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resumenPorVendedor as $r)
                    <tr>
                        <td>{{ $r['nombre'] }}</td>
                        <td class="num">${{ number_format($r['total_usd'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($resumenPorZona->isNotEmpty())
        <h2>Resumen por zona (USD)</h2>
        <table class="tbl">
            <thead>
                <tr>
                    <th>Zona (cliente)</th>
                    <th class="num">Total USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resumenPorZona as $r)
                    <tr>
                        <td>{{ $r['zona'] }}</td>
                        <td class="num">${{ number_format($r['total_usd'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Resumen por factura (una fila por documento)</h2>
    <p class="muted" style="margin:0 0 6px 0;">“Subtotal (filtro)” suma solo las líneas que entran en el corte actual (si filtrás por categoría, puede ser menor al total del documento).</p>
    <table class="tbl">
        <thead>
            <tr>
                <th>Factura</th>
                <th>Emisión</th>
                <th>Cliente</th>
                <th>Zona</th>
                <th>Estado</th>
                <th>Vendedor</th>
                <th class="num">Total doc.</th>
                <th class="num">Saldo</th>
                <th>Pago</th>
                <th>Verif.</th>
                <th class="num">#Lín.</th>
                <th class="num">Subtotal (filtro)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturasResumen as $row)
                <tr>
                    <td>{{ $row['numero'] }}</td>
                    <td>{{ $row['fecha'] }}</td>
                    <td>{{ $row['cliente'] }}</td>
                    <td>{{ $row['zona'] }}</td>
                    <td>{{ $row['estado_cliente'] }}</td>
                    <td>{{ $row['vendedor'] }}</td>
                    <td class="num">${{ number_format($row['total_factura'], 2) }}</td>
                    <td class="num">${{ number_format($row['saldo'], 2) }}</td>
                    <td>{{ $row['estado_pago'] }}</td>
                    <td>{{ $row['verif'] }}</td>
                    <td class="num">{{ $row['n_lineas'] }}</td>
                    <td class="num">${{ number_format($row['subtotal_filtro'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Detalle por línea (compacto)</h2>
    <p class="muted" style="margin:0 0 6px 0;">Cada fila es una línea de factura; el número de factura se repite solo como referencia (no es un duplicado del documento).</p>
    <table class="tbl">
        <thead>
            <tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Zona</th>
                <th>Estado</th>
                <th>Vendedor</th>
                <th>Categoría</th>
                <th class="num">Cant.</th>
                <th class="num">Ud/kg</th>
                <th class="num">P. unit.</th>
                <th class="num">Subtotal</th>
                <th>Verif.</th>
            </tr>
        </thead>
        <tbody>
            @php $uAb = \App\Models\Categoria::unidadAbreviada(); @endphp
            @foreach ($lineasDetalle as $linea)
                @php
                    $f = $linea->factura;
                    $c = $linea->categoria;
                    $ud = $uAb[$c->unidad] ?? $c->unidad;
                    $decCantidad = $c->unidad === \App\Models\Categoria::UNIDAD_UNIDAD ? 0 : 2;
                @endphp
                <tr>
                    <td>{{ $f->numero_factura ?? '#'.$f->id }}</td>
                    <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                    <td>{{ $f->cliente?->nombre_razon_social ?? '—' }}</td>
                    <td>{{ $f->cliente?->zona ?: '—' }}</td>
                    <td>{{ $f->cliente?->estado?->nombre_estado ?? '—' }}</td>
                    <td>{{ $f->vendedor?->name ?? '—' }}</td>
                    <td>{{ $c->nombre }} <span class="muted">({{ $c->codigo }})</span></td>
                    <td class="num">{{ $linea->cantidad_animales !== null ? number_format($linea->cantidad_animales, 0, ',', '.') : '—' }}</td>
                    <td class="num">{{ number_format((float) $linea->cantidad, $decCantidad) }} {{ $ud }}</td>
                    <td class="num">${{ number_format((float) $linea->precio_unitario, 2) }}</td>
                    <td class="num">${{ number_format((float) $linea->subtotal, 2) }}</td>
                    <td>{{ $f->estaVerificada() ? 'Sí' : 'Pend.' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">Millennium / Incapor — documento interno. Para enviar al cliente un estado de cuenta formal usá el PDF del módulo Estados de cuenta (por zona).</p>

    <div class="foot">
        <div class="foot-inner">
            <table>
                <tr>
                    <td style="width:120px;">
                        @if (!empty($logoBlanco))
                            <div class="logo-foot"><img src="{{ $logoBlanco }}" alt="Millennium" /></div>
                        @endif
                    </td>
                    <td class="num" style="font-size:7px;color:#ccc;">
                        {{ $generadoEn->format('d/m/Y H:i') }} · Confidential
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
