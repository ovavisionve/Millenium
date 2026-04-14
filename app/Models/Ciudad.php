<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Millennium — ciudad (Venezuela).
 *
 * PK heredada del dump: `id_ciudad`.
 */
class Ciudad extends Model
{
    protected $table = 'ciudades';

    protected $primaryKey = 'id_ciudad';

    public $timestamps = false;

    protected $fillable = [
        'id_ciudad',
        'id_estado',
        'nombre_ciudad',
        'es_capital',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_capital' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Estado, $this>
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }
}
