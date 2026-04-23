<?php

declare(strict_types=1);
use Composer\Autoload\ClassLoader;

require __DIR__.'/../vendor/autoload.php';

$class = 'Database\\Seeders\\EstadosCiudadesSeeder';

/** @var ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

echo "class: {$class}\n";
echo 'findFile: '.var_export($loader->findFile($class), true)."\n";
echo 'class_exists: '.(class_exists($class) ? 'YES' : 'NO')."\n";
