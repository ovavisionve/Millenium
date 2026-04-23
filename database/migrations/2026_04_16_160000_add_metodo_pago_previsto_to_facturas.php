<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facturas')) {
            return;
        }

        if (! Schema::hasColumn('facturas', 'metodo_pago_previsto')) {
            Schema::table('facturas', function (Blueprint $table): void {
                $table->string('metodo_pago_previsto', 30)->nullable()->after('dias_credito');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facturas') && Schema::hasColumn('facturas', 'metodo_pago_previsto')) {
            Schema::table('facturas', function (Blueprint $table): void {
                $table->dropColumn('metodo_pago_previsto');
            });
        }
    }
};
