<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
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
        .ne-small { font-size: 8px; color: #666; margin-top: 10px; }
    </style>
</head>
<body>
    @include('facturas._nota-entrega-cuerpo', ['factura' => $factura])
</body>
</html>
