<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Millennium — líneas de factura por categoría (animal / línea); elimina productos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table): void {
                if (! Schema::hasColumn('categorias', 'unidad')) {
                    $table->string('unidad', 16)->default('unidad')->after('descripcion');
                }
                if (! Schema::hasColumn('categorias', 'activo')) {
                    $table->boolean('activo')->default(true)->after('unidad');
                }
            });
            DB::table('categorias')->whereNull('unidad')->update(['unidad' => 'unidad']);
            DB::table('categorias')->whereNull('activo')->update(['activo' => true]);
        }

        if (! Schema::hasTable('factura_lineas')) {
            if (Schema::hasTable('productos')) {
                Schema::dropIfExists('productos');
            }

            return;
        }

        if (! Schema::hasColumn('factura_lineas', 'categoria_id')) {
            Schema::table('factura_lineas', function (Blueprint $table): void {
                $table->foreignId('categoria_id')->nullable()->after('factura_id')->constrained('categorias')->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('factura_lineas', 'producto_id') && Schema::hasTable('productos')) {
            $rows = DB::table('factura_lineas')->select('id', 'producto_id')->whereNotNull('producto_id')->get();
            foreach ($rows as $row) {
                $cid = DB::table('productos')->where('id', $row->producto_id)->value('categoria_id');
                if ($cid) {
                    DB::table('factura_lineas')->where('id', $row->id)->update(['categoria_id' => $cid]);
                }
            }

            $firstCat = DB::table('categorias')->orderBy('id')->value('id');
            if ($firstCat) {
                DB::table('factura_lineas')->whereNull('categoria_id')->update(['categoria_id' => $firstCat]);
            }

            Schema::table('factura_lineas', function (Blueprint $table): void {
                $table->dropForeign(['producto_id']);
            });

            Schema::table('factura_lineas', function (Blueprint $table): void {
                $table->dropColumn('producto_id');
            });

            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql' && Schema::hasColumn('factura_lineas', 'categoria_id')) {
                DB::statement('ALTER TABLE factura_lineas MODIFY categoria_id BIGINT UNSIGNED NOT NULL');
            }
        }

        Schema::dropIfExists('productos');
    }

    public function down(): void
    {
        //
    }
};
