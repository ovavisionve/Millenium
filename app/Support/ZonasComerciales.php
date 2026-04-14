<?php

namespace App\Support;

/**
 * Zonas / rutas comerciales fijas para clientes (alimenta reportes y filtros).
 * Personalizable con MILLENNIUM_ZONAS_COMERCIALES en .env (valores separados por coma).
 */
final class ZonasComerciales
{
    /** @var list<string> */
    private const PREDETERMINADAS = [
        'Portuguesa',
        'Amazonas',
        'Aragua',
        'Barinas',
        'Trujillo',
        'Apure',
        'Cojedes',
        'Lara',
        'Zulia',
        'Acarigua',
        'Guanare',
        'Centro',
        'Sur',
        'Portuguesa Norte',
    ];

    /**
     * @return array<string, string> valor guardado en clientes.zona => etiqueta en UI
     */
    public static function opciones(): array
    {
        $raw = config('millennium.zonas_comerciales_env');
        $partes = [];
        if (is_string($raw) && trim($raw) !== '') {
            $partes = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
        }
        if ($partes === []) {
            $partes = self::PREDETERMINADAS;
        }
        $out = [];
        foreach ($partes as $p) {
            if ($p !== '') {
                $out[$p] = $p;
            }
        }

        return $out !== [] ? $out : array_combine(self::PREDETERMINADAS, self::PREDETERMINADAS);
    }

    /** @return list<string> */
    public static function codigos(): array
    {
        return array_keys(self::opciones());
    }
}
