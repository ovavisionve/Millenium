<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $q = Categoria::query()->orderBy('nombre');

        if ($request->filled('buscar')) {
            $s = $request->string('buscar');
            $q->where(function ($query) use ($s) {
                $query->where('nombre', 'like', '%'.$s.'%')
                    ->orWhere('codigo', 'like', '%'.$s.'%');
            });
        }

        return view('maestros.categorias.index', [
            'categorias' => $q->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('maestros.categorias.create');
    }

    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        Categoria::create($request->validated());

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría creada.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('maestros.categorias.edit', compact('categoria'));
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return redirect()->route('categorias.index')
                ->withErrors(['error' => 'No se puede eliminar: hay productos usando esta categoría.']);
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('status', 'Categoría eliminada.');
    }
}
