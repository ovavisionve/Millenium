<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (! app()->environment('production')) {
            $this->call(MunicipiosParroquiasSeeder::class);
            $this->seedLocalDemoUsers();
        } else {
            $this->command?->warn('Entorno production: se omiten MunicipiosParroquiasSeeder y usuarios @millennium.local (evita pisar geografía INE y credenciales demo).');
        }

        $this->seedCategoriasYBancos();
    }

    /**
     * Usuarios y claves de demo solo fuera de production.
     */
    private function seedLocalDemoUsers(): void
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

        User::query()->updateOrCreate(
            ['email' => 'video@millennium.local'],
            [
                'name' => 'Administrador (demo / video)',
                'password' => 'Millennium2026!',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'colaborador@millennium.local'],
            [
                'name' => 'Administrador (colaborador)',
                'password' => 'Millennium2026!',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedCategoriasYBancos(): void
    {
        $categorias = [
            [
                'codigo' => 'VACA',
                'nombre' => 'Vaca',
                'descripcion' => 'Línea vacuno (animal / venta por categoría). El código se usa en reportes y filtros para comparar ventas de vaca vs otras líneas.',
                'unidad' => Categoria::UNIDAD_UNIDAD,
                'activo' => true,
            ],
            [
                'codigo' => 'BUF',
                'nombre' => 'Búfalo',
                'descripcion' => 'Línea búfalo. Un código corto (BUF) permite segmentar ventas por categoría en reportes cruzados.',
                'unidad' => Categoria::UNIDAD_UNIDAD,
                'activo' => true,
            ],
            [
                'codigo' => 'TRAST',
                'nombre' => 'Trastes',
                'descripcion' => 'Trastes y similares (categoría aparte). Útil para medir cuánto se vende de esa línea sin mezclarla con otras.',
                'unidad' => Categoria::UNIDAD_UNIDAD,
                'activo' => true,
            ],
        ];
        foreach ($categorias as $c) {
            Categoria::query()->updateOrCreate(
                ['codigo' => $c['codigo']],
                [
                    'nombre' => $c['nombre'],
                    'descripcion' => $c['descripcion'],
                    'unidad' => $c['unidad'],
                    'activo' => $c['activo'],
                ]
            );
        }

        $bancos = [
            ['nombre' => 'Banesco Karina', 'descripcion' => 'Cuenta bancaria operativa de Karina.', 'activo' => true],
            ['nombre' => 'Banesco Nelson', 'descripcion' => 'Cuenta bancaria operativa de Nelson.', 'activo' => true],
            ['nombre' => 'BNC', 'descripcion' => 'Banco Nacional de Credito.', 'activo' => true],
        ];

        foreach ($bancos as $banco) {
            Banco::query()->updateOrCreate(
                ['nombre' => $banco['nombre']],
                [
                    'descripcion' => $banco['descripcion'],
                    'activo' => $banco['activo'],
                ]
            );
        }
    }
}
