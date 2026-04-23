<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('numero_factura', 64)->nullable()->unique();
            $table->date('fecha_emision');
            $table->unsignedSmallInteger('dias_credito')->default(0);
            $table->date('fecha_vencimiento');
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('saldo_pendiente', 15, 2)->default(0);
            $table->string('estado_pago', 20)->default('abierta');
            $table->foreignId('creado_por')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('fecha_verificacion')->nullable();
            $table->timestamps();
        });

        Schema::create('factura_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete()->cascadeOnUpdate();
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio_unitario', 15, 4);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_lineas');
        Schema::dropIfExists('facturas');
    }
};
