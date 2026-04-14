<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendedorRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Millennium — maestros: vendedores asignables a clientes (usuarios activos con rol adecuado).
 * La creación aquí siempre genera rol «vendedor»; administración completa sigue en Usuarios.
 */
class VendedorController extends Controller
{
    public function index(): View
    {
        return view('maestros.vendedores.index', [
            'vendedores' => User::opcionesVendedor(),
            'roleLabels' => User::roleLabels(),
        ]);
    }

    public function create(): View
    {
        return view('maestros.vendedores.create');
    }

    public function store(StoreVendedorRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['role'] = User::ROLE_VENDEDOR;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['email_verified_at'] = now();

        User::create($data);

        return redirect()->route('vendedores.index')
            ->with('status', 'Vendedor creado. Ya puede asignarse en clientes y reportes.');
    }
}
