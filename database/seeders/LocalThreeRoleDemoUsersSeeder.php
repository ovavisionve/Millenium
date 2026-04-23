<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Tres usuarios de prueba con roles distintos (solo APP_ENV=local).
 *
 * Uso: php artisan db:seed --class=LocalThreeRoleDemoUsersSeeder
 */
class LocalThreeRoleDemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('Abortado: este seeder solo puede ejecutarse con APP_ENV=local.');

            return;
        }

        $rows = [];

        $admin = User::query()->updateOrCreate(
            ['email' => 'demo-admin@millennium.local'],
            [
                'name' => 'Demo — Administrador',
                'password' => 'Demo-Admin-2026-K9m',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $rows[] = [User::roleLabels()[User::ROLE_ADMIN], $admin->email, 'Demo-Admin-2026-K9m'];

        $vg = User::query()->updateOrCreate(
            ['email' => 'demo-vendedor-general@millennium.local'],
            [
                'name' => 'Demo — Vendedor general',
                'password' => 'Demo-VGeneral-2026-P4n',
                'role' => User::ROLE_VENDEDOR_GENERAL,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $rows[] = [User::roleLabels()[User::ROLE_VENDEDOR_GENERAL], $vg->email, 'Demo-VGeneral-2026-P4n'];

        $vn = User::query()->updateOrCreate(
            ['email' => 'demo-vendedor@millennium.local'],
            [
                'name' => 'Demo — Vendedor',
                'password' => 'Demo-Vendedor-2026-R2w',
                'role' => User::ROLE_VENDEDOR_NORMAL,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $rows[] = [User::roleLabels()[User::ROLE_VENDEDOR_NORMAL], $vn->email, 'Demo-Vendedor-2026-R2w'];

        $this->command?->newLine();
        $this->command?->warn('Usuarios demo por rol (solo local; no compartir en producción):');
        $this->command?->table(['Rol', 'Correo', 'Contraseña'], $rows);
    }
}
