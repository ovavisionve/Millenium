<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $q = Cliente::query()->with('vendedor')->orderBy('nombre_razon_social');

        if ($request->filled('buscar')) {
            $s = $request->string('buscar')->toString();
            $q->whereBuscarTexto($s);
        }

        if ($request->filled('vendedor_id')) {
            $q->where('vendedor_id', $request->integer('vendedor_id'));
        }

        return view('maestros.clientes.index', [
            'clientes' => $q->paginate(15)->withQueryString(),
            'vendedores' => User::opcionesVendedor(),
        ]);
    }

    public function create(): View
    {
        return view('maestros.clientes.create', [
            'vendedores' => User::opcionesVendedor(),
            'tiposDocumento' => Cliente::tiposDocumentoLabels(),
        ]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        Cliente::create($request->validated());

        return redirect()->route('clientes.index')
            ->with('status', 'Cliente registrado.');
    }

    public function edit(Cliente $cliente): View
    {
        return view('maestros.clientes.edit', [
            'cliente' => $cliente,
            'vendedores' => User::opcionesVendedor(),
            'tiposDocumento' => Cliente::tiposDocumentoLabels(),
        ]);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());

        return redirect()->route('clientes.index')
            ->with('status', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('status', 'Cliente eliminado.');
    }
}
