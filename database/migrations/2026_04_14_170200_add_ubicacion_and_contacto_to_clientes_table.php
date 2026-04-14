<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — clientes: correo + dirección + ubicación predeterminada (municipio/parroquia).
 *
 * Mantiene `zona` como “ruta/sector” (texto) y suma municipio/parroquia para reportes consistentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'email')) {
                $table->string('email', 180)->nullable()->after('nombre_razon_social');
            }
            if (! Schema::hasColumn('clientes', 'direccion')) {
                $table->string('direccion', 255)->nullable()->after('email');
            }

            // Catálogo: municipio/parroquia (IDs heredados del dump).
            if (! Schema::hasColumn('clientes', 'id_municipio')) {
                $table->unsignedInteger('id_municipio')->nullable()->after('direccion');
            }
            if (! Schema::hasColumn('clientes', 'id_parroquia')) {
                $table->unsignedInteger('id_parroquia')->nullable()->after('id_municipio');
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'id_municipio')) {
                $table->foreign('id_municipio')
                    ->references('id_municipio')
                    ->on('municipios')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
            if (Schema::hasColumn('clientes', 'id_parroquia')) {
                $table->foreign('id_parroquia')
                    ->references('id_parroquia')
                    ->on('parroquias')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'id_parroquia')) {
                $table->dropForeign(['id_parroquia']);
            }
            if (Schema::hasColumn('clientes', 'id_municipio')) {
                $table->dropForeign(['id_municipio']);
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            $cols = [];
            foreach (['id_parroquia', 'id_municipio', 'direccion', 'email'] as $c) {
                if (Schema::hasColumn('clientes', $c)) {
                    $cols[] = $c;
                }
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
