<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaLinea extends Model
{
    protected $fillable = [
        'factura_id',
        'categoria_id',
        'cantidad_animales',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_animales' => 'integer',
            'cantidad' => 'decimal:3',
            'precio_unitario' => 'decimal:4',
            'subtotal' => 'decimal:2',
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
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
