<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — catálogo de municipios (zona predeterminada).
 *
 * Basado en `municipios.sql` provisto por el equipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->unsignedInteger('id_municipio')->primary();
            $table->string('nombre_municipio', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
