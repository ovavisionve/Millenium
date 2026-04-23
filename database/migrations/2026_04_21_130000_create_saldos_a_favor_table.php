<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldos_a_favor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('origen_pago_id')->nullable()->constrained('pagos')->nullOnDelete()->cascadeOnUpdate();
            $table->date('fecha_recibo')->nullable();
            $table->decimal('monto_usd', 15, 2);
            $table->decimal('saldo_usd', 15, 2);
            $table->text('notas')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['cliente_id', 'saldo_usd']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldos_a_favor');
    }
};
