<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Millennium — municipio (catálogo) para zonas predeterminadas.
 *
 * PK heredada del dump: `id_municipio`.
 */
class Municipio extends Model
{
    protected $table = 'municipios';

    protected $primaryKey = 'id_municipio';

    public $timestamps = false;

    protected $fillable = [
        'id_municipio',
        'id_estado',
        'nombre_municipio',
    ];

    /**
     * @return BelongsTo<Estado, $this>
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    /**
     * @return HasMany<Parroquia, $this>
     */
    public function parroquias(): HasMany
    {
        return $this->hasMany(Parroquia::class, 'id_municipio', 'id_municipio');
    }
}
