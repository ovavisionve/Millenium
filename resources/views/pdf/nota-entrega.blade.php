<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .ne-header { width: 100%; border-collapse: collapse; margin: 0 0 10px 0; }
        .ne-header td { vertical-align: top; padding: 2px 8px 2px 0; }
        .ne-header td.ne-logo-cell { width: 125px; }
        .ne-header img { max-width: 118px; height: auto; display: block; }
        .ne-empresa-nombre { font-size: 12px; font-weight: bold; margin: 0 0 2px 0; }
        .ne-empresa-rif { font-size: 11px; margin: 0 0 2px 0; }
        .ne-pago-previsto { margin: 8px 0 10px 0; padding: 6px 8px; background: #f5f5f5; border: 1px solid #ddd; font-size: 10px; }
        .ne-pago-previsto strong { display: block; margin-bottom: 2px; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        .ne-muted { color: #555; font-size: 9px; }
        .ne-h2 { font-size: 12px; margin: 12px 0 6px 0; }
        .ne-block { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .ne-block td { padding: 3px 6px; border-bottom: 1px solid #eee; vertical-align: top; }
        .ne-block td:first-child { width: 110px; color: #555; }
        .ne-lines { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 6px; }
        .ne-lines th, .ne-lines td { border: 1px solid #ccc; padding: 4px 5px; }
        .ne-lines th { background: #f5f5f5; text-align: left; }
        .ne-num { text-align: right; }
        .ne-obs-footer { margin-top: 12px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 10px; white-space: pre-wrap; }
        .ne-obs-footer .ne-obs-title { font-weight: bold; margin-bottom: 4px; font-size: 11px; }
        .ne-small { font-size: 8px; color: #666; margin-top: 10px; }
    </style>
</head>
<body>
    @include('facturas._nota-entrega-cuerpo', ['factura' => $factura])
</body>
</html>
