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

    public const ROLE_VENDEDOR = 'vendedor';

    /** @var list<string> */
    public static array $roles = [
        self::ROLE_ADMIN,
        self::ROLE_VERIFICADOR,
        self::ROLE_VENDEDOR,
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

    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_VERIFICADOR => 'Verificador',
            self::ROLE_VENDEDOR => 'Vendedor',
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

    /** Usuarios que pueden aparecer como vendedor en clientes (admin, verificador o vendedor). */
    public static function opcionesVendedor()
    {
        return static::query()
            ->where('is_active', true)
            ->whereIn('role', [self::ROLE_ADMIN, self::ROLE_VERIFICADOR, self::ROLE_VENDEDOR])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }
}
