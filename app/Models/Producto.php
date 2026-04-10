<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    public const UNIDAD_KG = 'kg';

    public const UNIDAD_UNIDAD = 'unidad';

    /** @var list<string> */
    public static array $unidades = [
        self::UNIDAD_KG,
        self::UNIDAD_UNIDAD,
    ];

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
            self::UNIDAD_KG => 'Kilogramos (kg)',
            self::UNIDAD_UNIDAD => 'Unidad',
        ];
    }
}
