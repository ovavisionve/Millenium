@php
    /** @var \App\Models\Factura $factura */
    $logoPath = public_path('images/millennium/millenium-vectores.png');
    $logoSrc = is_readable($logoPath)
        ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
        : null;
@endphp
<div class="ne-doc">
    <table class="ne-header">
        <tr>
            <td class="ne-logo-cell">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" alt="{{ config('millennium.empresa_nombre_corto') }}" />
                @else
                    <span style="font-weight: bold; font-size: 13px;">{{ config('millennium.empresa_nombre_corto') }}</span>
                @endif
            </td>
            <td>
                <p class="ne-empresa-nombre">{{ config('millennium.empresa_razon_social') }}</p>
                <p class="ne-empresa-rif"><strong>RIF:</strong> {{ config('millennium.empresa_rif') }}</p>
                <p class="ne-muted" style="margin: 0;">{{ config('millennium.empresa_nombre_corto') }} · {{ config('app.name') }}</p>
            </td>
        </tr>
    </table>

    <h1>Deuda</h1>
    <p class="ne-muted">{{ config('app.name') }} · Documento no fiscal · Factura {{ $factura->numero_factura ?? '#'.$factura->id }}</p>

    <div class="ne-pago-previsto">
        <strong>Forma de pago indicada por el cliente</strong>
        <span>{{ $factura->etiquetaMetodoPagoPrevisto() ?? '— Sin indicar —' }}</span>
    </div>

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
            <td><strong>Vendedor</strong></td>
            <td>{{ $factura->vendedor?->name ?? '—' }}</td>
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
                <th>Categoría</th>
                <th class="ne-num">Cant. animales</th>
                <th class="ne-num">unidad/Kilos</th>
                <th class="ne-num">P. unit. USD</th>
                <th class="ne-num">Subtotal USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($factura->lineas as $linea)
            <tr>
                <td>
                    <strong>{{ $linea->categoria->nombre }}</strong>
                    <div class="ne-muted">{{ $linea->categoria->codigo }}</div>
                </td>
                <td class="ne-num">{{ $linea->cantidad_animales !== null ? number_format($linea->cantidad_animales, 0, ',', '.') : '—' }}</td>
                @php
                    $unidad = $linea->categoria->unidad;
                    $decCantidad = $unidad === \App\Models\Categoria::UNIDAD_UNIDAD ? 0 : 2;
                    $udAb = \App\Models\Categoria::unidadAbreviada()[$unidad] ?? $unidad;
                @endphp
                <td class="ne-num">{{ number_format((float) $linea->cantidad, $decCantidad) }} {{ $udAb }}</td>
                <td class="ne-num">${{ number_format((float) $linea->precio_unitario, 2) }}</td>
                <td class="ne-num">${{ number_format($linea->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="ne-num"><strong>Total USD</strong></td>
                <td class="ne-num"><strong>${{ number_format($factura->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p class="ne-muted ne-small">Este documento resume la mercancía asociada a la factura indicada. Para pagos y saldos usar el estado de cuenta o la factura formal en el sistema.</p>

    @if ($factura->observaciones)
    <div class="ne-obs-footer">
        <div class="ne-obs-title">Observaciones</div>
        {{ $factura->observaciones }}
    </div>
    @endif
</div>
