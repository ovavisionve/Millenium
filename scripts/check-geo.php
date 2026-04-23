<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$estado = DB::table('estados')->where('nombre_estado', 'Lara')->first();
if (! $estado) {
    echo "No existe el estado Lara\n";
    exit(1);
}

$count = DB::table('ciudades')->where('id_estado', $estado->id_estado)->count();
echo "Lara id={$estado->id_estado} ciudades={$count}\n";
