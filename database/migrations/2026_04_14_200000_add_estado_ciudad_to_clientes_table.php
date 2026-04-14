<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — cliente: estado (obligatorio en validación) y ciudad (opcional) para reportes por entidad federal.
 *
 * `id_municipio` / `id_parroquia` siguen opcionales; si existen, deben coincidir con el estado elegido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'id_estado')) {
                $table->unsignedInteger('id_estado')->nullable()->after('direccion');
            }
            if (! Schema::hasColumn('clientes', 'id_ciudad')) {
                $table->unsignedInteger('id_ciudad')->nullable()->after('id_estado');
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'id_estado')) {
                $table->foreign('id_estado')
                    ->references('id_estado')
                    ->on('estados')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
            if (Schema::hasColumn('clientes', 'id_ciudad')) {
                $table->foreign('id_ciudad')
                    ->references('id_ciudad')
                    ->on('ciudades')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });

        // Millennium: completar estado desde municipio ya guardado (clientes previos a este cambio).
        if (Schema::hasColumn('clientes', 'id_estado') && Schema::hasColumn('clientes', 'id_municipio')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('
                    UPDATE clientes
                    SET id_estado = (
                        SELECT m.id_estado FROM municipios m
                        WHERE m.id_municipio = clientes.id_municipio
                    )
                    WHERE id_estado IS NULL AND id_municipio IS NOT NULL
                ');
            } else {
                DB::statement('
                    UPDATE clientes c
                    INNER JOIN municipios m ON m.id_municipio = c.id_municipio
                    SET c.id_estado = m.id_estado
                    WHERE c.id_estado IS NULL
                ');
            }
        }
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'id_ciudad')) {
                $table->dropForeign(['id_ciudad']);
            }
            if (Schema::hasColumn('clientes', 'id_estado')) {
                $table->dropForeign(['id_estado']);
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            $cols = [];
            foreach (['id_ciudad', 'id_estado'] as $c) {
                if (Schema::hasColumn('clientes', $c)) {
                    $cols[] = $c;
                }
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
