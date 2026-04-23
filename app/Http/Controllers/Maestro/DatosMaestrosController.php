<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Punto de entrada único para maestros (clientes, categorías, vendedores).
 */
class DatosMaestrosController extends Controller
{
    public function index(): View
    {
        return view('maestros.index', [
            'esAdmin' => request()->user()?->isAdmin() ?? false,
        ]);
    }
}
