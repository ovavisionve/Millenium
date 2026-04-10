<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clientes', 'tipo_documento')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->string('tipo_documento', 1)->default('V')->after('id');
            });
        }

        if (! Schema::hasColumn('clientes', 'documento_numero')) {
            Schema::table('clientes', function (Blueprint $table) {
                $after = Schema::hasColumn('clientes', 'tipo_documento') ? 'tipo_documento' : 'id';
                $table->string('documento_numero', 32)->nullable()->after($after);
            });
        }

        if (Schema::hasColumn('clientes', 'rif_cedula')) {
            foreach (DB::table('clientes')->cursor() as $row) {
                $tipo = strtoupper(substr((string) ($row->tipo_documento ?? 'V'), 0, 1));
                if (! in_array($tipo, ['V', 'E', 'J', 'G', 'P'], true)) {
                    $tipo = 'V';
                }
                $num = trim((string) $row->rif_cedula);
                if (preg_match('/^([VEJGP])[\-\s]+(.+)$/iu', $num, $m)) {
                    $tipo = strtoupper($m[1]);
                    $num = trim(preg_replace('/\s+/', '', $m[2]));
                }
                if ($num === '') {
                    $num = 'SIN-'.$row->id;
                }
                DB::table('clientes')->where('id', $row->id)->update([
                    'tipo_documento' => $tipo,
                    'documento_numero' => $num,
                ]);
            }
        } else {
            foreach (DB::table('clientes')->cursor() as $row) {
                if ($row->documento_numero !== null && $row->documento_numero !== '') {
                    continue;
                }
                $tipo = strtoupper(substr((string) ($row->tipo_documento ?? 'V'), 0, 1));
                if (! in_array($tipo, ['V', 'E', 'J', 'G', 'P'], true)) {
                    $tipo = 'V';
                }
                DB::table('clientes')->where('id', $row->id)->update([
                    'tipo_documento' => $tipo,
                    'documento_numero' => 'SIN-'.$row->id,
                ]);
            }
        }

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)
            && Schema::hasColumn('clientes', 'documento_numero')) {
            DB::statement('ALTER TABLE clientes MODIFY documento_numero VARCHAR(32) NOT NULL');
        }

        if (Schema::hasColumn('clientes', 'rif_cedula')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropUnique(['rif_cedula']);
            });
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropColumn('rif_cedula');
            });
        }

        if (Schema::hasColumn('clientes', 'nombre')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->renameColumn('nombre', 'nombre_razon_social');
            });
        }

        $this->ensureCompositeUnique();
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if ($this->indexExists('clientes', 'clientes_tipo_documento_numero_unique')) {
                $table->dropUnique('clientes_tipo_documento_numero_unique');
            }
        });

        if (Schema::hasColumn('clientes', 'nombre_razon_social')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->renameColumn('nombre_razon_social', 'nombre');
            });
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->string('rif_cedula', 32)->nullable()->after('id');
        });

        foreach (DB::table('clientes')->cursor() as $row) {
            $rif = $row->tipo_documento.'-'.$row->documento_numero;
            if (strlen($rif) > 32) {
                $rif = substr($rif, 0, 32);
            }
            DB::table('clientes')->where('id', $row->id)->update(['rif_cedula' => $rif]);
        }

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE clientes MODIFY rif_cedula VARCHAR(32) NOT NULL');
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('rif_cedula');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['tipo_documento', 'documento_numero']);
        });
    }

    private function ensureCompositeUnique(): void
    {
        if ($this->indexExists('clientes', 'clientes_tipo_documento_numero_unique')) {
            return;
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique(['tipo_documento', 'documento_numero'], 'clientes_tipo_documento_numero_unique');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'`');

            foreach ($rows as $row) {
                if (($row->Key_name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select('PRAGMA index_list(`'.$table.'`)');

            foreach ($rows as $row) {
                if (($row->name ?? '') === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }
};
