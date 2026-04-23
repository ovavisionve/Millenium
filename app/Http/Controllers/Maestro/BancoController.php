<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBancoRequest;
use App\Http\Requests\UpdateBancoRequest;
use App\Models\Banco;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BancoController extends Controller
{
    public function index(Request $request): View
    {
        $q = Banco::query()->orderBy('nombre');

        if ($request->filled('buscar')) {
            $s = $request->string('buscar');
            $q->where('nombre', 'like', '%'.$s.'%');
        }

        return view('maestros.bancos.index', [
            'bancos' => $q->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('maestros.bancos.create');
    }

    public function store(StoreBancoRequest $request): RedirectResponse
    {
        Banco::create($request->validated());

        return redirect()->route('bancos.index')
            ->with('status', 'Banco creado.');
    }

    public function edit(Banco $banco): View
    {
        return view('maestros.bancos.edit', compact('banco'));
    }

    public function update(UpdateBancoRequest $request, Banco $banco): RedirectResponse
    {
        $banco->update($request->validated());

        return redirect()->route('bancos.index')
            ->with('status', 'Banco actualizado.');
    }

    public function destroy(Banco $banco): RedirectResponse
    {
        $banco->delete();

        return redirect()->route('bancos.index')
            ->with('status', 'Banco eliminado.');
    }
}

