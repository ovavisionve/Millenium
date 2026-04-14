<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Factura extends Model
{
    /** Días antes del vencimiento para marcar "por vencer" (Paso 2 Vic). */
    public const DIAS_UMBRAL_POR_VENCER = 3;

    public const ESTADO_PAGO_ABIERTA = 'abierta';

    public const ESTADO_PAGO_PAGADA = 'pagada';

    public const CARTERA_VENCIDA = 'vencida';

    public const CARTERA_POR_VENCER = 'por_vencer';

    public const CARTERA_AL_DIA = 'al_dia';

    protected $fillable = [
        'cliente_id',
        'numero_factura',
        'fecha_emision',
        'dias_credito',
        'fecha_vencimiento',
        'total',
        'saldo_pendiente',
        'estado_pago',
        'creado_por',
        'verificado_por',
        'fecha_verificacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_verificacion' => 'datetime',
            'total' => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    /**
     * @return HasMany<FacturaLinea, $this>
     */
    public function lineas(): HasMany
    {
        return $this->hasMany(FacturaLinea::class);
    }

    /**
     * @return HasMany<Pago, $this>
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class)->orderByDesc('fecha_recibo')->orderByDesc('id');
    }

    public function estadoCartera(?Carbon $referencia = null): string
    {
        $hoy = ($referencia ?? Carbon::today())->startOfDay();
        $venc = $this->fecha_vencimiento->copy()->startOfDay();

        if ($hoy->gt($venc)) {
            return self::CARTERA_VENCIDA;
        }

        $limitePorVencer = $hoy->copy()->addDays(self::DIAS_UMBRAL_POR_VENCER);
        if ($venc->lte($limitePorVencer)) {
            return self::CARTERA_POR_VENCER;
        }

        return self::CARTERA_AL_DIA;
    }

    public static function etiquetasCartera(): array
    {
        return [
            self::CARTERA_VENCIDA => 'Vencida',
            self::CARTERA_POR_VENCER => 'Por vencer',
            self::CARTERA_AL_DIA => 'Al día',
        ];
    }

    public function estaVerificada(): bool
    {
        return $this->verificado_por !== null;
    }

    public function puedeVerificar(User $user): bool
    {
        if ($this->estaVerificada()) {
            return false;
        }

        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_VERIFICADOR, User::ROLE_VENDEDOR], true) && $user->is_active;
    }

    /** Texto para listados: quién verificó precios (Fatimar) y cuándo. */
    public function textoVerificacionFatimar(): ?string
    {
        if (! $this->estaVerificada()) {
            return null;
        }

        $nombre = $this->verificadoPor?->name ?? 'Usuario';
        $fecha = $this->fecha_verificacion?->format('d/m/Y H:i');

        return $fecha
            ? 'Fatimar: '.$nombre.' verificó precios · '.$fecha
            : 'Fatimar: '.$nombre.' verificó precios';
    }

    /** Descuenta saldo en USD y marca pagada si corresponde. */
    public function aplicarAbonoUsd(float $montoUsd): void
    {
        $montoUsd = round($montoUsd, 2);
        $nuevoSaldo = round(max(0, (float) $this->saldo_pendiente - $montoUsd), 2);
        $this->saldo_pendiente = $nuevoSaldo;
        if ($nuevoSaldo <= 0) {
            $this->saldo_pendiente = 0;
            $this->estado_pago = self::ESTADO_PAGO_PAGADA;
        }
        $this->save();
    }

    /**
     * Mayor número de factura que es entero en texto puro (ej. "101011"). Ignora formatos tipo "B-8674".
     */
    public static function maxNumeroFacturaNumerico(): int
    {
        $max = 0;
        foreach (static::query()->whereNotNull('numero_factura')->pluck('numero_factura') as $n) {
            $s = trim((string) $n);
            if ($s !== '' && ctype_digit($s)) {
                $max = max($max, (int) $s);
            }
        }

        return $max;
    }

    /** Vista previa en formulario de alta (no reserva el número). */
    public static function vistaPreviaSiguienteNumero(): string
    {
        $inicial = max(1, (int) config('millennium.factura_numero_inicial', 1));
        $maxDb = self::maxNumeroFacturaNumerico();

        return (string) max($inicial, $maxDb + 1);
    }

    /**
     * Correlativo único para nueva factura. Ajustá el piso con MILLENNIUM_FACTURA_NUMERO_INICIAL en .env.
     */
    public static function generarNumeroFactura(): string
    {
        $inicial = max(1, (int) config('millennium.factura_numero_inicial', 1));

        return DB::transaction(function () use ($inicial): string {
            $maxDb = self::maxNumeroFacturaNumerico();
            $next = max($inicial, $maxDb + 1);
            $candidate = (string) $next;
            while (static::query()->where('numero_factura', $candidate)->exists()) {
                $next++;
                $candidate = (string) $next;
            }

            return $candidate;
        });
    }
}
