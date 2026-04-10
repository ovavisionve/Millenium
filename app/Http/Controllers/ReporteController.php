<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $lineas = null;
        $totales = ['subtotal' => 0.0, 'kg' => 0.0];
        $resumenPorVendedor = collect();
        $resumenPorZona = collect();

        if ($request->boolean('generar')) {
            $base = FacturaLinea::query();
            $this->applyReporteFilters($base, $request);

            $totales['subtotal'] = round((float) (clone $base)->sum('subtotal'), 2);
            $totales['kg'] = round((float) (clone $base)->whereHas('producto', function ($p): void {
                $p->where('unidad', Producto::UNIDAD_KG);
            })->sum('cantidad'), 3);

            $lineas = (clone $base)
                ->with(['factura.cliente.vendedor', 'factura.verificadoPor', 'producto.categoria'])
                ->orderByDesc('id')
                ->paginate(75)
                ->withQueryString();

            $resumenPorVendedor = FacturaLinea::query()
                ->fromSub((clone $base)->select('factura_lineas.*'), 'fl')
                ->join('facturas', 'facturas.id', '=', 'fl.factura_id')
                ->join('clientes', 'clientes.id', '=', 'facturas.cliente_id')
                ->selectRaw('COALESCE(clientes.vendedor_id, 0) as vid')
                ->selectRaw('SUM(fl.subtotal) as total_usd')
                ->groupBy('vid')
                ->orderByDesc('total_usd')
                ->get()
                ->map(function ($row) {
                    $uid = (int) $row->vid;
                    $user = $uid > 0 ? User::find($uid) : null;

                    return [
                        'nombre' => $user?->name ?? 'Sin vendedor',
                        'total_usd' => round((float) $row->total_usd, 2),
                    ];
                });

            $resumenPorZona = FacturaLinea::query()
                ->fromSub((clone $base)->select('factura_lineas.*'), 'fl')
                ->join('facturas', 'facturas.id', '=', 'fl.factura_id')
                ->join('clientes', 'clientes.id', '=', 'facturas.cliente_id')
                ->select('clientes.zona')
                ->selectRaw('SUM(fl.subtotal) as total_usd')
                ->groupBy('clientes.zona')
                ->orderByDesc('total_usd')
                ->get()
                ->map(fn ($row) => [
                    'zona' => $row->zona ?: '—',
                    'total_usd' => round((float) $row->total_usd, 2),
                ]);
        }

        $estadoCuentaFacturas = null;
        $clienteEstado = null;
        if ($request->filled('cuenta_cliente_id')) {
            $clienteEstado = Cliente::with('vendedor')->find($request->integer('cuenta_cliente_id'));
            if ($clienteEstado) {
                $fq = Factura::query()
                    ->with('verificadoPor')
                    ->where('cliente_id', $clienteEstado->id)
                    ->orderByDesc('fecha_emision')
                    ->orderByDesc('id');

                if ($request->boolean('solo_vencidas')) {
                    $hoy = Carbon::today();
                    $fq->whereDate('fecha_vencimiento', '<', $hoy);
                }

                $estadoCuentaFacturas = $fq->get();
            }
        }

        $vendedores = User::query()
            ->where('is_active', true)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_VERIFICADOR, User::ROLE_VENDEDOR])
            ->orderBy('name')
            ->get();

        return view('reportes.index', [
            'lineas' => $lineas,
            'totales' => $totales,
            'resumenPorVendedor' => $resumenPorVendedor,
            'resumenPorZona' => $resumenPorZona,
            'vendedores' => $vendedores,
            'productos' => Producto::query()->where('activo', true)->with('categoria')->orderBy('nombre')->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'clientes' => Cliente::orderBy('nombre_razon_social')->get(),
            'estadoCuentaFacturas' => $estadoCuentaFacturas,
            'clienteEstado' => $clienteEstado,
            'etiquetasCartera' => Factura::etiquetasCartera(),
        ]);
    }

    private function applyReporteFilters(Builder $q, Request $request): void
    {
        $q->whereHas('factura', function ($q) use ($request): void {
            if ($request->filled('desde')) {
                $q->whereDate('fecha_emision', '>=', $request->date('desde'));
            }
            if ($request->filled('hasta')) {
                $q->whereDate('fecha_emision', '<=', $request->date('hasta'));
            }
            if ($request->boolean('solo_sin_verificar')) {
                $q->whereNull('verificado_por');
            }
        });

        $q->whereHas('factura.cliente', function ($q) use ($request): void {
            if ($request->filled('vendedor_id')) {
                $q->where('vendedor_id', $request->integer('vendedor_id'));
            }
            if ($request->filled('zona')) {
                $z = $request->string('zona')->trim()->toString();
                if ($z !== '') {
                    $q->where('zona', 'like', '%'.$z.'%');
                }
            }
        });

        if ($request->filled('producto_id')) {
            $q->where('producto_id', $request->integer('producto_id'));
        }

        if ($request->filled('categoria_id')) {
            $q->whereHas('producto', function ($p) use ($request): void {
                $p->where('categoria_id', $request->integer('categoria_id'));
            });
        }
    }
}
