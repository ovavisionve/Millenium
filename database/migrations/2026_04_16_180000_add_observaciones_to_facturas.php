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

        if (! Schema::hasColumn('facturas', 'observaciones')) {
            Schema::table('facturas', function (Blueprint $table): void {
                $table->text('observaciones')->nullable()->after('metodo_pago_previsto');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('facturas') && Schema::hasColumn('facturas', 'observaciones')) {
            Schema::table('facturas', function (Blueprint $table): void {
                $table->dropColumn('observaciones');
            });
        }
    }
};
