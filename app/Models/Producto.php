<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    public const UNIDAD_KG = 'kg';

    public const UNIDAD_UNIDAD = 'unidad';

    /** @var list<string> */
    public static array $unidades = [
        self::UNIDAD_UNIDAD,
        self::UNIDAD_KG,
    ];

    /**
     * Nombres estándar para maestro de productos (matadero / embutidos / líneas típicas).
     * Ampliar aquí cuando el negocio lo requiera.
     *
     * @return array<string, string> valor guardado en BD => etiqueta en formulario
     */
    public static function nombresPredeterminados(): array
    {
        return [
            'Carne de res en canal' => 'Carne de res en canal',
            'Carne de res en piezas' => 'Carne de res en piezas',
            'Carne de res — cortes' => 'Carne de res — cortes',
            'Cerdo en canal' => 'Cerdo en canal',
            'Cerdo en piezas' => 'Cerdo en piezas',
            'Pollo entero' => 'Pollo entero',
            'Pollo en piezas' => 'Pollo en piezas',
            'Molida (res o mixta)' => 'Molida (res o mixta)',
            'Menudencias' => 'Menudencias',
            'Huesos' => 'Huesos',
            'Grasa / manteca' => 'Grasa / manteca',
            'Mortadela' => 'Mortadela',
            'Jamón' => 'Jamón',
            'Salchicha' => 'Salchicha',
            'Chorizo' => 'Chorizo',
            'Queso' => 'Queso',
            'Otros embutidos' => 'Otros embutidos',
            'Producto de supermercado (genérico)' => 'Producto de supermercado (genérico)',
        ];
    }

    /** @return list<string> */
    public static function nombresPredeterminadosKeys(): array
    {
        return array_keys(self::nombresPredeterminados());
    }

    /**
     * Código interno único (P + 7 dígitos basados en el siguiente id lógico).
     * Generado solo en alta; no se edita después.
     */
    public static function generarCodigoUnico(): string
    {
        return DB::transaction(function (): string {
            $maxId = (int) static::query()->lockForUpdate()->max('id');
            $next = $maxId + 1;
            $candidate = 'P'.str_pad((string) $next, 7, '0', STR_PAD_LEFT);
            while (static::query()->where('codigo', $candidate)->exists()) {
                $next++;
                $candidate = 'P'.str_pad((string) $next, 7, '0', STR_PAD_LEFT);
            }

            return $candidate;
        });
    }

    protected $fillable = [
        'categoria_id',
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
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
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
            self::UNIDAD_UNIDAD => 'Pieza / unidad de venta (cantidad en factura)',
            self::UNIDAD_KG => 'Peso en balanza (kg)',
        ];
    }

    /** Abreviatura en documentos (factura, listados compactos). */
    public static function unidadAbreviada(): array
    {
        return [
            self::UNIDAD_UNIDAD => 'ud.',
            self::UNIDAD_KG => 'kg',
        ];
    }
}
