<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('millennium:factory-reset {--force : Ejecutar incluso en producción}', function () {
    /**
     * Millennium — reset “de fábrica” para pruebas:
     * - Borra datos operativos (clientes, productos, facturas, pagos, etc.)
     * - Mantiene `users` y `migrations` para no perder el login ni el historial de migraciones
     *
     * Uso:
     * - Local/dev:  php artisan millennium:factory-reset
     * - Producción: php artisan millennium:factory-reset --force
     */
    $force = (bool) $this->option('force');
    if (app()->environment('production') && ! $force) {
        $this->error('Bloqueado en producción. Usá --force si estás 100% seguro.');
        return 1;
    }

    $keep = [
        'users',
        'migrations',
        'password_reset_tokens',
    ];

    $driver = DB::getDriverName();
    if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
        $this->error("Driver no soportado para reset automático: {$driver}");
        return 1;
    }

    // Descubrir todas las tablas del esquema actual sin hardcodear módulos.
    $tables = [];
    if (in_array($driver, ['mysql', 'mariadb'], true)) {
        $rows = DB::select('SHOW TABLES');
        foreach ($rows as $row) {
            $tables[] = (string) array_values((array) $row)[0];
        }
    } else {
        $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        foreach ($rows as $row) {
            $tables[] = (string) ($row->name ?? '');
        }
    }

    $tables = array_values(array_filter($tables, fn ($t) => $t !== '' && ! in_array($t, $keep, true)));
    sort($tables);

    if (empty($tables)) {
        $this->info('No hay tablas para limpiar (excluyendo users/migrations).');
        return 0;
    }

    $this->info('Se limpiarán estas tablas:');
    foreach ($tables as $t) {
        $this->line(' - '.$t);
    }

    try {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tables as $t) {
                DB::statement('TRUNCATE TABLE `'.$t.'`');
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            DB::beginTransaction();
            // SQLite: TRUNCATE no existe; usamos DELETE.
            foreach ($tables as $t) {
                DB::statement('DELETE FROM "'.$t.'"');
            }
            DB::commit();
        }
    } catch (\Throwable $e) {
        // En MySQL/MariaDB TRUNCATE hace commit implícito: no usamos transacciones ahí.
        if ($driver === 'sqlite') {
            DB::rollBack();
        }
        $this->error('Error limpiando tablas: '.$e->getMessage());
        return 1;
    }

    $this->info('Reset completado. Usuarios y migraciones se conservaron.');
    return 0;
})->purpose('Millennium: limpiar tablas para pruebas conservando users');
