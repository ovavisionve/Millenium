@php
    /** @var \App\Models\Factura $factura */
@endphp
<div class="ne-doc">
    <h1>Nota de entrega</h1>
    <p class="ne-muted">{{ config('app.name') }} · Documento no fiscal · Referencia factura {{ $factura->numero_factura ?? '#'.$factura->id }}</p>

    <table class="ne-block">
        <tr>
            <td><strong>Cliente</strong></td>
            <td>{{ $factura->cliente->nombre_razon_social }}</td>
        </tr>
        <tr>
            <td><strong>Identificación</strong></td>
            <td>{{ $factura->cliente->full_identificacion }}</td>
        </tr>
        <tr>
            <td><strong>Zona / ruta</strong></td>
            <td>{{ $factura->cliente->zona ?: '—' }}</td>
        </tr>
        <tr>
            <td><strong>Fecha</strong></td>
            <td>{{ $factura->fecha_emision->format('d/m/Y') }}</td>
        </tr>
    </table>

    <h2 class="ne-h2">Mercancía entregada</h2>
    <table class="ne-lines">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="ne-num">Cantidad</th>
                <th class="ne-num">P. unit. USD</th>
                <th class="ne-num">Subtotal USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($factura->lineas as $linea)
            <tr>
                <td>
                    <strong>{{ $linea->producto->nombre }}</strong>
                    <div class="ne-muted">{{ $linea->producto->categoria->nombre }} · {{ $linea->producto->codigo }}</div>
                </td>
                <td class="ne-num">{{ number_format($linea->cantidad, 3) }} {{ \App\Models\Producto::unidadAbreviada()[$linea->producto->unidad] ?? $linea->producto->unidad }}</td>
                <td class="ne-num">${{ number_format($linea->precio_unitario, 4) }}</td>
                <td class="ne-num">${{ number_format($linea->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="ne-num"><strong>Total USD</strong></td>
                <td class="ne-num"><strong>${{ number_format($factura->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p class="ne-muted ne-small">Este documento resume la mercancía asociada a la factura indicada. Para pagos y saldos usar el estado de cuenta o la factura formal en el sistema.</p>
</div>
