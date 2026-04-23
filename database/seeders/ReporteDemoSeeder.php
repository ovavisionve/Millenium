<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ReporteDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Base: ubicación, admins, categorías y bancos (idempotente).
        $this->call(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@millennium.local')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $admin) {
            throw new \RuntimeException('No existe ningún usuario. Ejecuta migraciones/seeders base primero.');
        }

        // Vendedores para filtros / reportes.
        $vendedores = collect([
            ['email' => 'vendedor1@millennium.local', 'name' => 'Vendedor 1 (demo)', 'role' => User::ROLE_VENDEDOR_NORMAL],
            ['email' => 'vendedor2@millennium.local', 'name' => 'Vendedor 2 (demo)', 'role' => User::ROLE_VENDEDOR_NORMAL],
            ['email' => 'verificador@millennium.local', 'name' => 'Verificador (demo)', 'role' => User::ROLE_VERIFICADOR],
        ])->map(function (array $u) {
            return User::query()->updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => 'Millennium2026!',
                    'role' => $u['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        })->values();

        // Asegurar al menos 1 categoría en kg para probar el total KG del reporte.
        Categoria::query()->updateOrCreate(
            ['codigo' => 'KG'],
            [
                'nombre' => 'Carne (kg)',
                'descripcion' => 'Categoría de prueba con unidad kg para reportes.',
                'unidad' => Categoria::UNIDAD_KG,
                'activo' => true,
            ]
        );

        // Asegurar bancos típicos para selects.
        Banco::query()->updateOrCreate(['nombre' => 'Banesco'], ['descripcion' => 'Banco demo', 'activo' => true]);

        $categorias = Categoria::query()->where('activo', true)->orderBy('id')->get();
        $bancos = Banco::query()->where('activo', true)->orderBy('id')->get();

        // IDs de ubicación (opcionales, pero ayudan a pruebas de búsqueda/filtros).
        $estadoIds = DB::table('estados')->pluck('id_estado')->all();
        $ciudadIds = DB::table('ciudades')->pluck('id_ciudad')->all();
        $municipioIds = DB::table('municipios')->pluck('id_municipio')->all();
        $parroquiaIds = DB::table('parroquias')->pluck('id_parroquia')->all();

        $zonas = [
            'Centro',
            'Acarigua',
            'Araure',
            'Guanare',
            'Biscucuy',
            'Píritu',
            '—', // simula texto extraño
            null, // sin zona
        ];

        // Crear/actualizar clientes demo (12).
        $clientes = collect(range(1, 12))->map(function (int $i) use ($zonas, $vendedores, $estadoIds, $ciudadIds, $municipioIds, $parroquiaIds) {
            $tipo = 'J';
            $doc = 'DEMO'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $zona = $zonas[array_rand($zonas)];
            $vend = $vendedores->random();

            return Cliente::query()->updateOrCreate(
                ['tipo_documento' => $tipo, 'documento_numero' => $doc],
                [
                    'nombre_razon_social' => 'Cliente Demo '.$i,
                    'email' => 'cliente'.$i.'@demo.local',
                    'direccion' => 'Dirección demo '.$i,
                    'telefono' => '0412'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'zona' => $zona === '—' ? '' : $zona,
                    'vendedor_id' => $vend->id,
                    'id_estado' => $estadoIds !== [] ? $estadoIds[array_rand($estadoIds)] : null,
                    'id_ciudad' => $ciudadIds !== [] ? $ciudadIds[array_rand($ciudadIds)] : null,
                    'id_municipio' => $municipioIds !== [] ? $municipioIds[array_rand($municipioIds)] : null,
                    'id_parroquia' => $parroquiaIds !== [] ? $parroquiaIds[array_rand($parroquiaIds)] : null,
                ]
            );
        })->values();

        $metodos = array_keys(Pago::metodosPago());
        $tiposTasa = array_keys(Pago::tiposTasa());

        $hoy = Carbon::today();
        $verificador = $vendedores->firstWhere('email', 'verificador@millennium.local') ?? $admin;

        // Re-ejecutable: borra data demo anterior (facturas/lineas/pagos) de clientes DEMO*.
        // Nota: no borra maestros (clientes/categorías/usuarios), solo documentos de venta.
        $demoClienteIds = $clientes->pluck('id')->all();
        if (! empty($demoClienteIds)) {
            DB::transaction(function () use ($demoClienteIds): void {
                $facturaIds = DB::table('facturas')->whereIn('cliente_id', $demoClienteIds)->pluck('id')->all();
                if (empty($facturaIds)) {
                    return;
                }
                DB::table('pagos')->whereIn('factura_id', $facturaIds)->delete();
                DB::table('factura_lineas')->whereIn('factura_id', $facturaIds)->delete();
                DB::table('facturas')->whereIn('id', $facturaIds)->delete();
            });
        }

        // Facturas DEMO determinísticas (casos para probar filtros) + facturas aleatorias para volumen.
        DB::transaction(function () use (
            $clientes,
            $vendedores,
            $categorias,
            $bancos,
            $admin,
            $metodos,
            $tiposTasa,
            $hoy,
            $verificador
        ): void {
            $catKg = $categorias->firstWhere('unidad', Categoria::UNIDAD_KG) ?? $categorias->first();
            $catUd = $categorias->firstWhere('unidad', Categoria::UNIDAD_UNIDAD) ?? $categorias->first();

            $vend1 = $vendedores->firstWhere('email', 'vendedor1@millennium.local') ?? $vendedores->first();
            $vend2 = $vendedores->firstWhere('email', 'vendedor2@millennium.local') ?? $vendedores->last();

            $mkFactura = function (
                Cliente $cliente,
                ?User $vendedor,
                Carbon $fechaEmision,
                int $diasCredito,
                bool $verificada,
                bool $pagada,
                bool $pagoParcial
            ) use ($admin, $metodos, $tiposTasa, $bancos, $catKg, $catUd, $verificador): Factura {
                $fechaVenc = $fechaEmision->copy()->addDays($diasCredito);
                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'vendedor_id' => $vendedor?->id,
                    'numero_factura' => Factura::generarNumeroFactura(),
                    'fecha_emision' => $fechaEmision,
                    'dias_credito' => $diasCredito,
                    'metodo_pago_previsto' => $metodos[array_rand($metodos)],
                    'observaciones' => 'DEMO-SEED reportes',
                    'fecha_vencimiento' => $fechaVenc,
                    'total' => 0,
                    'saldo_pendiente' => 0,
                    'estado_pago' => Factura::ESTADO_PAGO_ABIERTA,
                    'creado_por' => $admin->id,
                ]);

                // 2 líneas fijas: 1 en kg y 1 en unidad (para totales kg y subtotal).
                $total = 0.0;
                foreach ([$catKg, $catUd] as $cat) {
                    if (! $cat) {
                        continue;
                    }
                    $esKg = $cat->unidad === Categoria::UNIDAD_KG;
                    $cantidad = $esKg ? 100.500 : 2.000;
                    $precio = $esKg ? 6.2500 : 250.0000;
                    $subtotal = round($cantidad * $precio, 2);
                    $total += $subtotal;

                    FacturaLinea::create([
                        'factura_id' => $factura->id,
                        'categoria_id' => $cat->id,
                        'cantidad_animales' => $cat->unidad === Categoria::UNIDAD_UNIDAD ? 2 : null,
                        'cantidad' => number_format($cantidad, 3, '.', ''),
                        'precio_unitario' => number_format($precio, 4, '.', ''),
                        'subtotal' => $subtotal,
                    ]);
                }

                $total = round($total, 2);
                $factura->update([
                    'total' => $total,
                    'saldo_pendiente' => $total,
                    'estado_pago' => Factura::ESTADO_PAGO_ABIERTA,
                ]);

                if ($pagoParcial) {
                    $monto = round($total * 0.4, 2);
                    $this->crearPagoDemo($factura, $admin->id, $bancos, $monto, $tiposTasa);
                    $factura->refresh();
                }

                if ($pagada) {
                    $factura->refresh();
                    $resta = round((float) $factura->saldo_pendiente, 2);
                    if ($resta > 0) {
                        $this->crearPagoDemo($factura, $admin->id, $bancos, $resta, $tiposTasa);
                        $factura->refresh();
                    }
                }

                if ($verificada) {
                    $factura->update([
                        'verificado_por' => $verificador->id,
                        'fecha_verificacion' => $fechaEmision->copy()->addHours(10),
                    ]);
                }

                return $factura;
            };

            // Casos clave para probar filtros (fechas, vendedor, estado pago, verificación):
            // A) Hoy, abierta, sin verificar, vendedor 1
            $mkFactura($clientes[0], $vend1, $hoy->copy(), 0, false, false, false);
            // B) Ayer, abierta, verificada, vendedor 1
            $mkFactura($clientes[1], $vend1, $hoy->copy()->subDay(), 7, true, false, false);
            // C) Hoy, pagada, verificada, vendedor 2
            $mkFactura($clientes[2], $vend2, $hoy->copy(), 0, true, true, false);
            // D) Hace 10 días, pagada, sin verificar, vendedor 2
            $mkFactura($clientes[3], $vend2, $hoy->copy()->subDays(10), 15, false, true, false);
            // E) Hace 20 días, abierta con pago parcial, sin verificar, vendedor 2
            $mkFactura($clientes[4], $vend2, $hoy->copy()->subDays(20), 30, false, false, true);
            // F) Hace 40 días, pagada, verificada, vendedor 1
            $mkFactura($clientes[5], $vend1, $hoy->copy()->subDays(40), 15, true, true, false);

            // Volumen extra (aleatorio) para que el reporte no esté vacío en otros cortes.
            for ($i = 1; $i <= 20; $i++) {
                $cliente = $clientes->random();
                $vendedor = (random_int(1, 100) <= 80) ? $cliente->vendedor : $vendedores->random();

                $diasAtras = random_int(1, 75);
                $fechaEmision = $hoy->copy()->subDays($diasAtras);
                $diasCredito = [0, 7, 15, 30][array_rand([0, 7, 15, 30])];
                $fechaVenc = $fechaEmision->copy()->addDays($diasCredito);

                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'vendedor_id' => $vendedor?->id,
                    'numero_factura' => Factura::generarNumeroFactura(),
                    'fecha_emision' => $fechaEmision,
                    'dias_credito' => $diasCredito,
                    'metodo_pago_previsto' => $metodos[array_rand($metodos)],
                    'observaciones' => ($i % 5 === 0) ? 'Demo: caso especial para reportes #'.$i : null,
                    'fecha_vencimiento' => $fechaVenc,
                    'total' => 0,
                    'saldo_pendiente' => 0,
                    'estado_pago' => Factura::ESTADO_PAGO_ABIERTA,
                    'creado_por' => $admin->id,
                ]);

                // Líneas: 1–3.
                $lineas = random_int(1, 3);
                $total = 0.0;
                for ($l = 1; $l <= $lineas; $l++) {
                    $cat = $categorias->random();
                    $esKg = $cat->unidad === Categoria::UNIDAD_KG;

                    $cantidad = $esKg
                        ? round(random_int(40, 280) + random_int(0, 999) / 1000, 3) // 40–280.999 kg
                        : round(random_int(1, 6), 3); // 1–6 ud

                    $precio = $esKg
                        ? round(random_int(4, 12) + random_int(0, 9999) / 10000, 4) // $4–$12/kg
                        : round(random_int(180, 650) + random_int(0, 9999) / 10000, 4); // $180–$650 por animal/ud

                    $subtotal = round($cantidad * $precio, 2);
                    $total += $subtotal;

                    FacturaLinea::create([
                        'factura_id' => $factura->id,
                        'categoria_id' => $cat->id,
                        'cantidad_animales' => $cat->unidad === Categoria::UNIDAD_UNIDAD ? random_int(1, 6) : null,
                        'cantidad' => number_format($cantidad, 3, '.', ''),
                        'precio_unitario' => number_format($precio, 4, '.', ''),
                        'subtotal' => $subtotal,
                    ]);
                }

                $total = round($total, 2);
                $factura->update([
                    'total' => $total,
                    'saldo_pendiente' => $total,
                    'estado_pago' => Factura::ESTADO_PAGO_ABIERTA,
                ]);

                // Escenarios de pago:
                // - 1..6: sin pagos
                // - 7..12: pago parcial (1 pago)
                // - 13..20: pagada (1–3 pagos)
                if ($i <= 6) {
                    continue;
                }

                if ($i <= 12) {
                    $monto = round($total * (random_int(25, 70) / 100), 2);
                    $this->crearPagoDemo($factura, $admin->id, $bancos, $monto, $tiposTasa);
                    $factura->refresh();
                    continue;
                }

                $resta = $total;
                $pagosN = random_int(1, 3);
                for ($p = 1; $p <= $pagosN; $p++) {
                    $monto = ($p === $pagosN)
                        ? $resta
                        : round(max(1, $resta * (random_int(30, 60) / 100)), 2);
                    $monto = min($monto, $resta);
                    $this->crearPagoDemo($factura, $admin->id, $bancos, $monto, $tiposTasa);
                    $factura->refresh();
                    $resta = round((float) $factura->saldo_pendiente, 2);
                    if ($resta <= 0) {
                        break;
                    }
                }
            }
        });
    }

    /**
     * Crea un pago y aplica el abono al saldo.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Banco>  $bancos
     * @param  array<int, string>  $tiposTasa
     */
    private function crearPagoDemo(Factura $factura, int $registradoPor, $bancos, float $montoUsd, array $tiposTasa): void
    {
        $metodo = [
            Pago::METODO_ZELLE,
            Pago::METODO_TRANSFERENCIA,
            Pago::METODO_EFECTIVO,
            Pago::METODO_PAGO_MOVIL,
            Pago::METODO_USDT,
        ][array_rand([
            Pago::METODO_ZELLE,
            Pago::METODO_TRANSFERENCIA,
            Pago::METODO_EFECTIVO,
            Pago::METODO_PAGO_MOVIL,
            Pago::METODO_USDT,
        ])];

        $tipoTasa = $tiposTasa[array_rand($tiposTasa)];
        $valorTasa = ($metodo === Pago::METODO_PAGO_MOVIL)
            ? round(random_int(30, 60) + random_int(0, 9999) / 10000, 4)
            : (float) config('millennium.cobranza_tasa_placeholder_divisa', 1);

        $montoBs = ($metodo === Pago::METODO_PAGO_MOVIL)
            ? round($montoUsd * $valorTasa, 2)
            : null;

        $banco = $bancos->isNotEmpty() ? $bancos->random() : null;
        $fechaRecibo = $factura->fecha_emision->copy()->addDays(random_int(0, 12));

        Pago::create([
            'factura_id' => $factura->id,
            'fecha_recibo' => $fechaRecibo,
            'monto_aplicado_usd' => $montoUsd,
            'tipo_tasa' => $tipoTasa,
            'valor_tasa' => $valorTasa,
            'monto_bs' => $montoBs,
            'metodo_pago' => $metodo,
            'estado_validacion_banco' => ($metodo === Pago::METODO_PAGO_MOVIL) ? Pago::VALIDACION_BANCO_PENDIENTE : null,
            'referencia' => strtoupper('REF'.random_int(100000, 999999)),
            'banco_destino' => $banco?->nombre,
            'recibido_por' => ($metodo === Pago::METODO_EFECTIVO) ? 'Caja demo' : null,
            'comprobante_path' => null,
            'notas' => 'Demo seed',
            'registrado_por' => $registradoPor,
        ]);

        $factura->aplicarAbonoUsd($montoUsd);
    }
}

