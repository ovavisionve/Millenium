<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacturaRequest;
use App\Http\Requests\UpdateFacturaRequest;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Pago;
use App\Models\User;
use App\Support\MovimientosPagoInforme;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FacturaController extends Controller
{
    /** @var list<string> */
    private const ALCANCES_FACTURAS = ['vigentes', 'canceladas', 'todas'];

    public function __construct()
    {
        $this->authorizeResource(Factura::class, 'factura');
    }

    public function index(Request $request): View
    {
        $alcance = $request->string('alcance')->toString();
        if (! in_array($alcance, self::ALCANCES_FACTURAS, true)) {
            $alcance = 'vigentes';
        }

        $user = $request->user();

        $q = Factura::query()
            ->with(['cliente', 'creadoPor', 'verificadoPor'])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id');

        if ($user?->esVendedorRestringido()) {
            $q->where('vendedor_id', $user->id);
        }

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
        } elseif ($alcance === 'vigentes') {
            $q->where('estado_pago', Factura::ESTADO_PAGO_ABIERTA);
        } elseif ($alcance === 'canceladas') {
            $q->where('estado_pago', Factura::ESTADO_PAGO_PAGADA);
        }

        $tituloPagina = match ($alcance) {
            'vigentes' => $user?->puedeGestionOperativaCompleta()
                ? 'Facturas vigentes y cobranza'
                : 'Facturas vigentes',
            'canceladas' => 'Historial — facturas canceladas',
            default => 'Facturas (todas)',
        };

        $clientes = Cliente::query()
            ->when($user?->esVendedorRestringido(), function ($cq) use ($user): void {
                $cq->whereHas('facturas', fn ($fq) => $fq->where('vendedor_id', $user->id));
            })
            ->orderBy('nombre_razon_social')
            ->get();

        return view('facturas.index', [
            'facturas' => $q->paginate(20)->withQueryString(),
            'etiquetasCartera' => Factura::etiquetasCartera(),
            'clientes' => $clientes,
            'tituloPagina' => $tituloPagina,
            'alcance' => $alcance,
        ]);
    }

    /** Compatibilidad: enlaces viejos y marcadores → misma pantalla con pestaña historial. */
    public function canceladas(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Factura::class);

        return redirect()->route('facturas.index', array_merge(
            $request->except('alcance'),
            ['alcance' => 'canceladas']
        ));
    }

    public function create(): View
    {
        $clientes = Cliente::query()
            ->with(['vendedor', 'estado', 'ciudad', 'municipio', 'parroquia'])
            ->orderBy('nombre_razon_social')
            ->get();

        return view('facturas.create', [
            'clientes' => $clientes,
            'clientesResumen' => $this->clientesResumenParaFactura($clientes),
            'vendedores' => User::opcionesVendedor(),
            'metodosPagoFactura' => Pago::metodosPago(),
            'siguienteNumeroFactura' => Factura::vistaPreviaSiguienteNumero(),
            'categorias' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'lineaVacia' => [
                'categoria_id' => '',
                'cantidad_animales' => '',
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

        $factura = DB::transaction(function () use ($request, $validated, $data, $fechaEmision, $fechaVenc) {
            $factura = Factura::create([
                'cliente_id' => $validated['cliente_id'],
                'vendedor_id' => $validated['vendedor_id'],
                'numero_factura' => $validated['numero_factura'],
                'fecha_emision' => $fechaEmision,
                'dias_credito' => (int) $validated['dias_credito'],
                'metodo_pago_previsto' => $validated['metodo_pago_previsto'],
                'observaciones' => $validated['observaciones'],
                'fecha_vencimiento' => $fechaVenc,
                'total' => $data['total'],
                'saldo_pendiente' => $data['total'],
                'estado_pago' => Factura::ESTADO_PAGO_ABIERTA,
                'creado_por' => $request->user()->id,
            ]);

            foreach ($data['lineas'] as $linea) {
                FacturaLinea::create([
                    'factura_id' => $factura->id,
                    'categoria_id' => $linea['categoria_id'],
                    'cantidad_animales' => $linea['cantidad_animales'],
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);
            }

            return $factura->load(['cliente', 'lineas.categoria']);
        });

        return redirect()->route('facturas.show', $factura)
            ->with('status', 'Factura registrada correctamente. Podés imprimir o enviar el documento de deuda al cliente.')
            ->with('abrir_nota_entrega', true);
    }

    /** Pantalla imprimible / envío al cliente (referencia a la factura). */
    public function notaEntrega(Factura $factura): View
    {
        $this->authorize('view', $factura);

        $factura->load(['cliente', 'vendedor', 'lineas.categoria']);

        return view('facturas.nota-entrega', ['factura' => $factura]);
    }

    public function notaEntregaPdf(Factura $factura): Response
    {
        $this->authorize('view', $factura);

        $factura->load(['cliente', 'vendedor', 'lineas.categoria']);
        $num = $factura->numero_factura ?? (string) $factura->id;
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $num);
        $pdf = Pdf::loadView('pdf.nota-entrega', ['factura' => $factura])->setPaper('a4');

        return $pdf->download('deuda-'.$slug.'.pdf');
    }

    /** PDF comprobante de abonos (estilo operación) para una factura; opcional filtro por fecha de recibo. */
    public function movimientosPagoPdf(Request $request, Factura $factura): Response
    {
        $this->authorize('viewPagos', $factura);

        $factura->load('cliente');

        $desde = $request->filled('desde') ? $request->date('desde')->startOfDay() : null;
        $hasta = $request->filled('hasta') ? $request->date('hasta')->endOfDay() : null;

        $pagos = Pago::query()
            ->where('factura_id', $factura->id)
            ->with(['registradoPor', 'factura'])
            ->when($desde, fn ($q) => $q->whereDate('fecha_recibo', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_recibo', '<=', $hasta))
            ->orderBy('fecha_recibo')
            ->orderBy('id')
            ->get();

        $periodo = MovimientosPagoInforme::textoPeriodoRecibos($desde, $hasta);
        $data = MovimientosPagoInforme::datosParaVistaPdf($factura->cliente, collect([$factura]), $pagos, $periodo);
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) ($factura->numero_factura ?? $factura->id));
        $pdf = Pdf::loadView('pdf.movimientos-pago', $data)->setPaper('a4', 'landscape');

        return $pdf->download('movimientos-pago-factura-'.$slug.'.pdf');
    }

    public function show(Factura $factura): View
    {
        $user = request()->user();
        $with = ['cliente.vendedor', 'vendedor', 'lineas.categoria', 'creadoPor', 'verificadoPor'];
        if ($user && $user->can('viewPagos', $factura)) {
            $with[] = 'pagos.registradoPor';
        }
        $factura->load($with);

        return view('facturas.show', [
            'factura' => $factura,
            'etiquetasCartera' => Factura::etiquetasCartera(),
            'tiposTasaPago' => Pago::tiposTasa(),
            'metodosPago' => Pago::metodosPago(),
        ]);
    }

    public function edit(Factura $factura): View
    {
        $factura->load('lineas.categoria');

        $lineasForm = old('lineas', $factura->lineas->map(fn ($l) => [
            'categoria_id' => (string) $l->categoria_id,
            'cantidad_animales' => $l->cantidad_animales !== null ? (string) $l->cantidad_animales : '',
            'cantidad' => (string) $l->cantidad,
            'precio_unitario' => (string) $l->precio_unitario,
        ])->values()->all());

        $clientes = Cliente::query()
            ->with(['vendedor', 'estado', 'ciudad', 'municipio', 'parroquia'])
            ->orderBy('nombre_razon_social')
            ->get();

        return view('facturas.edit', [
            'factura' => $factura,
            'lineasForm' => $lineasForm,
            'clientes' => $clientes,
            'clientesResumen' => $this->clientesResumenParaFactura($clientes),
            'vendedores' => User::opcionesVendedor(),
            'metodosPagoFactura' => Pago::metodosPago(),
            'categorias' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
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
                'vendedor_id' => $validated['vendedor_id'],
                'numero_factura' => $validated['numero_factura'],
                'fecha_emision' => $fechaEmision,
                'dias_credito' => (int) $validated['dias_credito'],
                'metodo_pago_previsto' => $validated['metodo_pago_previsto'],
                'observaciones' => $validated['observaciones'],
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
                    'categoria_id' => $linea['categoria_id'],
                    'cantidad_animales' => $linea['cantidad_animales'],
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
        $this->authorize('verificar', $factura);

        $user = $request->user();

        $factura->update([
            'verificado_por' => $user->id,
            'fecha_verificacion' => now(),
        ]);

        return redirect()->route('facturas.show', $factura)
            ->with('status', 'Verificación registrada con el usuario '.$user->name.'. Queda registrado en el reporte y en el detalle de la factura.');
    }

    /**
     * @param  array<int, array{categoria_id: int|string, cantidad_animales?: int|null, cantidad: float|int|string, precio_unitario: float|int|string}>  $lineasInput
     * @return array{total: float, lineas: list<array{categoria_id: int, cantidad_animales: int|null, cantidad: string, precio_unitario: string, subtotal: float}>}
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
            $anim = $row['cantidad_animales'] ?? null;
            $lineas[] = [
                'categoria_id' => (int) $row['categoria_id'],
                'cantidad_animales' => $anim !== null && $anim !== '' ? (int) $anim : null,
                'cantidad' => number_format($cant, 3, '.', ''),
                'precio_unitario' => number_format($pu, 4, '.', ''),
                'subtotal' => $sub,
            ];
        }

        $total = round($total, 2);

        return ['total' => $total, 'lineas' => $lineas];
    }

    /**
     * @param  Collection<int, Cliente>  $clientes
     * @return array<string, array{nombre: string, rif: string, email: string|null, telefono: string|null, direccion: string|null, vendedor: string|null, vendedor_id: string, zona: string|null, ubicacion: string|null}>
     */
    private function clientesResumenParaFactura(Collection $clientes): array
    {
        return $clientes->mapWithKeys(function (Cliente $c): array {
            $ubicacion = collect([
                $c->estado?->nombre_estado,
                $c->ciudad?->nombre_ciudad,
                $c->municipio?->nombre_municipio,
                $c->parroquia?->nombre_parroquia,
            ])->filter()->implode(' · ');

            return [
                (string) $c->id => [
                    'nombre' => $c->nombre_razon_social,
                    'rif' => $c->full_identificacion,
                    'email' => $c->email ? trim((string) $c->email) : null,
                    'telefono' => $c->telefono ? trim((string) $c->telefono) : null,
                    'direccion' => $c->direccion ? trim((string) $c->direccion) : null,
                    'vendedor' => $c->vendedor?->name,
                    'vendedor_id' => $c->vendedor_id !== null ? (string) $c->vendedor_id : '',
                    'zona' => $c->zona ? trim((string) $c->zona) : null,
                    'ubicacion' => $ubicacion !== '' ? $ubicacion : null,
                ],
            ];
        })->all();
    }
}
