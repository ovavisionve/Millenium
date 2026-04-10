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
        'telefono',
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
     * @return HasMany<Factura, $this>
     */
    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
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
                ->orWhere('zona', 'like', '%'.$s.'%')
                ->orWhereRaw("{$concat} LIKE ?", ['%'.$s.'%']);
        });
    }
}
