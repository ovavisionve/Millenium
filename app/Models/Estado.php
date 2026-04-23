<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Millennium — estado (Venezuela).
 *
 * PK heredada del dump: `id_estado`.
 */
class Estado extends Model
{
    protected $table = 'estados';

    protected $primaryKey = 'id_estado';

    public $timestamps = false;

    protected $fillable = [
        'id_estado',
        'nombre_estado',
        'codigo_iso_3166_2',
    ];

    /**
     * @return HasMany<Ciudad, $this>
     */
    public function ciudades(): HasMany
    {
        return $this->hasMany(Ciudad::class, 'id_estado', 'id_estado');
    }

    /**
     * @return HasMany<Municipio, $this>
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'id_estado', 'id_estado');
    }
}
