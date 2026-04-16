<?php

/**
 * Millennium — parámetros operativos (entrega / producción).
 *
 * MILLENNIUM_FACTURA_NUMERO_INICIAL: primer número correlativo si aún no hay facturas
 * con número numérico en BD; al superar el máximo existente, sigue max+1.
 */
return [
    /** Textos para PDFs operativos (documento de deuda, movimientos de pago, etc.). */
    'empresa_nombre_corto' => env('MILLENNIUM_EMPRESA_NOMBRE_CORTO', 'INCAPOR'),
    'empresa_razon_social' => env('MILLENNIUM_EMPRESA_RAZON', 'INDUSTRIAS CARNICAS PORTUGUESA, C.A.'),
    'empresa_rif' => env('MILLENNIUM_EMPRESA_RIF', 'J-40101298-1'),

    'factura_numero_inicial' => max(1, (int) env('MILLENNIUM_FACTURA_NUMERO_INICIAL', 1)),

    /**
     * Tasa Bs/USD guardada en pagos en divisas/efectivo cuando no aplica conversión al momento del abono.
     * Los reportes pueden filtrar por método; no sustituye la tasa real en pago móvil.
     */
    'cobranza_tasa_placeholder_divisa' => (float) env('MILLENNIUM_TASA_PLACEHOLDER_DIVISA', 1),

    /**
     * Valor inicial del filtro "Zona (contiene)" en /reportes cuando no hay ?zona= en la URL.
     * Si el usuario envía el formulario con el campo vacío, no se aplica filtro por zona.
     */
    'reporte_zona_default' => env('MILLENNIUM_REPORTE_ZONA_DEFAULT', ''),

    /**
     * Zonas comerciales / rutas fijas (cliente.zona). Lista separada por comas; si está vacío se usan
     * valores por defecto en App\Support\ZonasComerciales.
     */
    'zonas_comerciales_env' => env('MILLENNIUM_ZONAS_COMERCIALES', ''),

    /**
     * Si está definida (solo local), LocalLoginCredentialsSeeder usa la misma contraseña para todos los correos listados.
     */
    'local_demo_password' => env('MILLENNIUM_LOCAL_DEMO_PASSWORD'),
];
