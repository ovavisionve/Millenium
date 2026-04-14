<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — Venezuela: estados/ciudades + vínculo de municipios a estado.
 *
 * Importante:
 * - Mantiene `municipios.id_municipio` como PK heredada.
 * - Agrega `municipios.id_estado` para reportes/filtrado por estado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->unsignedInteger('id_estado')->primary();
            $table->string('nombre_estado', 250);
            $table->string('codigo_iso_3166_2', 4);
        });

        Schema::create('ciudades', function (Blueprint $table) {
            $table->unsignedInteger('id_ciudad')->primary();
            $table->unsignedInteger('id_estado');
            $table->string('nombre_ciudad', 200);
            $table->boolean('es_capital')->default(false);

            $table->foreign('id_estado')
                ->references('id_estado')
                ->on('estados')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index(['id_estado', 'nombre_ciudad']);
        });

        Schema::table('municipios', function (Blueprint $table) {
            if (! Schema::hasColumn('municipios', 'id_estado')) {
                // nullable por compatibilidad (ya existe data parcial); se llena con importador.
                $table->unsignedInteger('id_estado')->nullable()->after('id_municipio');
                $table->index(['id_estado', 'nombre_municipio']);
            }
        });

        Schema::table('municipios', function (Blueprint $table) {
            if (Schema::hasColumn('municipios', 'id_estado')) {
                $table->foreign('id_estado')
                    ->references('id_estado')
                    ->on('estados')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('municipios', function (Blueprint $table) {
            if (Schema::hasColumn('municipios', 'id_estado')) {
                $table->dropForeign(['id_estado']);
                $table->dropIndex(['id_estado', 'nombre_municipio']);
                $table->dropColumn('id_estado');
            }
        });

        Schema::dropIfExists('ciudades');
        Schema::dropIfExists('estados');
    }
};
