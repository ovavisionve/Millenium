<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReporteController extends Controller
{
    /** Máximo de líneas permitidas en un PDF (evita timeouts / archivos enormes). */
    private const REPORTE_PDF_MAX_LINEAS = 3500;

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->boolean('generar')) {
            $redirect = $this->redirectIfReporteFechasEmisionInvalidas($request);
            if ($redirect !== null) {
                return $redirect;
            }
        }

        $lineas = null;
        $totales = ['subtotal' => 0.0, 'kg' => 0.0];
        $resumenPorVendedor = collect();
        $resumenPorZona = collect();

        // Millennium — filtro por "zona" en reportes: usar Estado (entidad federal) del cliente.
        // Para no hacer perder tiempo, el selector de Estado muestra SOLO estados con datos disponibles
        // (y respeta filtros actuales excepto el propio estado).
        $estados = $this->estadosDisponiblesParaReporte($request);

        if ($request->boolean('generar')) {
            $base = FacturaLinea::query();
            $this->applyReporteFilters($base, $request);

            $totales['subtotal'] = round((float) (clone $base)->sum('subtotal'), 2);
            $totales['kg'] = round((float) (clone $base)->whereHas('categoria', function ($c): void {
                $c->where('unidad', Categoria::UNIDAD_KG);
            })->sum('cantidad'), 3);

            $lineas = (clone $base)
                ->with(['factura.cliente.vendedor', 'factura.vendedor', 'factura.verificadoPor', 'categoria'])
                ->orderByDesc('id')
                ->paginate(75)
                ->withQueryString();

            $resumenPorVendedor = FacturaLinea::query()
                ->fromSub((clone $base)->select('factura_lineas.*'), 'fl')
                ->join('facturas', 'facturas.id', '=', 'fl.factura_id')
                ->selectRaw('COALESCE(facturas.vendedor_id, 0) as vid')
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
            ->whereIn('role', [
                User::ROLE_ADMIN,
                User::ROLE_VERIFICADOR,
                User::ROLE_VENDEDOR_GENERAL,
                User::ROLE_VENDEDOR_NORMAL,
            ])
            ->orderBy('name')
            ->get();

        return view('reportes.index', [
            'lineas' => $lineas,
            'totales' => $totales,
            'resumenPorVendedor' => $resumenPorVendedor,
            'resumenPorZona' => $resumenPorZona,
            'vendedores' => $vendedores,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'clientes' => Cliente::orderBy('nombre_razon_social')->get(),
            'estados' => $estados,
            'estadoCuentaFacturas' => $estadoCuentaFacturas,
            'clienteEstado' => $clienteEstado,
            'etiquetasCartera' => Factura::etiquetasCartera(),
        ]);
    }

    /**
     * PDF de reporte “Ventas y líneas”: **resumen por factura** + **detalle por línea** compacto (mismos filtros que pantalla).
     */
    public function pdfResumen(Request $request): RedirectResponse|Response
    {
        $redirect = $this->redirectIfReporteFechasEmisionInvalidas($request);
        if ($redirect !== null) {
            return $redirect;
        }

        $base = FacturaLinea::query();
        $this->applyReporteFilters($base, $request);

        $nLineas = (clone $base)->count();
        if ($nLineas > self::REPORTE_PDF_MAX_LINEAS) {
            return redirect()
                ->route('reportes.index', $request->except('generar'))
                ->with(
                    'error_pdf',
                    'El PDF admite como máximo '.self::REPORTE_PDF_MAX_LINEAS.' líneas con los filtros actuales. Acotá fechas, vendedor o categoría y volvé a intentar.'
                );
        }

        if ($nLineas === 0) {
            return redirect()
                ->route('reportes.index', $request->except('generar'))
                ->with('error_pdf', 'No hay líneas para exportar con esos filtros. Generá primero el reporte en pantalla.');
        }

        $totales = [
            'subtotal' => round((float) (clone $base)->sum('subtotal'), 2),
            'kg' => round((float) (clone $base)->whereHas('categoria', function ($c): void {
                $c->where('unidad', Categoria::UNIDAD_KG);
            })->sum('cantidad'), 3),
        ];

        $resumenPorVendedor = FacturaLinea::query()
            ->fromSub((clone $base)->select('factura_lineas.*'), 'fl')
            ->join('facturas', 'facturas.id', '=', 'fl.factura_id')
            ->selectRaw('COALESCE(facturas.vendedor_id, 0) as vid')
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

        $aggPorFactura = (clone $base)
            ->selectRaw('factura_id')
            ->selectRaw('SUM(subtotal) as subtotal_filtro')
            ->selectRaw('COUNT(*) as n_lineas')
            ->groupBy('factura_id')
            ->get()
            ->keyBy('factura_id');

        $facturasResumen = Factura::query()
            ->whereIn('id', $aggPorFactura->keys()->all())
            ->with(['cliente.estado', 'vendedor', 'verificadoPor'])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get()
            ->map(function (Factura $f) use ($aggPorFactura) {
                $a = $aggPorFactura->get($f->id);

                return [
                    'numero' => $f->numero_factura,
                    'fecha' => $f->fecha_emision->format('d/m/Y'),
                    'cliente' => $f->cliente?->nombre_razon_social ?? '—',
                    'zona' => $f->cliente?->zona ?: '—',
                    'estado_cliente' => $f->cliente?->estado?->nombre_estado ?? '—',
                    'vendedor' => $f->vendedor?->name ?? '—',
                    'total_factura' => round((float) $f->total, 2),
                    'saldo' => round((float) $f->saldo_pendiente, 2),
                    'estado_pago' => $f->estado_pago === Factura::ESTADO_PAGO_PAGADA ? 'Pagada' : 'Por pagar',
                    'verif' => $f->estaVerificada() ? ($f->verificadoPor?->name ?? 'Verificada') : 'Pendiente',
                    'n_lineas' => (int) ($a->n_lineas ?? 0),
                    'subtotal_filtro' => round((float) ($a->subtotal_filtro ?? 0), 2),
                ];
            })
            ->values();

        $lineasDetalle = (clone $base)
            ->with(['factura.cliente.estado', 'factura.vendedor', 'factura.verificadoPor', 'categoria'])
            ->orderByDesc('factura_id')
            ->orderBy('id')
            ->get();

        $logoColor = $this->pdfLogoDataUri('images/brand/milenium-vectores.png');
        $logoBlanco = $this->pdfLogoDataUri('images/brand/millenium-vectores-en-blanco.png');

        $pdf = Pdf::loadView('pdf.reporte-ventas-resumen', [
            'generadoEn' => Carbon::now(),
            'filtros' => $this->reportePdfFiltrosLegibles($request),
            'totales' => $totales,
            'resumenPorVendedor' => $resumenPorVendedor,
            'resumenPorZona' => $resumenPorZona,
            'facturasResumen' => $facturasResumen,
            'lineasDetalle' => $lineasDetalle,
            'logoColor' => $logoColor,
            'logoBlanco' => $logoBlanco,
            'nLineas' => $nLineas,
            'nFacturas' => $facturasResumen->count(),
        ])->setPaper('a4', 'landscape');

        $slug = Carbon::now()->format('Y-m-d_His');

        return $pdf->download('reporte-ventas-lineas-resumen_'.$slug.'.pdf');
    }

    /**
     * Valida fechas de emisión del reporte antes de consultar la BD (evita rangos imposibles o años absurdos).
     */
    private function redirectIfReporteFechasEmisionInvalidas(Request $request): ?RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ], [
            'desde.date' => 'La fecha Desde (emisión) no es válida.',
            'hasta.date' => 'La fecha Hasta (emisión) no es válida.',
        ]);

        $validator->after(function ($v) use ($request): void {
            if ($v->errors()->hasAny(['desde', 'hasta'])) {
                return;
            }
            $hoy = Carbon::today();
            if ($request->filled('desde') && Carbon::parse($request->input('desde'))->startOfDay()->gt($hoy)) {
                $v->errors()->add('desde', 'La fecha Desde (emisión) no puede ser posterior a hoy.');
            }
            if ($request->filled('hasta') && Carbon::parse($request->input('hasta'))->startOfDay()->gt($hoy)) {
                $v->errors()->add('hasta', 'La fecha Hasta (emisión) no puede ser posterior a hoy.');
            }
            if (! $request->filled('desde') || ! $request->filled('hasta')) {
                return;
            }
            if ($v->errors()->hasAny(['desde', 'hasta'])) {
                return;
            }
            if ($request->date('desde')->gt($request->date('hasta'))) {
                $v->errors()->add(
                    'hasta',
                    'La fecha Hasta (emisión) debe ser igual o posterior a Desde (emisión).'
                );
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('reportes.index', $request->except('generar'))
                ->withErrors($validator)
                ->withInput();
        }

        return null;
    }

    /** @return list<string> */
    private function reportePdfFiltrosLegibles(Request $request): array
    {
        $out = [];
        if ($request->filled('desde')) {
            $out[] = 'Desde emisión: '.$request->date('desde')->format('d/m/Y');
        }
        if ($request->filled('hasta')) {
            $out[] = 'Hasta emisión: '.$request->date('hasta')->format('d/m/Y');
        }
        if ($request->filled('vendedor_id')) {
            $u = User::find($request->integer('vendedor_id'));
            $out[] = 'Vendedor (factura): '.($u?->name ?? '#'.$request->integer('vendedor_id'));
        }
        if ($request->filled('id_estado')) {
            $nom = DB::table('estados')->where('id_estado', $request->integer('id_estado'))->value('nombre_estado');
            $out[] = 'Estado (cliente): '.($nom ?? 'ID '.$request->integer('id_estado'));
        }
        if ($request->filled('categoria_id')) {
            $c = Categoria::find($request->integer('categoria_id'));
            $out[] = 'Categoría: '.($c?->nombre ?? '#'.$request->integer('categoria_id'));
        }
        if ($request->filled('estado_pago')) {
            $ep = $request->string('estado_pago')->toString();
            $out[] = 'Estado pago: '.($ep === Factura::ESTADO_PAGO_PAGADA ? 'Pagada' : ($ep === Factura::ESTADO_PAGO_ABIERTA ? 'Por pagar' : $ep));
        }
        if ($request->boolean('solo_sin_verificar')) {
            $out[] = 'Solo facturas sin verificar: sí';
        }
        if ($out === []) {
            $out[] = 'Sin filtros (todo el universo de líneas disponibles).';
        }

        return $out;
    }

    private function pdfLogoDataUri(string $relativePublicPath): ?string
    {
        $path = public_path($relativePublicPath);
        if (! is_readable($path)) {
            return null;
        }
        $data = @file_get_contents($path);
        if ($data === false || $data === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($data);
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
            if ($request->filled('estado_pago')) {
                $ep = $request->string('estado_pago')->toString();
                if ($ep === Factura::ESTADO_PAGO_ABIERTA || $ep === Factura::ESTADO_PAGO_PAGADA) {
                    $q->where('estado_pago', $ep);
                }
            }
            if ($request->filled('vendedor_id')) {
                $q->where('vendedor_id', $request->integer('vendedor_id'));
            }
        });

        $q->whereHas('factura.cliente', function ($q) use ($request): void {
            if ($request->filled('id_estado')) {
                $q->where('id_estado', $request->integer('id_estado'));
            }
        });

        if ($request->filled('categoria_id')) {
            $q->where('categoria_id', $request->integer('categoria_id'));
        }
    }

    /**
     * Estados (entidades federales) disponibles para el reporte según filtros actuales.
     * No aplica el filtro id_estado para que el selector muestre opciones útiles.
     */
    private function estadosDisponiblesParaReporte(Request $request)
    {
        $base = FacturaLinea::query();

        // Reutiliza las reglas del reporte, pero sin aplicar id_estado.
        $base->whereHas('factura', function ($q) use ($request): void {
            if ($request->filled('desde')) {
                $q->whereDate('fecha_emision', '>=', $request->date('desde'));
            }
            if ($request->filled('hasta')) {
                $q->whereDate('fecha_emision', '<=', $request->date('hasta'));
            }
            if ($request->boolean('solo_sin_verificar')) {
                $q->whereNull('verificado_por');
            }
            if ($request->filled('estado_pago')) {
                $ep = $request->string('estado_pago')->toString();
                if ($ep === Factura::ESTADO_PAGO_ABIERTA || $ep === Factura::ESTADO_PAGO_PAGADA) {
                    $q->where('estado_pago', $ep);
                }
            }
            if ($request->filled('vendedor_id')) {
                $q->where('vendedor_id', $request->integer('vendedor_id'));
            }
        });

        if ($request->filled('categoria_id')) {
            $base->where('categoria_id', $request->integer('categoria_id'));
        }

        // Estados presentes en clientes vinculados a facturas/lineas filtradas.
        return DB::table('estados')
            ->join('clientes', 'clientes.id_estado', '=', 'estados.id_estado')
            ->join('facturas', 'facturas.cliente_id', '=', 'clientes.id')
            ->joinSub($base->select('factura_id')->distinct(), 'fl', function ($join): void {
                $join->on('fl.factura_id', '=', 'facturas.id');
            })
            ->select('estados.id_estado', 'estados.nombre_estado', 'estados.codigo_iso_3166_2')
            ->distinct()
            ->orderBy('estados.nombre_estado')
            ->get();
    }
}
