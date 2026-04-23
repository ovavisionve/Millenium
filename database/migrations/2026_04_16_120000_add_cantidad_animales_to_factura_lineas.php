<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('factura_lineas')) {
            return;
        }

        if (! Schema::hasColumn('factura_lineas', 'cantidad_animales')) {
            Schema::table('factura_lineas', function (Blueprint $table): void {
                $table->unsignedSmallInteger('cantidad_animales')->nullable()->after('categoria_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('factura_lineas') && Schema::hasColumn('factura_lineas', 'cantidad_animales')) {
            Schema::table('factura_lineas', function (Blueprint $table): void {
                $table->dropColumn('cantidad_animales');
            });
        }
    }
};
