<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@millennium.local'],
            [
                'name' => 'Administrador Millennium',
                'password' => 'Millennium2026!',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Accesos para demos / equipo (cambiar contraseñas en producción o desde /usuarios si eres admin).
        User::query()->updateOrCreate(
            ['email' => 'video@millennium.local'],
            [
                'name' => 'Equipo video',
                'password' => 'VideoMillennium2026!',
                'role' => User::ROLE_VENDEDOR,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'invitado@millennium.local'],
            [
                'name' => 'Usuario invitado',
                'password' => 'InvitadoMillennium2026!',
                'role' => User::ROLE_VENDEDOR,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $categorias = [
            [
                'codigo' => 'VACA',
                'nombre' => 'Vaca',
                'descripcion' => 'Línea vacuno: carne y productos asociados. El código VACA (ej.) se usa en reportes y filtros por tipo de producto para comparar ventas “de vaca” vs otras líneas.',
            ],
            [
                'codigo' => 'BUF',
                'nombre' => 'Búfalo',
                'descripcion' => 'Línea búfalo: productos distintos al vacuno. Mantener un código corto (BUF) permite segmentar ventas por categoría en reportes cruzados.',
            ],
            [
                'codigo' => 'TRAST',
                'nombre' => 'Trastes',
                'descripcion' => 'Trastes y similares (categoría aparte). Útil para medir cuánto se vende de esa línea sin mezclarla con kilos de carne.',
            ],
        ];
        foreach ($categorias as $c) {
            Categoria::query()->updateOrCreate(
                ['codigo' => $c['codigo']],
                ['nombre' => $c['nombre'], 'descripcion' => $c['descripcion']]
            );
        }
    }
}
