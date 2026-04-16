<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deuda {{ $factura->numero_factura ?? $factura->id }} — {{ config('app.name') }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; color: #321D17; margin: 0; padding: 1.25rem; background: #faf8f5; }
        .ne-toolbar { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e7ddd4; }
        .ne-toolbar a, .ne-toolbar button {
            display: inline-block; padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; text-decoration: none;
            border: 1px solid #321D17; background: #fff; color: #321D17; cursor: pointer;
        }
        .ne-toolbar a.primary { background: #321D17; color: #fff; border-color: #321D17; }
        .ne-doc { max-width: 720px; margin: 0 auto; background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(50,29,23,0.08); }
        .ne-header { width: 100%; border-collapse: collapse; margin: 0 0 1rem 0; }
        .ne-header td { vertical-align: top; padding: 0.25rem 0.75rem 0.25rem 0; }
        .ne-header td.ne-logo-cell { width: 130px; }
        .ne-header img { max-width: 120px; height: auto; display: block; }
        .ne-empresa-nombre { font-size: 1rem; font-weight: 700; margin: 0 0 0.25rem 0; color: #321D17; }
        .ne-empresa-rif { font-size: 0.9rem; margin: 0 0 0.25rem 0; }
        .ne-pago-previsto { margin: 0.75rem 0 1rem 0; padding: 0.5rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.875rem; }
        .ne-pago-previsto strong { display: block; margin-bottom: 0.25rem; color: #321D17; }
        .ne-obs-footer { margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0; font-size: 0.875rem; white-space: pre-wrap; color: #321D17; }
        .ne-obs-footer .ne-obs-title { font-weight: 700; margin-bottom: 0.35rem; font-size: 0.95rem; }
        .ne-doc h1 { font-size: 1.35rem; margin: 0 0 0.25rem 0; }
        .ne-h2 { font-size: 1.05rem; margin: 1.25rem 0 0.5rem 0; }
        .ne-muted { color: #64748b; font-size: 0.875rem; }
        .ne-small { font-size: 0.8rem; margin-top: 1.25rem; }
        .ne-block { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem; }
        .ne-block td { padding: 0.35rem 0.5rem; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .ne-block td:first-child { width: 9rem; color: #64748b; }
        .ne-lines { width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-top: 0.5rem; }
        .ne-lines th, .ne-lines td { border: 1px solid #e2e8f0; padding: 0.45rem 0.5rem; }
        .ne-lines th { background: #f8fafc; text-align: left; }
        .ne-num { text-align: right; }
        @media print {
            body { background: #fff; padding: 0; }
            .ne-toolbar { display: none; }
            .ne-doc { box-shadow: none; border-radius: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="ne-toolbar">
        <a href="{{ route('facturas.show', $factura) }}">← Volver a la factura</a>
        <button type="button" class="primary" onclick="window.print()">Imprimir</button>
        <a class="primary" href="{{ route('facturas.nota-entrega.pdf', $factura) }}">Descargar PDF deuda</a>
    </div>
    @include('facturas._nota-entrega-cuerpo', ['factura' => $factura])
</body>
</html>
