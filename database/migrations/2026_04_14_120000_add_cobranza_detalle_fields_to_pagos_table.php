<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->date('fecha_publicacion')->nullable()->after('fecha_recibo');
            $table->string('cuenta_destino', 255)->nullable()->after('banco_destino');
            $table->string('recibido_por', 255)->nullable()->after('cuenta_destino');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['fecha_publicacion', 'cuenta_destino', 'recibido_por']);
        });
    }
};
