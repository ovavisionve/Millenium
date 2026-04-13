<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\Maestro\CategoriaController;
use App\Http\Controllers\Maestro\ClienteController;
use App\Http\Controllers\Maestro\ProductoController;
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
    Route::resource('categorias', CategoriaController::class)->except(['show']);
    Route::resource('productos', ProductoController::class)->except(['show']);
    // Millennium: ruta específica antes del resource para no capturarla como {cliente}
    Route::get('clientes/check-documento', [ClienteController::class, 'checkDocumento'])->name('clientes.check-documento');
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::post('facturas/{factura}/verificar', [FacturaController::class, 'verificar'])->name('facturas.verificar');
    Route::get('facturas/canceladas', [FacturaController::class, 'canceladas'])->name('facturas.canceladas');
    Route::resource('facturas', FacturaController::class);
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('cobranza', [CobranzaController::class, 'index'])->name('cobranza.index');
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
