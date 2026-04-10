<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete()->cascadeOnUpdate();
            $table->date('fecha_recibo');
            $table->decimal('monto_aplicado_usd', 15, 2);
            $table->string('tipo_tasa', 20);
            $table->decimal('valor_tasa', 15, 4);
            $table->decimal('monto_bs', 15, 2)->nullable();
            $table->string('metodo_pago', 30);
            $table->string('referencia', 255)->nullable();
            $table->string('banco_destino', 100)->nullable();
            $table->string('comprobante_path', 500)->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
