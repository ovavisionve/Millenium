<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * El seed antiguo `MunicipiosParroquiasSeeder` cargaba solo municipios de Aragua con id_municipio 1–18
 * pero sin `id_estado`. Este comando asigna Aragua (id_estado = 4 según catálogo nacional).
 *
 * No usar si ya importaste el dump nacional completo (ahí los PK ya no son 1–18 para esos nombres).
 */
class FixLegacyAraguaSeedMunicipiosEstadoCommand extends Command
{
    protected $signature = 'millennium:fix-legacy-aragua-municipios-estado
                            {--dry-run : Solo mostrar cuántas filas tendrían actualización}';

    protected $description = 'Rellena id_estado=4 (Aragua) en municipios 1–18 si estaban NULL (seed legado).';

    public function handle(): int
    {
        $idAragua = ImportGeografiaDumpsCommand::ID_ESTADO_ARAGUA;

        if (! DB::table('estados')->where('id_estado', $idAragua)->exists()) {
            $this->error('No existe `estados.id_estado` = '.$idAragua.'. Cargá estados antes (migraciones / seeder).');

            return self::FAILURE;
        }

        $query = DB::table('municipios')
            ->whereNull('id_estado')
            ->whereBetween('id_municipio', [1, 18]);

        $count = (int) $query->count();

        if ($this->option('dry-run')) {
            $this->info('Filas con id_estado NULL y id_municipio entre 1 y 18: '.$count);

            return self::SUCCESS;
        }

        $updated = DB::table('municipios')
            ->whereNull('id_estado')
            ->whereBetween('id_municipio', [1, 18])
            ->update(['id_estado' => $idAragua]);

        $this->info('Filas actualizadas con id_estado='.$idAragua.' (Aragua): '.$updated);

        $stillNull = (int) DB::table('municipios')->whereNull('id_estado')->count();
        if ($stillNull > 0) {
            $this->warn('municipios.id_estado NULL restantes (otros IDs): '.$stillNull.'. Ejecutá `php artisan millennium:import-geografia-dumps`.');
        }

        return self::SUCCESS;
    }
}
