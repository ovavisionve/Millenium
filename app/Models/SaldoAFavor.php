<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoAFavor extends Model
{
    protected $table = 'saldos_a_favor';

    protected $fillable = [
        'cliente_id',
        'origen_pago_id',
        'fecha_recibo',
        'monto_usd',
        'saldo_usd',
        'notas',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recibo' => 'date',
            'monto_usd' => 'decimal:2',
            'saldo_usd' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsTo<Pago, $this>
     */
    public function origenPago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'origen_pago_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
