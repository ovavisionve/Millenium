<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 15px; margin: 0 0 2px 0; }
        .sub { color: #555; font-size: 9px; margin-bottom: 10px; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.meta td { padding: 3px 0; vertical-align: top; }
        table.meta td.lbl { width: 100px; color: #555; }
        table.inv { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.inv th, table.inv td { border: 1px solid #ccc; padding: 4px 5px; }
        table.inv th { background: #f5f5f5; text-align: left; }
        .num { text-align: right; }
        .total { margin-top: 10px; font-size: 12px; font-weight: bold; text-align: right; }
        .foot { margin-top: 14px; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <h1>Estado de cuenta — saldo pendiente</h1>
    <p class="sub">{{ config('app.name') }} · Emitido {{ $emitidoEn->format('d/m/Y H:i') }}</p>

    <table class="meta">
        <tr><td class="lbl">Cliente</td><td><strong>{{ $cliente->nombre_razon_social }}</strong></td></tr>
        <tr><td class="lbl">Identificación</td><td>{{ $cliente->full_identificacion }}</td></tr>
        <tr><td class="lbl">Zona</td><td>{{ $cliente->zona ?: '—' }}</td></tr>
        @if ($cliente->vendedor)
        <tr><td class="lbl">Vendedor</td><td>{{ $cliente->vendedor->name }}</td></tr>
        @endif
    </table>

    <table class="inv">
        <thead>
            <tr>
                <th>Factura</th>
                <th>Emisión</th>
                <th>Vence</th>
                <th>Total USD</th>
                <th>Saldo USD</th>
                <th>Cartera</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturas as $f)
            @php $ec = $f->estadoCartera(); @endphp
            <tr>
                <td>{{ $f->numero_factura ?? '#'.$f->id }}</td>
                <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                <td>{{ $f->fecha_vencimiento->format('d/m/Y') }}</td>
                <td class="num">${{ number_format($f->total, 2) }}</td>
                <td class="num">${{ number_format($f->saldo_pendiente, 2) }}</td>
                <td>{{ $etiquetasCartera[$ec] ?? $ec }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total saldo pendiente: ${{ number_format($totalSaldo, 2) }} USD</p>

    <p class="foot">Los saldos se actualizan automáticamente al registrar cobros en el sistema. Documento informativo para el cliente.</p>
</body>
</html>
