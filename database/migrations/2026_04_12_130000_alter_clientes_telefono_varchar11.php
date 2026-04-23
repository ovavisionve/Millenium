<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — acota telefono a 11 caracteres (móvil 04 + 9 dígitos); antes era VARCHAR(40).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clientes')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE clientes MODIFY telefono VARCHAR(11) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('clientes')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE clientes MODIFY telefono VARCHAR(40) NULL');
        }
    }
};
