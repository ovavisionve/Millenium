<?php

namespace App\Support;

/**
 * Millennium — móvil Venezuela: 11 dígitos, prefijo 04 (misma regla en front, FormRequest y mensajes).
 */
final class VenezuelanTelefonoMovil
{
    public const LONGITUD = 11;

    public static function soloDigitos(string $raw): string
    {
        return preg_replace('/\D/', '', $raw);
    }

    /** null = válido (vacío u opcional); string = mensaje de error traducido */
    public static function validarOpcional(?string $soloDigitos): ?string
    {
        if ($soloDigitos === null || $soloDigitos === '') {
            return null;
        }
        if (strlen($soloDigitos) !== self::LONGITUD || ! preg_match('/^04\d{9}$/', $soloDigitos)) {
            return __('clientes.validation.telefono_movil');
        }

        return null;
    }
}
