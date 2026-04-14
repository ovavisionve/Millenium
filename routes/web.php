<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\CuentasPorCobrarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\Maestro\CategoriaController;
use App\Http\Controllers\Maestro\ClienteController;
use App\Http\Controllers\Maestro\DatosMaestrosController;
use App\Http\Controllers\Maestro\ProductoController;
use App\Http\Controllers\Maestro\VendedorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

// Esta ruta define la página de inicio ('/'). Cuando un usuario visita el dominio principal (ej: http://localhost/),
// Laravel ejecuta esta función anónima (closure), que simplemente retorna la vista 'welcome'.
// Es la portada/pantalla de bienvenida por defecto de la aplicación Laravel.

/* Route::get('/', function () { */
/*     return view('welcome'); */
/* }); */

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('usuarios', UserController::class)->except(['show']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('datos-maestros', [DatosMaestrosController::class, 'index'])->name('datos-maestros.index');
    Route::get('vendedores', [VendedorController::class, 'index'])->name('vendedores.index');
    Route::get('vendedores/crear', [VendedorController::class, 'create'])->name('vendedores.create');
    Route::post('vendedores', [VendedorController::class, 'store'])->name('vendedores.store');
    Route::resource('categorias', CategoriaController::class)->except(['show']);
    Route::resource('productos', ProductoController::class)->except(['show']);
    // Millennium: ruta específica antes del resource para no capturarla como {cliente}
    Route::get('clientes/check-documento', [ClienteController::class, 'checkDocumento'])->name('clientes.check-documento');
    // Millennium: ubicación Venezuela (estado → ciudad / municipio → parroquia)
    Route::get('clientes/ciudades', [ClienteController::class, 'ciudades'])->name('clientes.ciudades');
    Route::get('clientes/municipios-por-estado', [ClienteController::class, 'municipiosPorEstado'])->name('clientes.municipios-por-estado');
    Route::get('clientes/parroquias', [ClienteController::class, 'parroquias'])->name('clientes.parroquias');
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::post('facturas/{factura}/verificar', [FacturaController::class, 'verificar'])->name('facturas.verificar');
    Route::get('facturas/canceladas', [FacturaController::class, 'canceladas'])->name('facturas.canceladas');
    Route::get('facturas/{factura}/nota-entrega', [FacturaController::class, 'notaEntrega'])->name('facturas.nota-entrega');
    Route::get('facturas/{factura}/nota-entrega/pdf', [FacturaController::class, 'notaEntregaPdf'])->name('facturas.nota-entrega.pdf');
    Route::get('facturas/{factura}/movimientos-pago.pdf', [FacturaController::class, 'movimientosPagoPdf'])->name('facturas.movimientos-pago.pdf');
    Route::resource('facturas', FacturaController::class);
    Route::get('estados-de-cuenta', fn () => redirect()->route('cuentas-por-cobrar.index'))->name('estados-cuenta.index');
    Route::get('cuentas-por-cobrar', [CuentasPorCobrarController::class, 'index'])->name('cuentas-por-cobrar.index');
    Route::get('cuentas-por-cobrar/{cliente}', [CuentasPorCobrarController::class, 'show'])->name('cuentas-por-cobrar.show');
    Route::get('cuentas-por-cobrar/{cliente}/estado-cuenta.pdf', [CuentasPorCobrarController::class, 'estadoCuentaPdf'])->name('cuentas-por-cobrar.estado-cuenta-pdf');
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('cobranza', [CobranzaController::class, 'index'])->name('cobranza.index');
    Route::get('cobranza/cliente/{cliente}/movimientos-pago.pdf', [CobranzaController::class, 'movimientosPagoPdf'])->name('cobranza.cliente.movimientos-pago.pdf');
    Route::get('cobranza/cliente/{cliente}', [CobranzaController::class, 'cliente'])->name('cobranza.cliente');
    Route::post('cobranza/cliente/{cliente}/pagos', [CobranzaController::class, 'storeCliente'])->name('cobranza.cliente.pagos.store');
    Route::get('cobranza/facturas/{factura}/pagos/nuevo', [CobranzaController::class, 'create'])->name('cobranza.pagos.create');
    Route::post('cobranza/facturas/{factura}/pagos', [CobranzaController::class, 'store'])->name('cobranza.pagos.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
