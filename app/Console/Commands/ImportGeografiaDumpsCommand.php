<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Lee `municipios.sql` y `parroquias.sql` (columnas municipio/parroquia del dump original)
 * y hace upsert al esquema Laravel: nombre_municipio, nombre_parroquia, id_estado, id_municipio.
 *
 * Ejemplos:
 *   php artisan millennium:import-geografia-dumps
 *   php artisan millennium:import-geografia-dumps --solo-id-estado
 *   php artisan millennium:import-geografia-dumps --municipios=C:\tmp\municipios.sql
 */
class ImportGeografiaDumpsCommand extends Command
{
    /** id_estado oficial Aragua en el dump nacional / estados INE. */
    public const ID_ESTADO_ARAGUA = 4;

    protected $signature = 'millennium:import-geografia-dumps
                            {--municipios= : Ruta absoluta o relativa a municipios.sql}
                            {--parroquias= : Ruta absoluta o relativa a parroquias.sql}
                            {--skip-parroquias : Solo municipios}
                            {--solo-id-estado : Solo rellena id_estado desde el dump (no cambia nombre_municipio)}';

    protected $description = 'Importar dumps municipios.sql / parroquias.sql al esquema Millennium (upsert idénticos IDs del dump nacional).';

    public function handle(): int
    {
        $mPath = $this->resolvePath($this->option('municipios'), 'municipios.sql');
        $pPath = $this->resolvePath($this->option('parroquias'), 'parroquias.sql');

        if (! is_readable($mPath)) {
            $this->error('No se puede leer municipios: '.$mPath);

            return self::FAILURE;
        }

        $estadosN = (int) DB::table('estados')->count();
        if ($estadosN < 1) {
            $this->warn('La tabla `estados` está vacía. Cargá estados antes (p. ej. EstadosCiudadesSeeder / dump). Sin FK válidas puede fallar el upsert de municipios.');
        }

        $sqlM = file_get_contents($mPath);
        if (! is_string($sqlM) || $sqlM === '') {
            $this->error('municipios.sql vacío o ilegible.');

            return self::FAILURE;
        }

        $tuplasM = $this->extraerTuplasTres($sqlM, 'municipios');
        if ($tuplasM === []) {
            $this->error('No se encontró INSERT INTO municipios ... VALUES en el archivo.');

            return self::FAILURE;
        }

        $rowsM = [];
        foreach ($tuplasM as $t) {
            $rowsM[] = [
                'id_municipio' => (int) $t[0],
                'id_estado' => (int) $t[1],
                'nombre_municipio' => (string) $t[2],
            ];
        }

        if ($this->option('solo-id-estado')) {
            $n = 0;
            foreach ($rowsM as $row) {
                $affected = DB::table('municipios')->where('id_municipio', $row['id_municipio'])->update([
                    'id_estado' => $row['id_estado'],
                ]);
                $n += $affected;
            }
            $this->info('Municipios: id_estado actualizado en filas existentes (filas tocadas aprox.: '.$n.', tuplas dump: '.count($rowsM).').');
            $this->warn('Si un id_municipio no existía en BD, no se insertó fila nueva; usá el import completo sin --solo-id-estado.');
        } else {
            foreach (array_chunk($rowsM, 200) as $chunk) {
                DB::table('municipios')->upsert($chunk, ['id_municipio'], ['id_estado', 'nombre_municipio']);
            }
            $this->info('Municipios upsert: '.count($rowsM));
        }

        $nullEst = (int) DB::table('municipios')->whereNull('id_estado')->count();
        $this->line('municipios.id_estado NULL restantes: '.$nullEst);

        if ($this->option('skip-parroquias')) {
            return self::SUCCESS;
        }

        if (! is_readable($pPath)) {
            $this->error('No se puede leer parroquias: '.$pPath);

            return self::FAILURE;
        }

        $sqlP = file_get_contents($pPath);
        if (! is_string($sqlP) || $sqlP === '') {
            $this->error('parroquias.sql vacío o ilegible.');

            return self::FAILURE;
        }

        $tuplasP = $this->extraerTuplasTres($sqlP, 'parroquias');
        if ($tuplasP === []) {
            $this->error('No se encontró INSERT INTO parroquias ... VALUES en el archivo.');

            return self::FAILURE;
        }

        $rowsP = [];
        foreach ($tuplasP as $t) {
            $rowsP[] = [
                'id_parroquia' => (int) $t[0],
                'id_municipio' => (int) $t[1],
                'nombre_parroquia' => (string) $t[2],
            ];
        }

        foreach (array_chunk($rowsP, 250) as $chunk) {
            DB::table('parroquias')->upsert($chunk, ['id_parroquia'], ['id_municipio', 'nombre_parroquia']);
        }
        $this->info('Parroquias upsert: '.count($rowsP));

        $nullMun = (int) DB::table('parroquias')->whereNull('id_municipio')->count();
        $this->line('parroquias.id_municipio NULL restantes: '.$nullMun);

        $this->comment('Si había clientes con id_parroquia/id_municipio antiguos (seed solo-Aragua), revisá esas filas: los IDs nacionales pueden no coincidir con el seed anterior.');

        return self::SUCCESS;
    }

    private function resolvePath(?string $opt, string $defaultBasename): string
    {
        if (is_string($opt) && trim($opt) !== '') {
            $path = $opt;
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
                $path = base_path($path);
            }

            return $path;
        }

        return base_path($defaultBasename);
    }

    /**
     * @return list<array<int,string>>
     */
    private function extraerTuplasTres(string $sqlFull, string $tabla): array
    {
        $re = '/INSERT\s+INTO\s*`?'.preg_quote($tabla, '/').'`?\s*\([^)]+\)\s*VALUES\s*(.+?);/is';
        if (! preg_match($re, $sqlFull, $m)) {
            return [];
        }
        $valuesRaw = $m[1];
        preg_match_all("/\\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*\\)/u", $valuesRaw, $mm, PREG_SET_ORDER);

        $out = [];
        foreach ($mm as $row) {
            $name = str_replace(["\\'", '\\\\'], ["'", '\\'], (string) $row[3]);
            $out[] = [(string) $row[1], (string) $row[2], $name];
        }

        return $out;
    }
}
