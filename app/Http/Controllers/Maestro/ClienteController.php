<?php

namespace App\Http\Controllers\Maestro;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Parroquia;
use App\Models\User;
use App\Support\VenezuelanDocumento;
use App\Support\ZonasComerciales;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $q = Cliente::query()->with(['vendedor', 'estado', 'ciudad', 'municipio', 'parroquia'])->orderBy('nombre_razon_social');

        if ($request->filled('buscar')) {
            $s = $request->string('buscar')->toString();
            $q->whereBuscarTexto($s);
        }

        if ($request->filled('vendedor_id')) {
            $q->where('vendedor_id', $request->integer('vendedor_id'));
        }

        if ($request->filled('zona') && $request->string('zona')->trim()->toString() !== '') {
            $q->where('zona', $request->string('zona')->trim()->toString());
        }

        return view('maestros.clientes.index', [
            'clientes' => $q->paginate(15)->withQueryString(),
            'vendedores' => User::opcionesVendedor(),
            'zonasComercialesFiltro' => ZonasComerciales::opciones(),
        ]);
    }

    /**
     * Millennium — comprobación AJAX de formato + duplicado (misma normalización que Store/Update).
     */
    public function checkDocumento(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo_documento' => ['required', 'string', 'size:1', Rule::in(Cliente::TIPOS_DOCUMENTO)],
            'documento_numero' => ['required', 'string', 'max:48'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
        ]);

        $digitos = VenezuelanDocumento::soloDigitos($validated['documento_numero']);
        $formato = VenezuelanDocumento::validarFormato($validated['tipo_documento'], $digitos);

        if ($formato !== null) {
            return response()->json([
                'ok' => true,
                'formato_valido' => false,
                'disponible' => null,
                'mensaje' => $formato,
            ]);
        }

        $q = Cliente::query()
            ->where('tipo_documento', $validated['tipo_documento'])
            ->where('documento_numero', $digitos);

        if (! empty($validated['cliente_id'])) {
            $q->where('id', '!=', $validated['cliente_id']);
        }

        $existe = $q->exists();

        return response()->json([
            'ok' => true,
            'formato_valido' => true,
            'disponible' => ! $existe,
            'mensaje' => $existe ? __('clientes.documento_ya_registrado') : null,
        ]);
    }

    public function create(): View
    {
        return view('maestros.clientes.create', [
            'vendedores' => User::opcionesVendedor(),
            'tiposDocumento' => Cliente::tiposDocumentoLabels(),
            'estados' => Estado::query()->orderBy('nombre_estado')->get(['id_estado', 'nombre_estado', 'codigo_iso_3166_2']),
            'zonasComerciales' => ZonasComerciales::opciones(),
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
            'cliente' => $cliente->load(['estado', 'ciudad', 'municipio', 'parroquia']),
            'vendedores' => User::opcionesVendedor(),
            'tiposDocumento' => Cliente::tiposDocumentoLabels(),
            'estados' => Estado::query()->orderBy('nombre_estado')->get(['id_estado', 'nombre_estado', 'codigo_iso_3166_2']),
            'zonasComerciales' => ZonasComerciales::opciones(),
        ]);
    }

    /**
     * Millennium — ciudades por estado (dropdown dependiente).
     */
    public function ciudades(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_estado' => ['required', 'integer', 'exists:estados,id_estado'],
        ]);

        $rows = Ciudad::query()
            ->where('id_estado', $validated['id_estado'])
            ->orderByDesc('es_capital')
            ->orderBy('nombre_ciudad')
            ->get(['id_ciudad', 'id_estado', 'nombre_ciudad', 'es_capital']);

        return response()->json(['ok' => true, 'ciudades' => $rows]);
    }

    /**
     * Millennium — municipios por estado (dropdown dependiente; reportes por entidad federal).
     */
    public function municipiosPorEstado(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_estado' => ['required', 'integer', 'exists:estados,id_estado'],
        ]);

        $rows = Municipio::query()
            ->where('id_estado', $validated['id_estado'])
            ->orderBy('nombre_municipio')
            ->get(['id_municipio', 'id_estado', 'nombre_municipio']);

        return response()->json(['ok' => true, 'municipios' => $rows]);
    }

    /**
     * Millennium — parroquias por municipio (para dropdown dependiente en formulario de clientes).
     */
    public function parroquias(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_municipio' => ['required', 'integer', 'exists:municipios,id_municipio'],
        ]);

        $rows = Parroquia::query()
            ->where('id_municipio', $validated['id_municipio'])
            ->orderBy('nombre_parroquia')
            ->get(['id_parroquia', 'id_municipio', 'nombre_parroquia']);

        return response()->json(['ok' => true, 'parroquias' => $rows]);
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
