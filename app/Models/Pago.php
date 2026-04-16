<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    public const TIPO_TASA_BCV = 'bcv';

    public const TIPO_TASA_PARALELO = 'paralelo';

    public const METODO_ZELLE = 'zelle';

    public const METODO_PANAMA = 'panama';

    public const METODO_USDT = 'usdt';

    public const METODO_EFECTIVO = 'efectivo';

    public const METODO_PAGO_MOVIL = 'pago_movil';

    public const METODO_TRANSFERENCIA = 'transferencia';

    public const VALIDACION_BANCO_PENDIENTE = 'pendiente';

    public const VALIDACION_BANCO_VERIFICADO_MANUAL = 'verificado_manual';

    protected $fillable = [
        'factura_id',
        'fecha_recibo',
        'monto_aplicado_usd',
        'tipo_tasa',
        'valor_tasa',
        'monto_bs',
        'metodo_pago',
        'estado_validacion_banco',
        'referencia',
        'banco_destino',
        'recibido_por',
        'comprobante_path',
        'notas',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recibo' => 'date',
            'monto_aplicado_usd' => 'decimal:2',
            'valor_tasa' => 'decimal:4',
            'monto_bs' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Factura, $this>
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public static function tiposTasa(): array
    {
        return [
            self::TIPO_TASA_BCV => 'BCV',
            self::TIPO_TASA_PARALELO => 'Paralelo',
        ];
    }

    public static function metodosPago(): array
    {
        return [
            self::METODO_ZELLE => 'Zelle',
            self::METODO_PANAMA => 'Panamá',
            self::METODO_USDT => 'USDT',
            self::METODO_EFECTIVO => 'Efectivo',
            self::METODO_PAGO_MOVIL => 'Pago móvil (Bs)',
            self::METODO_TRANSFERENCIA => 'Transferencia bancaria',
        ];
    }

    /** Divisas / efectivo: referencia y captura recomendadas. */
    public static function metodosDivisas(): array
    {
        return array_intersect_key(self::metodosPago(), array_flip([
            self::METODO_ZELLE,
            self::METODO_PANAMA,
            self::METODO_USDT,
            self::METODO_EFECTIVO,
            self::METODO_TRANSFERENCIA,
        ]));
    }

    /** Bolívares (conciliación bancaria manual hasta integración). */
    public static function metodosBolivares(): array
    {
        return array_intersect_key(self::metodosPago(), array_flip([
            self::METODO_PAGO_MOVIL,
        ]));
    }

    /** Zelle, Panamá y transferencia bancaria comparten referencia / comprobante. */
    public static function metodosGrupoTransferencia(): array
    {
        return [
            self::METODO_ZELLE,
            self::METODO_PANAMA,
            self::METODO_TRANSFERENCIA,
        ];
    }

    public static function metodosGrupoEfectivo(): array
    {
        return [self::METODO_EFECTIVO];
    }

    public static function metodosGrupoPagoMovil(): array
    {
        return [self::METODO_PAGO_MOVIL];
    }

    /** USDT: mismo bloque de campos que transferencias en divisas. */
    public static function metodosGrupoDivisaDigital(): array
    {
        return [self::METODO_USDT];
    }

    /**
     * @return 'transferencia'|'efectivo'|'pago_movil'|'usdt'
     */
    public static function grupoMetodo(string $metodo): string
    {
        if (in_array($metodo, self::metodosGrupoPagoMovil(), true)) {
            return 'pago_movil';
        }
        if (in_array($metodo, self::metodosGrupoEfectivo(), true)) {
            return 'efectivo';
        }
        if (in_array($metodo, self::metodosGrupoDivisaDigital(), true)) {
            return 'usdt';
        }

        return 'transferencia';
    }
}
