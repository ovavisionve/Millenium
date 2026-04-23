<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_VERIFICADOR = 'verificador';

    /** Ve todas las facturas (solo lectura + verificar las propias vía `vendedor_id` en factura). */
    public const ROLE_VENDEDOR_GENERAL = 'vendedor_general';

    /** Solo facturas donde él es `vendedor_id` (lectura + verificar). */
    public const ROLE_VENDEDOR_NORMAL = 'vendedor_normal';

    /** @var list<string> */
    public static array $roles = [
        self::ROLE_ADMIN,
        self::ROLE_VERIFICADOR,
        self::ROLE_VENDEDOR_GENERAL,
        self::ROLE_VENDEDOR_NORMAL,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN && $this->is_active;
    }

    /** Administración de facturas, cobranza, maestros y reportes (no aplica a usuarios con rol solo vendedor). */
    public function puedeGestionOperativaCompleta(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_VERIFICADOR], true);
    }

    public function esVendedorGeneral(): bool
    {
        return $this->role === self::ROLE_VENDEDOR_GENERAL && $this->is_active;
    }

    public function esVendedorNormal(): bool
    {
        return $this->role === self::ROLE_VENDEDOR_NORMAL && $this->is_active;
    }

    public function esVendedorRestringido(): bool
    {
        return $this->esVendedorGeneral() || $this->esVendedorNormal();
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_VERIFICADOR => 'Verificador',
            self::ROLE_VENDEDOR_GENERAL => 'Vendedor general',
            self::ROLE_VENDEDOR_NORMAL => 'Vendedor',
        ];
    }

    /**
     * Clientes asignados a este usuario como vendedor (Paso 1 Vic).
     *
     * @return HasMany<Cliente, $this>
     */
    public function clientesComoVendedor(): HasMany
    {
        return $this->hasMany(Cliente::class, 'vendedor_id');
    }

    /** Usuarios que pueden figurar como vendedor en clientes y en facturas. */
    public static function opcionesVendedor()
    {
        return static::query()
            ->where('is_active', true)
            ->whereIn('role', [
                self::ROLE_ADMIN,
                self::ROLE_VERIFICADOR,
                self::ROLE_VENDEDOR_GENERAL,
                self::ROLE_VENDEDOR_NORMAL,
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }
}
