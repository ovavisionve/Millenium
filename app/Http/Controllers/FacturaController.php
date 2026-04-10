<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacturaRequest;
use App\Http\Requests\UpdateFacturaRequest;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Pago;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function index(Request $request, string $tituloPagina = 'Facturas'): View
    {
        $q = Factura::query()
            ->with(['cliente', 'creadoPor', 'verificadoPor'])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id');

        if ($request->filled('desde')) {
            $q->whereDate('fecha_emision', '>=', $request->date('desde'));
        }
        if ($request->filled('hasta')) {
            $q->whereDate('fecha_emision', '<=', $request->date('hasta'));
        }
        if ($request->filled('cliente_id')) {
            $q->where('cliente_id', $request->integer('cliente_id'));
        }
        if ($request->filled('cartera')) {
            $c = $request->string('cartera')->toString();
            $hoy = Carbon::today();
            $limite = $hoy->copy()->addDays(Factura::DIAS_UMBRAL_POR_VENCER);
            if ($c === Factura::CARTERA_VENCIDA) {
                $q->whereDate('fecha_vencimiento', '<', $hoy);
            } elseif ($c === Factura::CARTERA_POR_VENCER) {
                $q->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->whereDate('fecha_vencimiento', '<=', $limite);
            } elseif ($c === Factura::CARTERA_AL_DIA) {
                $q->whereDate('fecha_vencimiento', '>', $limite);
            }
        }
        if ($request->boolean('solo_sin_verificar')) {
            $q->whereNull('verificado_por');
        }
        if ($request->filled('estado_pago')) {
            $q->where('estado_pago', $request->string('estado_pago')->toString());
        }

        return view('facturas.index', [
            'facturas' => $q->paginate(20)->withQueryString(),
            'etiquetasCartera' => Factura::etiquetasCartera(),
            'clientes' => Cliente::orderBy('nombre_razon_social')->get(),
            'tituloPagina' => $tituloPagina,
        ]);
    }

    /** Historial de facturas totalmente pagadas (cómo se canceló: ver detalle y movimientos). */
    public function canceladas(Request $request): View
    {
        $request->merge(['estado_pago' => Factura::ESTADO_PAGO_PAGADA]);

        return $this->index($request, 'Documentos cancelados');
    }

    public function create(): View
    {
        return view('facturas.create', [
            'clientes' => Cliente::orderBy('nombre_razon_social')->get(),
            'productos' => Producto::query()->where('activo', true)->with('categoria')->orderBy('nombre')->get(),
            'lineaVacia' => [
                'producto_id' => '',
                'cantidad' => '',
                'precio_unitario' => '',
            ],
        ]);
    }

    public function store(StoreFacturaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $data = $this->totalesDesdeLineas($validated['lineas']);
        $fechaEmision = Carbon::parse($validated['fecha_emision'])->startOfDay();
        $fechaVenc = $fechaEmision->copy()->addDays((int) $validated['dias_credito']);

        DB::transaction(function () use ($request, $validated, $data, $fechaEmision, $fechaVenc) {
            $factura = Factura::create([
                'cliente_id' => $validated['cliente_id'],
                'numero_factura' => $validated['numero_factura'] ?? null,
                'fecha_emision' => $fechaEmision,
                'dias_credito' => (int) $validated['dias_credito'],
                'fecha_vencimiento' => $fechaVenc,
                'total' => $data['total'],
                'saldo_pendiente' => $data['total'],
                'estado_pago' => Factura::ESTADO_PAGO_ABIERTA,
                'creado_por' => $request->user()->id,
            ]);

            foreach ($data['lineas'] as $linea) {
                FacturaLinea::create([
                    'factura_id' => $factura->id,
                    'producto_id' => $linea['producto_id'],
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);
            }
        });

        return redirect()->route('facturas.index')
            ->with('status', 'Factura registrada correctamente.');
    }

    public function show(Factura $factura): View
    {
        $factura->load(['cliente.vendedor', 'lineas.producto.categoria', 'creadoPor', 'verificadoPor', 'pagos.registradoPor']);

        return view('facturas.show', [
            'factura' => $factura,
            'etiquetasCartera' => Factura::etiquetasCartera(),
            'tiposTasaPago' => Pago::tiposTasa(),
            'metodosPago' => Pago::metodosPago(),
        ]);
    }

    public function edit(Factura $factura): View
    {
        $factura->load('lineas.producto');

        $lineasForm = old('lineas', $factura->lineas->map(fn ($l) => [
            'producto_id' => (string) $l->producto_id,
            'cantidad' => (string) $l->cantidad,
            'precio_unitario' => (string) $l->precio_unitario,
        ])->values()->all());

        return view('facturas.edit', [
            'factura' => $factura,
            'lineasForm' => $lineasForm,
            'clientes' => Cliente::orderBy('nombre_razon_social')->get(),
            'productos' => Producto::query()->where('activo', true)->with('categoria')->orderBy('nombre')->get(),
        ]);
    }

    public function update(UpdateFacturaRequest $request, Factura $factura): RedirectResponse
    {
        $validated = $request->validated();
        $data = $this->totalesDesdeLineas($validated['lineas']);
        $fechaEmision = Carbon::parse($validated['fecha_emision'])->startOfDay();
        $fechaVenc = $fechaEmision->copy()->addDays((int) $validated['dias_credito']);

        DB::transaction(function () use ($validated, $factura, $data, $fechaEmision, $fechaVenc) {
            $factura->lineas()->delete();

            $nuevoTotal = $data['total'];
            $totalPagado = round((float) $factura->total - (float) $factura->saldo_pendiente, 2);
            $nuevoSaldo = round(max(0, $nuevoTotal - $totalPagado), 2);
            $estadoPago = $nuevoSaldo <= 0 ? Factura::ESTADO_PAGO_PAGADA : Factura::ESTADO_PAGO_ABIERTA;

            $factura->update([
                'cliente_id' => $validated['cliente_id'],
                'numero_factura' => $validated['numero_factura'] ?? null,
                'fecha_emision' => $fechaEmision,
                'dias_credito' => (int) $validated['dias_credito'],
                'fecha_vencimiento' => $fechaVenc,
                'total' => $nuevoTotal,
                'saldo_pendiente' => $nuevoSaldo,
                'estado_pago' => $estadoPago,
                'verificado_por' => null,
                'fecha_verificacion' => null,
            ]);

            foreach ($data['lineas'] as $linea) {
                FacturaLinea::create([
                    'factura_id' => $factura->id,
                    'producto_id' => $linea['producto_id'],
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);
            }
        });

        return redirect()->route('facturas.show', $factura)
            ->with('status', 'Factura actualizada.');
    }

    public function destroy(Factura $factura): RedirectResponse
    {
        if ($factura->pagos()->exists()) {
            return redirect()->route('facturas.show', $factura)
                ->with('status', 'No se puede eliminar una factura con pagos registrados.');
        }

        $factura->delete();

        return redirect()->route('facturas.index')
            ->with('status', 'Factura eliminada.');
    }

    public function verificar(Request $request, Factura $factura): RedirectResponse
    {
        $user = $request->user();
        if (! $factura->puedeVerificar($user)) {
            abort(403, 'No puedes verificar esta factura.');
        }

        $factura->update([
            'verificado_por' => $user->id,
            'fecha_verificacion' => now(),
        ]);

        return redirect()->route('facturas.show', $factura)
            ->with('status', 'Precios verificados (Fatimar) con el usuario '.$user->name.'. Queda registrado en el reporte y en el detalle de la factura.');
    }

    /**
     * @param  array<int, array{producto_id: int|string, cantidad: float|int|string, precio_unitario: float|int|string}>  $lineasInput
     * @return array{total: float, lineas: list<array{producto_id: int, cantidad: string, precio_unitario: string, subtotal: float}>}
     */
    private function totalesDesdeLineas(array $lineasInput): array
    {
        $lineas = [];
        $total = 0.0;

        foreach ($lineasInput as $row) {
            $cant = (float) $row['cantidad'];
            $pu = (float) $row['precio_unitario'];
            $sub = round($cant * $pu, 2);
            $total += $sub;
            $lineas[] = [
                'producto_id' => (int) $row['producto_id'],
                'cantidad' => number_format($cant, 3, '.', ''),
                'precio_unitario' => number_format($pu, 4, '.', ''),
                'subtotal' => $sub,
            ];
        }

        $total = round($total, 2);

        return ['total' => $total, 'lineas' => $lineas];
    }
}
