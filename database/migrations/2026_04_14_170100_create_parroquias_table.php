<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — catálogo de parroquias (zona predeterminada) dependiente de municipio.
 *
 * Basado en `parroquias.sql` provisto por el equipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parroquias', function (Blueprint $table) {
            $table->unsignedInteger('id_parroquia')->primary();
            $table->unsignedInteger('id_municipio');
            $table->string('nombre_parroquia', 100);

            $table->foreign('id_municipio')
                ->references('id_municipio')
                ->on('municipios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['id_municipio', 'nombre_parroquia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parroquias');
    }
};
