<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $q = Producto::query()->with('categoria')->orderBy('nombre');

        if ($request->filled('buscar')) {
            $s = $request->string('buscar');
            $q->where(function ($query) use ($s) {
                $query->where('nombre', 'like', '%'.$s.'%')
                    ->orWhere('codigo', 'like', '%'.$s.'%');
            });
        }

        if ($request->filled('categoria_id')) {
            $q->where('categoria_id', $request->integer('categoria_id'));
        }

        return view('maestros.productos.index', [
            'productos' => $q->paginate(15)->withQueryString(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'unidadLabels' => Producto::unidadLabels(),
        ]);
    }

    public function create(): View
    {
        return view('maestros.productos.create', [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'unidades' => Producto::$unidades,
            'unidadLabels' => Producto::unidadLabels(),
        ]);
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');

        Producto::create($data);

        return redirect()->route('productos.index')
            ->with('status', 'Producto creado.');
    }

    public function edit(Producto $producto): View
    {
        return view('maestros.productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'unidades' => Producto::$unidades,
            'unidadLabels' => Producto::unidadLabels(),
        ]);
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $data = $request->validated();
        $data['activo'] = $request->boolean('activo');

        $producto->update($data);

        return redirect()->route('productos.index')
            ->with('status', 'Producto actualizado.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('status', 'Producto eliminado.');
    }
}
