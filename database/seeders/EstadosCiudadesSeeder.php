<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class EstadosCiudadesSeeder extends Seeder
{
    public function run(): void
    {
        $sqlPath = env('VENEZUELA_SQL_PATH')
            ?: base_path('..'.DIRECTORY_SEPARATOR.'venezuela.sql');

        if (! is_file($sqlPath)) {
            throw new RuntimeException("No se encontró el archivo venezuela.sql en: {$sqlPath}");
        }

        $sql = file_get_contents($sqlPath);
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException("No se pudo leer o el archivo está vacío: {$sqlPath}");
        }

        [$estadosRows, $ciudadesRows] = $this->parseEstadosCiudadesFromSql($sql);

        DB::transaction(function () use ($estadosRows, $ciudadesRows) {
            foreach (array_chunk($estadosRows, 250) as $chunk) {
                DB::table('estados')->upsert(
                    $chunk,
                    ['id_estado'],
                    ['nombre_estado', 'codigo_iso_3166_2']
                );
            }

            foreach (array_chunk($ciudadesRows, 500) as $chunk) {
                DB::table('ciudades')->upsert(
                    $chunk,
                    ['id_ciudad'],
                    ['id_estado', 'nombre_ciudad', 'es_capital']
                );
            }
        });
    }

    /**
     * @return array{0: array<int, array{id_estado:int,nombre_estado:string,codigo_iso_3166_2:string}>, 1: array<int, array{id_ciudad:int,id_estado:int,nombre_ciudad:string,es_capital:bool}>}
     */
    private function parseEstadosCiudadesFromSql(string $sql): array
    {
        $estadosInsert = $this->extractInsertValuesBlock($sql, 'estados');
        $ciudadesInsert = $this->extractInsertValuesBlock($sql, 'ciudades');

        $estadosTuples = $this->parseTuples($estadosInsert);
        $ciudadesTuples = $this->parseTuples($ciudadesInsert);

        $estadosRows = [];
        foreach ($estadosTuples as $t) {
            if (count($t) < 3) {
                continue;
            }
            $estadosRows[] = [
                'id_estado' => (int) $t[0],
                'nombre_estado' => (string) $t[1],
                'codigo_iso_3166_2' => (string) $t[2],
            ];
        }

        $ciudadesRows = [];
        foreach ($ciudadesTuples as $t) {
            if (count($t) < 4) {
                continue;
            }
            $ciudadesRows[] = [
                'id_ciudad' => (int) $t[0],
                'id_estado' => (int) $t[1],
                'nombre_ciudad' => (string) $t[2],
                'es_capital' => ((int) $t[3]) === 1,
            ];
        }

        if ($estadosRows === [] || $ciudadesRows === []) {
            throw new RuntimeException('No se pudo extraer data de estados/ciudades desde venezuela.sql (bloques INSERT vacíos).');
        }

        return [$estadosRows, $ciudadesRows];
    }

    private function extractInsertValuesBlock(string $sql, string $table): string
    {
        $pattern = "/INSERT\\s+INTO\\s+`".preg_quote($table, '/')."`\\s*\\([^\\)]*\\)\\s*VALUES\\s*(.*?);/si";
        if (! preg_match($pattern, $sql, $m)) {
            throw new RuntimeException("No se encontró INSERT INTO `{$table}` ... VALUES ...; en venezuela.sql");
        }

        return trim($m[1]);
    }

    /**
     * Parsea una lista de tuplas estilo:
     * (1, 2, 'Texto', 0),
     * (2, 3, 'Otro', 1)
     *
     * @return array<int, array<int, int|string|null>>
     */
    private function parseTuples(string $valuesBlock): array
    {
        $s = trim($valuesBlock);
        $len = strlen($s);
        $i = 0;

        $tuples = [];

        $skipWs = function () use (&$i, $len, $s): void {
            while ($i < $len) {
                $c = $s[$i];
                if ($c === ' ' || $c === "\n" || $c === "\r" || $c === "\t") {
                    $i++;
                    continue;
                }
                break;
            }
        };

        $readNumber = function () use (&$i, $len, $s): int {
            $start = $i;
            while ($i < $len && preg_match('/[0-9\-]/', $s[$i]) === 1) {
                $i++;
            }
            return (int) substr($s, $start, $i - $start);
        };

        $readIdentifier = function () use (&$i, $len, $s): string {
            $start = $i;
            while ($i < $len && preg_match('/[A-Za-z_]/', $s[$i]) === 1) {
                $i++;
            }
            return strtoupper(substr($s, $start, $i - $start));
        };

        $readString = function () use (&$i, $len, $s): string {
            // Asume comillas simples SQL. Maneja escape de comilla simple '' -> '
            if ($s[$i] !== "'") {
                return '';
            }
            $i++; // skip opening quote
            $out = '';
            while ($i < $len) {
                $c = $s[$i];
                if ($c === "'") {
                    // escape '' ?
                    if (($i + 1) < $len && $s[$i + 1] === "'") {
                        $out .= "'";
                        $i += 2;
                        continue;
                    }
                    $i++; // closing quote
                    break;
                }
                $out .= $c;
                $i++;
            }
            return $out;
        };

        while ($i < $len) {
            $skipWs();
            if ($i >= $len) {
                break;
            }

            if ($s[$i] !== '(') {
                $i++;
                continue;
            }
            $i++; // (

            $tuple = [];
            while ($i < $len) {
                $skipWs();
                if ($i >= $len) {
                    break;
                }

                $c = $s[$i];
                if ($c === "'") {
                    $tuple[] = $readString();
                } elseif (preg_match('/[0-9\-]/', $c) === 1) {
                    $tuple[] = $readNumber();
                } elseif (preg_match('/[A-Za-z_]/', $c) === 1) {
                    $ident = $readIdentifier();
                    $tuple[] = $ident === 'NULL' ? null : $ident;
                } else {
                    $i++;
                    continue;
                }

                $skipWs();
                if ($i < $len && $s[$i] === ',') {
                    $i++; // comma between fields
                    continue;
                }
                if ($i < $len && $s[$i] === ')') {
                    $i++; // )
                    break;
                }
            }

            $tuples[] = $tuple;

            $skipWs();
            if ($i < $len && $s[$i] === ',') {
                $i++; // comma between tuples
            }
        }

        return $tuples;
    }
}

