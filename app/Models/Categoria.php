<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Categoria extends Model
{
    public const UNIDAD_KG = 'kg';

    public const UNIDAD_UNIDAD = 'unidad';

    /** @var list<string> */
    public static array $unidades = [
        self::UNIDAD_UNIDAD,
        self::UNIDAD_KG,
    ];

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'unidad',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Código interno único (C + 5 dígitos) para nuevas categorías desde el sistema.
     */
    public static function generarCodigoUnico(): string
    {
        return DB::transaction(function (): string {
            $maxId = (int) static::query()->lockForUpdate()->max('id');
            $next = $maxId + 1;
            $candidate = 'C'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            while (static::query()->where('codigo', $candidate)->exists()) {
                $next++;
                $candidate = 'C'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            }

            return $candidate;
        });
    }

    /**
     * @return HasMany<FacturaLinea, $this>
     */
    public function facturaLineas(): HasMany
    {
        return $this->hasMany(FacturaLinea::class);
    }

    public static function unidadLabels(): array
    {
        return [
            self::UNIDAD_UNIDAD => 'Pieza / unidad de venta (campo unidad/Kilos en factura)',
            self::UNIDAD_KG => 'Peso en balanza (kg)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function unidadAbreviada(): array
    {
        return [
            self::UNIDAD_UNIDAD => 'ud.',
            self::UNIDAD_KG => 'kg',
        ];
    }
}
