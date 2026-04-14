<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Millennium — carga inicial de municipios/parroquias (zona predeterminada).
 *
 * Fuente: dumps `municipios.sql` y `parroquias.sql` provistos por el equipo.
 * Nota: este seed carga el set actual (Aragua) tal cual; se puede ampliar luego a otros estados.
 */
class MunicipiosParroquiasSeeder extends Seeder
{
    public function run(): void
    {
        $municipios = [
            ['id_municipio' => 1, 'nombre_municipio' => 'Atanasio Girardot'],
            ['id_municipio' => 2, 'nombre_municipio' => 'Bolivar'],
            ['id_municipio' => 3, 'nombre_municipio' => 'Camatagua'],
            ['id_municipio' => 4, 'nombre_municipio' => 'Francisco Linares Alcentara'],
            ['id_municipio' => 5, 'nombre_municipio' => 'Jose Angel Lamas'],
            ['id_municipio' => 6, 'nombre_municipio' => 'Jose Felix Ribas'],
            ['id_municipio' => 7, 'nombre_municipio' => 'Jose Rafael Revenga'],
            ['id_municipio' => 8, 'nombre_municipio' => 'Libertador'],
            ['id_municipio' => 9, 'nombre_municipio' => 'Mario Briceno Iragorry'],
            ['id_municipio' => 10, 'nombre_municipio' => 'Ocumare de la Costa de Oro'],
            ['id_municipio' => 11, 'nombre_municipio' => 'San Casimiro'],
            ['id_municipio' => 12, 'nombre_municipio' => 'San Sebastien'],
            ['id_municipio' => 13, 'nombre_municipio' => 'Santiago Marino'],
            ['id_municipio' => 14, 'nombre_municipio' => 'Santos Michelena'],
            ['id_municipio' => 15, 'nombre_municipio' => 'Sucre'],
            ['id_municipio' => 16, 'nombre_municipio' => 'Tovar'],
            ['id_municipio' => 17, 'nombre_municipio' => 'Urdaneta'],
            ['id_municipio' => 18, 'nombre_municipio' => 'Zamora'],
        ];

        $parroquias = [
            ['id_parroquia' => 1, 'id_municipio' => 1, 'nombre_parroquia' => 'Pedro Jose Ovalles'],
            ['id_parroquia' => 2, 'id_municipio' => 1, 'nombre_parroquia' => 'Joaquin Crespo'],
            ['id_parroquia' => 3, 'id_municipio' => 1, 'nombre_parroquia' => 'Jose Casanova Godoy'],
            ['id_parroquia' => 4, 'id_municipio' => 1, 'nombre_parroquia' => 'Madre Maria de San Jose'],
            ['id_parroquia' => 5, 'id_municipio' => 1, 'nombre_parroquia' => 'Andres Eloy Blanco'],
            ['id_parroquia' => 6, 'id_municipio' => 1, 'nombre_parroquia' => 'Los Tacarigua'],
            ['id_parroquia' => 7, 'id_municipio' => 1, 'nombre_parroquia' => 'Las Delicias'],
            ['id_parroquia' => 8, 'id_municipio' => 1, 'nombre_parroquia' => 'Choroni'],
            ['id_parroquia' => 9, 'id_municipio' => 2, 'nombre_parroquia' => 'Bolivar'],
            ['id_parroquia' => 10, 'id_municipio' => 3, 'nombre_parroquia' => 'Camatagua'],
            ['id_parroquia' => 11, 'id_municipio' => 3, 'nombre_parroquia' => 'Carmen de Cura'],
            ['id_parroquia' => 12, 'id_municipio' => 4, 'nombre_parroquia' => 'Santa Rita'],
            ['id_parroquia' => 13, 'id_municipio' => 4, 'nombre_parroquia' => 'Francisco de Miranda'],
            ['id_parroquia' => 14, 'id_municipio' => 4, 'nombre_parroquia' => 'Mosenor Feliciano Gonzelez'],
            ['id_parroquia' => 15, 'id_municipio' => 5, 'nombre_parroquia' => 'Santa Cruz'],
            ['id_parroquia' => 16, 'id_municipio' => 6, 'nombre_parroquia' => 'Jose Felix Ribas'],
            ['id_parroquia' => 17, 'id_municipio' => 6, 'nombre_parroquia' => 'Castor Nieves Rios'],
            ['id_parroquia' => 18, 'id_municipio' => 6, 'nombre_parroquia' => 'Las Guacamayas'],
            ['id_parroquia' => 19, 'id_municipio' => 6, 'nombre_parroquia' => 'Pao de Zerate'],
            ['id_parroquia' => 20, 'id_municipio' => 6, 'nombre_parroquia' => 'Zuata'],
            ['id_parroquia' => 21, 'id_municipio' => 7, 'nombre_parroquia' => 'Jose Rafael Revenga'],
            ['id_parroquia' => 22, 'id_municipio' => 8, 'nombre_parroquia' => 'Palo Negro'],
            ['id_parroquia' => 23, 'id_municipio' => 8, 'nombre_parroquia' => 'San Martin de Porres'],
            ['id_parroquia' => 24, 'id_municipio' => 9, 'nombre_parroquia' => 'El Limon'],
            ['id_parroquia' => 25, 'id_municipio' => 9, 'nombre_parroquia' => 'Cana de Azucar'],
            ['id_parroquia' => 26, 'id_municipio' => 10, 'nombre_parroquia' => 'Ocumare de la Costa'],
            ['id_parroquia' => 27, 'id_municipio' => 11, 'nombre_parroquia' => 'San Casimiro'],
            ['id_parroquia' => 28, 'id_municipio' => 11, 'nombre_parroquia' => 'Guiripa'],
            ['id_parroquia' => 29, 'id_municipio' => 11, 'nombre_parroquia' => 'Ollas de Caramacate'],
            ['id_parroquia' => 30, 'id_municipio' => 11, 'nombre_parroquia' => 'Valle Morin'],
            ['id_parroquia' => 31, 'id_municipio' => 12, 'nombre_parroquia' => 'San Sebastian'],
            ['id_parroquia' => 32, 'id_municipio' => 13, 'nombre_parroquia' => 'Turmero'],
            ['id_parroquia' => 33, 'id_municipio' => 13, 'nombre_parroquia' => 'Arevalo Aponte'],
            ['id_parroquia' => 34, 'id_municipio' => 13, 'nombre_parroquia' => 'Chuao'],
            ['id_parroquia' => 35, 'id_municipio' => 13, 'nombre_parroquia' => 'Saman de Guere'],
            ['id_parroquia' => 36, 'id_municipio' => 13, 'nombre_parroquia' => 'Alfredo Pacheco Miranda'],
            ['id_parroquia' => 37, 'id_municipio' => 14, 'nombre_parroquia' => 'Santos Michelena'],
            ['id_parroquia' => 38, 'id_municipio' => 14, 'nombre_parroquia' => 'Tiara'],
            ['id_parroquia' => 39, 'id_municipio' => 15, 'nombre_parroquia' => 'Cagua'],
            ['id_parroquia' => 40, 'id_municipio' => 15, 'nombre_parroquia' => 'Bella Vista'],
            ['id_parroquia' => 41, 'id_municipio' => 16, 'nombre_parroquia' => 'Tovar'],
            ['id_parroquia' => 42, 'id_municipio' => 17, 'nombre_parroquia' => 'Urdaneta'],
            ['id_parroquia' => 43, 'id_municipio' => 17, 'nombre_parroquia' => 'Las Penitas'],
            ['id_parroquia' => 44, 'id_municipio' => 17, 'nombre_parroquia' => 'San Francisco de Cara'],
            ['id_parroquia' => 45, 'id_municipio' => 17, 'nombre_parroquia' => 'Taguay'],
            ['id_parroquia' => 46, 'id_municipio' => 18, 'nombre_parroquia' => 'Zamora'],
            ['id_parroquia' => 47, 'id_municipio' => 18, 'nombre_parroquia' => 'Magdaleno'],
            ['id_parroquia' => 48, 'id_municipio' => 18, 'nombre_parroquia' => 'San Francisco de Asis'],
            ['id_parroquia' => 49, 'id_municipio' => 18, 'nombre_parroquia' => 'Valles de Tucutunemo'],
            ['id_parroquia' => 50, 'id_municipio' => 18, 'nombre_parroquia' => 'Augusto Mijares'],
        ];

        DB::table('municipios')->upsert($municipios, ['id_municipio'], ['nombre_municipio']);
        DB::table('parroquias')->upsert($parroquias, ['id_parroquia'], ['id_municipio', 'nombre_parroquia']);
    }
}
