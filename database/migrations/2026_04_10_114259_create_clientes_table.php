<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esta migración define la estructura original de la tabla 'clientes' en la base de datos.
 *
 * Es el primer paso para entender cómo funciona el maestro de clientes desde la base de datos.
 * 
 * - Las migraciones en Laravel se usan para crear, modificar y eliminar tablas.
 * - Aquí definimos cómo quiero que 'clientes' exista en la base de datos.
 */

return new class extends Migration
{
    /**
     * El método 'up' se ejecuta cuando ejecutas 'php artisan migrate'.
     * Aquí se crea la tabla 'clientes' con sus columnas originales.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental (id)
            $table->string('rif_cedula', 32)->unique(); // Identificación fiscal o cédula, única por cliente
            $table->string('nombre', 180); // Nombre o razón social del cliente
            $table->string('telefono', 40)->nullable(); // Teléfono del cliente (opcional)
            $table->string('zona', 120); // Zona geográfica o sector al que pertenece el cliente
            $table->foreignId('vendedor_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate(); // Relación (opcional) con el usuario vendedor
            $table->timestamps(); // Marca de tiempo de creación y última actualización
        });
    }

    /**
     * El método 'down' se ejecuta cuando se revierte la migración.
     * Elimina la tabla 'clientes' de la base de datos.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
