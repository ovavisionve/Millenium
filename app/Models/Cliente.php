<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    public const TIPOS_DOCUMENTO = ['V', 'E', 'J', 'G', 'P'];

    protected $fillable = [
        'tipo_documento',
        'documento_numero',
        'nombre_razon_social',
        'email',
        'direccion',
        'telefono',
        'id_estado',
        'id_ciudad',
        'id_municipio',
        'id_parroquia',
        'zona',
        'vendedor_id',
    ];

    /**
     * @return array<string, string>
     */
    public static function tiposDocumentoLabels(): array
    {
        return [
            'V' => 'V — Venezolano',
            'E' => 'E — Extranjero',
            'J' => 'J — Jurídico',
            'G' => 'G — Gubernamental',
            'P' => 'P — Pasaporte',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    /**
     * @return BelongsTo<Municipio, $this>
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'id_municipio', 'id_municipio');
    }

    /**
     * @return BelongsTo<Estado, $this>
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    /**
     * @return BelongsTo<Ciudad, $this>
     */
    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    /**
     * @return BelongsTo<Parroquia, $this>
     */
    public function parroquia(): BelongsTo
    {
        return $this->belongsTo(Parroquia::class, 'id_parroquia', 'id_parroquia');
    }

    /**
     * @return HasMany<Factura, $this>
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    /**
     * @return HasMany<SaldoAFavor, $this>
     */
    public function saldosAFavor(): HasMany
    {
        return $this->hasMany(SaldoAFavor::class);
    }

    /** Saldo a favor disponible en USD (sumatoria). */
    public function saldoAFavorDisponibleUsd(): float
    {
        $v = (float) $this->saldosAFavor()
            ->where('saldo_usd', '>', 0)
            ->sum('saldo_usd');

        return round($v, 2);
    }

    public function getFullIdentificacionAttribute(): string
    {
        return strtoupper((string) $this->tipo_documento).'-'.trim((string) $this->documento_numero);
    }

    /**
     * Búsqueda por nombre, zona, número o identificación completa (tipo-número).
     *
     * @param  Builder<Cliente>  $query
     */
    public function scopeWhereBuscarTexto(Builder $query, string $s): void
    {
        $driver = $query->getConnection()->getDriverName();
        $concat = $driver === 'sqlite'
            ? "(tipo_documento || '-' || documento_numero)"
            : "CONCAT(tipo_documento, '-', documento_numero)";

        $query->where(function (Builder $q) use ($s, $concat): void {
            $q->where('nombre_razon_social', 'like', '%'.$s.'%')
                ->orWhere('documento_numero', 'like', '%'.$s.'%')
                ->orWhere('email', 'like', '%'.$s.'%')
                ->orWhere('direccion', 'like', '%'.$s.'%')
                ->orWhere('zona', 'like', '%'.$s.'%')
                ->orWhereHas('estado', fn (Builder $eq) => $eq->where('nombre_estado', 'like', '%'.$s.'%'))
                ->orWhereHas('ciudad', fn (Builder $cq) => $cq->where('nombre_ciudad', 'like', '%'.$s.'%'))
                ->orWhereHas('municipio', fn (Builder $mq) => $mq->where('nombre_municipio', 'like', '%'.$s.'%'))
                ->orWhereHas('parroquia', fn (Builder $pq) => $pq->where('nombre_parroquia', 'like', '%'.$s.'%'))
                ->orWhereRaw("{$concat} LIKE ?", ['%'.$s.'%']);
        });
    }
}
