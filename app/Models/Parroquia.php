<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Millennium — parroquia (catálogo) ligada a municipio.
 *
 * PK heredada del dump: `id_parroquia`.
 */
class Parroquia extends Model
{
    protected $table = 'parroquias';

    protected $primaryKey = 'id_parroquia';

    public $timestamps = false;

    protected $fillable = [
        'id_parroquia',
        'id_municipio',
        'nombre_parroquia',
    ];

    /**
     * @return BelongsTo<Municipio, $this>
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'id_municipio', 'id_municipio');
    }
}
