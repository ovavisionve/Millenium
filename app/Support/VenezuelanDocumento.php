<?php

namespace App\Support;

/**
 * Millennium — reglas de formato para documento venezolano (clientes).
 *
 * El prefijo (V, E, J, G, P) va en columna aparte; aquí solo validamos la parte numérica
 * normalizada (solo dígitos). Guiones en pantalla se ignoran al normalizar.
 */
final class VenezuelanDocumento
{
    /**
     * Deja solo dígitos (misma lógica que prepareForValidation en FormRequest).
     */
    public static function soloDigitos(string $raw): string
    {
        return preg_replace('/\D/u', '', $raw);
    }

    /**
     * @return string|null Mensaje de error en español, o null si el formato es aceptable
     */
    public static function validarFormato(string $tipo, string $digitos): ?string
    {
        $tipo = strtoupper(substr($tipo, 0, 1));

        if ($digitos === '') {
            return __('clientes.validation.documento_vacio');
        }

        if (preg_match('/^0+$/', $digitos)) {
            return __('clientes.validation.documento_solo_ceros');
        }

        if (strlen($digitos) >= 2 && preg_match('/^(\d)\1+$/', $digitos)) {
            return __('clientes.validation.documento_digitos_repetidos');
        }

        $len = strlen($digitos);

        return match ($tipo) {
            'V', 'E' => self::validarCedulaNatural($len),
            'J', 'G' => $len === 9
                ? null
                : __('clientes.validation.rif_nueve_digitos'),
            'P' => ($len >= 8 && $len <= 9)
                ? null
                : __('clientes.validation.pasaporte_ocho_nueve'),
            default => __('clientes.validation.tipo_invalido'),
        };
    }

    private static function validarCedulaNatural(int $len): ?string
    {
        if ($len >= 6 && $len <= 8) {
            return null;
        }

        return __('clientes.validation.cedula_seis_ocho');
    }
}
