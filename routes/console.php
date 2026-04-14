<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('millennium:import-venezuela {path? : Ruta al archivo venezuela.sql} {--truncate : Vaciar catálogos antes de importar}', function () {
    $path = $this->argument('path') ?: base_path('venezuela.sql');
    $truncate = (bool) $this->option('truncate');

    if (! is_string($path) || $path === '' || ! file_exists($path)) {
        $this->error('Millennium: no se encontró el archivo. Ruta recibida: '.$path);
        $this->line('Ejemplo: php artisan millennium:import-venezuela "C:\Users\pc\Desktop\Millennium\venezuela.sql"');

        return 1;
    }

    $sql = file_get_contents($path);
    if (! is_string($sql) || trim($sql) === '') {
        $this->error('Millennium: el archivo está vacío o no se pudo leer.');

        return 1;
    }

    // Millennium: importación robusta (regex por tabla) para el dump `venezuela.sql`.
    $extraerTuplas = static function (string $sqlFull, string $tabla, string $patTupla): array {
        $re = '/INSERT\s+INTO\s+`'.preg_quote($tabla, '/').'`\s*\([^)]+\)\s*VALUES\s*(.+?);/si';
        if (! preg_match($re, $sqlFull, $m)) {
            return [];
        }
        $valuesRaw = $m[1];
        preg_match_all($patTupla, $valuesRaw, $mm, PREG_SET_ORDER);

        return $mm;
    };

    $normalizar = static function ($v) {
        if ($v === null) {
            return null;
        }
        $v = trim((string) $v);
        if ($v === '' || strcasecmp($v, 'NULL') === 0) {
            return null;
        }
        if (preg_match('/^-?\d+$/', $v)) {
            return (int) $v;
        }

        return $v;
    };

    $tuplasEstados = $extraerTuplas($sql, 'estados', "/\\(\\s*(\\d+)\\s*,\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*\\)/u");
    $tuplasCiudades = $extraerTuplas($sql, 'ciudades', "/\\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*'([^']*)'\\s*,\\s*(\\d+)\\s*\\)/u");
    $tuplasMunicipios = $extraerTuplas($sql, 'municipios', "/\\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*'([^']*)'\\s*\\)/u");
    $tuplasParroquias = $extraerTuplas($sql, 'parroquias', "/\\(\\s*(\\d+)\\s*,\\s*(\\d+)\\s*,\\s*'([^']*)'\\s*\\)/u");

    if (empty($tuplasEstados) || empty($tuplasMunicipios) || empty($tuplasParroquias)) {
        $this->error('Millennium: no se pudieron encontrar los INSERT requeridos (estados/municipios/parroquias) dentro del archivo.');

        return 1;
    }

    if ($truncate) {
        // Millennium: vaciar solo catálogos (no toca clientes/ventas/etc.)
        DB::table('ciudades')->delete();
        DB::table('parroquias')->delete();
        DB::table('municipios')->delete();
        DB::table('estados')->delete();
    }

    // Estados
    $rowsEstados = [];
    foreach ($tuplasEstados as $t) {
        $rowsEstados[] = [
            'id_estado' => (int) $t[1],
            'nombre_estado' => (string) $t[2],
            'codigo_iso_3166_2' => (string) $t[3],
        ];
    }
    DB::table('estados')->upsert($rowsEstados, ['id_estado'], ['nombre_estado', 'codigo_iso_3166_2']);
    $this->info('Millennium: estados importados: '.count($rowsEstados));

    // Ciudades
    $rowsCiudades = [];
    foreach ($tuplasCiudades as $t) {
        $rowsCiudades[] = [
            'id_ciudad' => (int) $t[1],
            'id_estado' => (int) $t[2],
            'nombre_ciudad' => (string) $t[3],
            'es_capital' => (bool) ((int) $t[4]),
        ];
    }
    if (! empty($rowsCiudades)) {
        DB::table('ciudades')->upsert($rowsCiudades, ['id_ciudad'], ['id_estado', 'nombre_ciudad', 'es_capital']);
    }
    $this->info('Millennium: ciudades importadas: '.count($rowsCiudades));

    // Municipios
    $rowsMunicipios = [];
    foreach ($tuplasMunicipios as $t) {
        $rowsMunicipios[] = [
            'id_municipio' => (int) $t[1],
            'id_estado' => (int) $t[2],
            'nombre_municipio' => (string) $t[3],
        ];
    }
    DB::table('municipios')->upsert($rowsMunicipios, ['id_municipio'], ['id_estado', 'nombre_municipio']);
    $this->info('Millennium: municipios importados: '.count($rowsMunicipios));

    // Parroquias
    $rowsParroquias = [];
    foreach ($tuplasParroquias as $t) {
        $rowsParroquias[] = [
            'id_parroquia' => (int) $t[1],
            'id_municipio' => (int) $t[2],
            'nombre_parroquia' => (string) $t[3],
        ];
    }
    DB::table('parroquias')->upsert($rowsParroquias, ['id_parroquia'], ['id_municipio', 'nombre_parroquia']);
    $this->info('Millennium: parroquias importadas: '.count($rowsParroquias));

    $this->line('Millennium: listo. Si quieres “solo Portuguesa”, luego filtramos por `id_estado` en el importador o hacemos un seed reducido.');

    return 0;
})->purpose('Millennium: importar división político-territorial desde venezuela.sql (estados/ciudades/municipios/parroquias)');
