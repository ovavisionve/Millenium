<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — la “zona / ruta” pasa a ser opcional; el ancla territorial para reportes es `id_estado`.
 *
 * Sin doctrine/dbal: usamos SQL nativo por driver.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clientes', 'zona')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE clientes MODIFY zona VARCHAR(120) NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite no soporta MODIFY; en desarrollo suele bastar recrear tabla — aquí no-op seguro.
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('clientes', 'zona')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE clientes MODIFY zona VARCHAR(120) NOT NULL');
        }
    }
};
