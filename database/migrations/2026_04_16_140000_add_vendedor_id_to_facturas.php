<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facturas')) {
            return;
        }

        if (! Schema::hasColumn('facturas', 'vendedor_id')) {
            Schema::table('facturas', function (Blueprint $table): void {
                $table->foreignId('vendedor_id')->nullable()->after('cliente_id')->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            });
        }

        if (Schema::hasTable('clientes') && Schema::hasColumn('facturas', 'vendedor_id')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                // SQLite no soporta UPDATE..JOIN; usamos subquery correlacionada.
                DB::statement('
                    UPDATE facturas
                    SET vendedor_id = (
                        SELECT c.vendedor_id
                        FROM clientes c
                        WHERE c.id = facturas.cliente_id
                    )
                    WHERE vendedor_id IS NULL
                      AND EXISTS (
                        SELECT 1
                        FROM clientes c2
                        WHERE c2.id = facturas.cliente_id
                          AND c2.vendedor_id IS NOT NULL
                      )
                ');
            } else {
                DB::statement('
                    UPDATE facturas f
                    INNER JOIN clientes c ON c.id = f.cliente_id
                    SET f.vendedor_id = c.vendedor_id
                    WHERE f.vendedor_id IS NULL AND c.vendedor_id IS NOT NULL
                ');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facturas') && Schema::hasColumn('facturas', 'vendedor_id')) {
            Schema::table('facturas', function (Blueprint $table): void {
                $table->dropForeign(['vendedor_id']);
            });
            Schema::table('facturas', function (Blueprint $table): void {
                $table->dropColumn('vendedor_id');
            });
        }
    }
};
