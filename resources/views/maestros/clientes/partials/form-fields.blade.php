{{--
    Millennium — crear/editar cliente. El número de documento se sanea con JS nativo (addEventListener),
    igual que en tu formulario PHP; Alpine solo muestra mensajes y llama a check-documento (requiere app.js).
--}}
@php
    $isEdit = isset($cliente);
    $oldDoc = old('documento_numero', $isEdit ? $cliente->documento_numero : '');
    $oldDocDigits = preg_replace('/\D/', '', (string) $oldDoc);
    $oldTelRaw = old('telefono', $isEdit ? ($cliente->telefono ?? '') : '');
    $oldTelDigits = substr(preg_replace('/\D/', '', (string) $oldTelRaw), 0, 11);
    $oldEmail = old('email', $isEdit ? ($cliente->email ?? '') : '');
    $oldDireccion = old('direccion', $isEdit ? ($cliente->direccion ?? '') : '');
    $oldEstado = old('id_estado', $isEdit ? ($cliente->id_estado ?? $cliente->municipio?->id_estado) : '');
    $oldCiudad = old('id_ciudad', $isEdit ? ($cliente->id_ciudad ?? '') : '');
    $oldMunicipio = old('id_municipio', $isEdit ? ($cliente->id_municipio ?? '') : '');
    $oldParroquia = old('id_parroquia', $isEdit ? ($cliente->id_parroquia ?? '') : '');

    /** Millennium: data mínima para buscador de estado (id + nombre + ISO) */
    $estadosData = collect($estados ?? [])->map(fn ($e) => [
        'id' => (int) $e->id_estado,
        'nombre' => (string) $e->nombre_estado,
        'iso' => (string) ($e->codigo_iso_3166_2 ?? ''),
    ])->values();

    $zonasComerciales = $zonasComerciales ?? \App\Support\ZonasComerciales::opciones();
    $codigosZonaComercial = array_keys($zonasComerciales);
    $zonaValorActual = old('zona', $isEdit ? ($cliente->zona ?? '') : '');
    $oldZonaSelect = old('zona_select');
    if ($oldZonaSelect !== null && (string) $oldZonaSelect !== '') {
        $zonaSelectInicial = (string) $oldZonaSelect;
    } elseif ($zonaValorActual !== '' && in_array($zonaValorActual, $codigosZonaComercial, true)) {
        $zonaSelectInicial = $zonaValorActual;
    } elseif ($zonaValorActual !== '') {
        $zonaSelectInicial = '__otra__';
    } else {
        $zonaSelectInicial = '';
    }
    $zonaOtraInicial = old('zona_otra', $zonaSelectInicial === '__otra__' ? (string) $zonaValorActual : '');
@endphp
<div
    class="space-y-4"
    {{-- Millennium: usar comillas simples para no romper el atributo con strings JSON (comillas dobles) --}}
    x-data='clienteFormMillennium({
        checkUrl: @json(route("clientes.check-documento")),
        ciudadesUrl: @json(route("clientes.ciudades")),
        municipiosUrl: @json(route("clientes.municipios-por-estado")),
        parroquiasUrl: @json(route("clientes.parroquias")),
        clienteId: @json($isEdit ? $cliente->id : null),
        documentoInicial: @json($oldDocDigits),
        estadoInicial: @json($oldEstado !== null && $oldEstado !== "" ? (string) $oldEstado : ""),
        estadosData: @json($estadosData),
        ciudadInicial: @json($oldCiudad !== null && $oldCiudad !== "" ? (string) $oldCiudad : ""),
        municipioInicial: @json($oldMunicipio),
        parroquiaInicial: @json($oldParroquia),
        zonaSelectInicial: @json($zonaSelectInicial),
    })'
>
    <form
        method="post"
        action="{{ $isEdit ? route('clientes.update', $cliente) : route('clientes.store') }}"
        class="space-y-4"
        novalidate
        @submit="enviarCliente($event)"
    >
        @csrf
        @if ($isEdit)
            @method('patch')
        @endif

        {{-- Millennium — sección 2: identificación fiscal/persona --}}
        <div>
            <x-input-label value="Identificación" />
            <div class="mt-1 flex gap-2 items-stretch">
                <select
                    id="tipo_documento"
                    name="tipo_documento"
                    x-model="tipoDocumento"
                    @change="onCambioTipo()"
                    class="shrink-0 w-[4.5rem] rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
                >
                    @foreach ($tiposDocumento as $codigo => $etiqueta)
                        <option value="{{ $codigo }}" title="{{ $etiqueta }}" @selected(old('tipo_documento', $isEdit ? $cliente->tipo_documento : 'V') === $codigo)>{{ $codigo }}</option>
                    @endforeach
                </select>
                {{-- Sin x-bind / @input Alpine: si Vite no carga, igual funciona el script nativo abajo --}}
                <input
                    id="documento_numero"
                    name="documento_numero"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    maxlength="9"
                    value="{{ $oldDocDigits }}"
                    class="flex-1 min-w-0 font-mono border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-millennium-dark dark:focus:border-millennium-sand focus:ring-millennium-sand rounded-md shadow-sm"
                />
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Elegí el prefijo (V, E, J…) y escribí solo números aquí; no se pueden ingresar letras ni guiones en este campo.
            </p>
            <p x-show="hintTipo" x-cloak class="mt-1 text-xs text-millennium-dark/80 dark:text-millennium-sand/90" x-text="hintTipo"></p>
            <p x-show="docClienteMsg" x-cloak class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="docClienteMsg" role="alert"></p>
            <p x-show="docDuplicadoMsg" x-cloak class="mt-1 text-sm text-amber-700 dark:text-amber-300" x-text="docDuplicadoMsg" role="alert"></p>
            <x-input-error class="mt-2" :messages="$errors->get('tipo_documento')" />
            <x-input-error class="mt-2" :messages="$errors->get('documento_numero')" />
        </div>

        <div>
            <x-input-label for="nombre_razon_social" value="Nombre o razón social" />
            <x-text-input
                id="nombre_razon_social"
                name="nombre_razon_social"
                type="text"
                class="mt-1 block w-full"
                :value="old('nombre_razon_social', $isEdit ? $cliente->nombre_razon_social : '')"
                @blur="validarNombre()"
            />
            <p x-show="nombreMsg" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="nombreMsg" role="alert"></p>
            <x-input-error class="mt-2" :messages="$errors->get('nombre_razon_social')" />
        </div>

        {{-- Millennium — sección 3: contacto y datos útiles --}}
        <div>
            <x-input-label for="email" value="Correo (opcional)" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                value="{{ $oldEmail }}"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Útil para automatización (estado de cuenta, avisos, etc.).</p>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="direccion" value="Dirección (opcional)" />
            <textarea
                id="direccion"
                name="direccion"
                rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
            >{{ $oldDireccion }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
        </div>

        <div>
            {{-- Millennium — ubicación Venezuela: estado primero (reportes), luego ciudad / municipio / parroquia (cuadrícula 2×2) --}}
            <div class="rounded-lg border border-millennium-dark/10 dark:border-millennium-sand/20 bg-white/60 dark:bg-gray-900/20 p-4 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-sm font-semibold text-millennium-dark/90 dark:text-millennium-sand/90">Ubicación (predeterminada)</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 shrink-0">Estado obligatorio · resto opcional</div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">
                    El <span class="font-semibold text-gray-700 dark:text-gray-300">estado</span> (ej. Portuguesa, Amazonas) es lo que principalmente alimenta reportes por zona. Ciudad, municipio y parroquia son opcionales pero recomendadas para más detalle territorial.
                </p>
                <p x-show="ubicMsg" x-cloak class="text-sm text-red-600 dark:text-red-400" x-text="ubicMsg" role="alert"></p>
                {{-- Millennium: gap-x real entre columnas (minmax 0 evita que inputs100% se coman el hueco) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="min-w-0 space-y-1.5">
                        <x-input-label for="estado_buscar" value="Estado" />
                        {{-- Millennium: buscador de estado (evita scroll y el dropdown siempre abre hacia abajo) --}}
                        <div class="relative" @click.outside="estadoOpen = false">
                            <input type="hidden" id="id_estado" name="id_estado" :value="estadoId" />
                            <input
                                id="estado_buscar"
                                type="text"
                                autocomplete="off"
                                placeholder="Escribí: Portuguesa, Lara, Zulia…"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
                                :value="estadoTexto"
                                @focus="estadoOpen = true"
                                @input="onEstadoTextoInput($event)"
                                @keydown.down.prevent="estadoMover(1)"
                                @keydown.up.prevent="estadoMover(-1)"
                                @keydown.enter.prevent="estadoConfirmarActivo()"
                                @keydown.escape.prevent="estadoOpen = false"
                            />
                            <div
                                x-show="estadoOpen"
                                x-cloak
                                class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                style="top: calc(100% + 0.25rem);"
                            >
                                <div class="max-h-64 overflow-auto py-1">
                                    <template x-for="(e, idx) in estadosFiltrados" :key="e.id">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                            :class="idx === estadoActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                            @click="seleccionarEstado(e)"
                                        >
                                            <span class="font-medium" x-text="e.nombre"></span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="e.iso ? ' · ' + e.iso : ''"></span>
                                        </button>
                                    </template>
                                    <div x-show="estadosFiltrados.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                        No hay coincidencias.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('id_estado')" />
                    </div>
                    <div class="min-w-0 space-y-1.5">
                        <x-input-label for="ciudad_buscar" value="Ciudad (opcional)" />
                        <div class="relative" @click.outside="ciudadOpen = false">
                            <input type="hidden" id="id_ciudad" name="id_ciudad" :value="ciudadId" />
                            <input
                                id="ciudad_buscar"
                                type="text"
                                autocomplete="off"
                                placeholder="Escribí para buscar ciudad…"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800"
                                :disabled="!estadoId || ubicCargandoCiudades"
                                :value="ciudadTexto"
                                @focus="estadoId && (ciudadOpen = true)"
                                @input="onCiudadTextoInput($event)"
                                @keydown.down.prevent="ciudadMover(1)"
                                @keydown.up.prevent="ciudadMover(-1)"
                                @keydown.enter.prevent="ciudadConfirmarActivo()"
                                @keydown.escape.prevent="ciudadOpen = false"
                            />
                            <div
                                x-show="ciudadOpen && estadoId && !ubicCargandoCiudades"
                                x-cloak
                                class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                style="top: calc(100% + 0.25rem);"
                            >
                                <div class="max-h-64 overflow-auto py-1">
                                    <template x-for="(c, idx) in ciudadesFiltrados" :key="c.id">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                            :class="idx === ciudadActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                            @click="seleccionarCiudad(c)"
                                        >
                                            <span class="font-medium" x-text="c.nombre + (c.es_capital ? ' (capital)' : '')"></span>
                                        </button>
                                    </template>
                                    <div x-show="ciudadesFiltrados.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="ciudadesLista.length === 0 ? 'Elegí primero el estado o esperá la carga.' : 'No hay coincidencias.'"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Se carga al elegir estado; podés escribir para filtrar.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('id_ciudad')" />
                    </div>
                    <div class="min-w-0 space-y-1.5">
                        <x-input-label for="municipio_buscar" value="Municipio (opcional)" />
                        <div class="relative" @click.outside="municipioOpen = false">
                            <input type="hidden" id="id_municipio" name="id_municipio" :value="municipioId" />
                            <input
                                id="municipio_buscar"
                                type="text"
                                autocomplete="off"
                                placeholder="Escribí para buscar municipio…"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800"
                                :disabled="!estadoId || ubicCargandoMunicipios"
                                :value="municipioTexto"
                                @focus="estadoId && (municipioOpen = true)"
                                @input="onMunicipioTextoInput($event)"
                                @keydown.down.prevent="municipioMover(1)"
                                @keydown.up.prevent="municipioMover(-1)"
                                @keydown.enter.prevent="municipioConfirmarActivo()"
                                @keydown.escape.prevent="municipioOpen = false"
                            />
                            <div
                                x-show="municipioOpen && estadoId && !ubicCargandoMunicipios"
                                x-cloak
                                class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                style="top: calc(100% + 0.25rem);"
                            >
                                <div class="max-h-64 overflow-auto py-1">
                                    <template x-for="(m, idx) in municipiosFiltrados" :key="m.id">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                            :class="idx === municipioActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                            @click="seleccionarMunicipio(m)"
                                        >
                                            <span class="font-medium" x-text="m.nombre"></span>
                                        </button>
                                    </template>
                                    <div x-show="municipiosFiltrados.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="municipiosLista.length === 0 ? 'Elegí primero el estado o esperá la carga.' : 'No hay coincidencias.'"></div>
                                </div>
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('id_municipio')" />
                    </div>
                    <div class="min-w-0 space-y-1.5">
                        <x-input-label for="parroquia_buscar" value="Parroquia (opcional)" />
                        <div class="relative" @click.outside="parroquiaOpen = false">
                            <input type="hidden" id="id_parroquia" name="id_parroquia" :value="parroquiaId" />
                            <input
                                id="parroquia_buscar"
                                type="text"
                                autocomplete="off"
                                placeholder="Escribí para buscar parroquia…"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800"
                                :disabled="!municipioId || ubicCargandoParroquias"
                                :value="parroquiaTexto"
                                @focus="municipioId && (parroquiaOpen = true)"
                                @input="onParroquiaTextoInput($event)"
                                @keydown.down.prevent="parroquiaMover(1)"
                                @keydown.up.prevent="parroquiaMover(-1)"
                                @keydown.enter.prevent="parroquiaConfirmarActivo()"
                                @keydown.escape.prevent="parroquiaOpen = false"
                            />
                            <div
                                x-show="parroquiaOpen && municipioId && !ubicCargandoParroquias"
                                x-cloak
                                class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                                style="top: calc(100% + 0.25rem);"
                            >
                                <div class="max-h-64 overflow-auto py-1">
                                    <template x-for="(p, idx) in parroquiasFiltrados" :key="p.id">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                            :class="idx === parroquiaActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                            @click="seleccionarParroquia(p)"
                                        >
                                            <span class="font-medium" x-text="p.nombre"></span>
                                        </button>
                                    </template>
                                    <div x-show="parroquiasFiltrados.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="parroquiasLista.length === 0 ? 'Elegí primero el municipio o esperá la carga.' : 'No hay coincidencias.'"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Se habilita al elegir municipio; podés escribir para filtrar.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('id_parroquia')" />
                    </div>
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="telefono" value="Teléfono (opcional)" />
            {{-- Millennium: mismo patrón que documento — solo dígitos, máx. 11 (04 + 9); JS nativo abajo --}}
            <input
                id="telefono"
                name="telefono"
                type="text"
                inputmode="numeric"
                autocomplete="tel"
                maxlength="11"
                placeholder="04124567890"
                value="{{ $oldTelDigits }}"
                @blur="validarTelefono()"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-millennium-dark dark:focus:border-millennium-sand focus:ring-millennium-sand dark:focus:ring-millennium-sand rounded-md shadow-sm"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Móvil Venezuela: 11 dígitos comenzando por 04 (ej. 04124567890).</p>
            <p x-show="telMsg" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="telMsg" role="alert"></p>
            <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
        </div>

        <div>
            <x-input-label for="zona_select" value="Zona comercial / ruta (reportes)" />
            <select
                id="zona_select"
                name="zona_select"
                x-model="zonaSelect"
                @change="validarZona()"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
            >
                <option value="">— Sin asignar —</option>
                @foreach ($zonasComerciales as $codigo => $etiqueta)
                    <option value="{{ $codigo }}" @selected($zonaSelectInicial === $codigo)>{{ $etiqueta }}</option>
                @endforeach
                <option value="__otra__" @selected($zonaSelectInicial === '__otra__')>Otra ruta o sector…</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lista fija para que los reportes por zona sean consistentes. Si no encaja, usá «Otra» y describí la ruta.</p>
            <div x-show="zonaSelect === '__otra__'" x-cloak class="mt-2 space-y-1">
                <x-input-label for="zona_otra" value="Describí la ruta o sector" />
                <x-text-input
                    id="zona_otra"
                    name="zona_otra"
                    type="text"
                    class="mt-1 block w-full"
                    value="{{ $zonaOtraInicial }}"
                    maxlength="120"
                    @blur="validarZona()"
                />
            </div>
            <p x-show="zonaMsg" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="zonaMsg" role="alert"></p>
            <x-input-error class="mt-2" :messages="$errors->get('zona_select')" />
            <x-input-error class="mt-2" :messages="$errors->get('zona')" />
            <x-input-error class="mt-2" :messages="$errors->get('zona_otra')" />
        </div>

        <div>
            <x-input-label for="vendedor_id" value="Vendedor asignado (opcional)" />
            <select id="vendedor_id" name="vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                <option value="">— Sin asignar —</option>
                @foreach ($vendedores as $v)
                    <option value="{{ $v->id }}" @selected(old('vendedor_id', $isEdit ? $cliente->vendedor_id : null) == $v->id)>
                        {{ $v->name }} — {{ \App\Models\User::roleLabels()[$v->role] ?? $v->role }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('vendedor_id')" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                ¿Falta alguien en la lista?
                <a href="{{ route('vendedores.index') }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Vendedores</a>
                · <a href="{{ route('datos-maestros.index') }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Datos maestros</a>
            </p>
        </div>

        <div class="flex items-center gap-2 pt-4">
            <x-primary-button type="submit">{{ $isEdit ? 'Guardar cambios' : 'Guardar' }}</x-primary-button>
            <a href="{{ route('clientes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ $isEdit ? 'Volver' : 'Cancelar' }}</a>
        </div>
    </form>
</div>

@once
    @push('scripts')
        <script>
            /** Millennium — tope de dígitos según prefijo (misma regla que el componente Alpine) */
            function millenniumMaxDigitosCliente(tipo) {
                if (tipo === 'V' || tipo === 'E') {
                    return 8;
                }
                return 9;
            }

            /** Millennium — móvil VE: 11 dígitos; mismo criterio que VenezuelanTelefonoMovil en PHP */
            var MILLENNIUM_TEL_MOVIL_MAX = 11;

            function millenniumBindClienteTelefonoNativo() {
                var telEl = document.getElementById('telefono');
                if (!telEl || telEl.dataset.millenniumTelNativo === '1') {
                    return;
                }
                telEl.dataset.millenniumTelNativo = '1';

                function sanitizar() {
                    var s = String(telEl.value).replace(/\D/g, '').slice(0, MILLENNIUM_TEL_MOVIL_MAX);
                    if (telEl.value !== s) {
                        telEl.value = s;
                    }
                }

                telEl.addEventListener('input', sanitizar);
                telEl.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var clip = (e.clipboardData || window.clipboardData).getData('text') || '';
                    var solo = clip.replace(/\D/g, '');
                    var start = this.selectionStart ?? this.value.length;
                    var end = this.selectionEnd ?? this.value.length;
                    var cur = String(this.value);
                    var merged = (cur.slice(0, start) + solo + cur.slice(end)).replace(/\D/g, '').slice(0, MILLENNIUM_TEL_MOVIL_MAX);
                    this.value = merged;
                    sanitizar();
                    var pos = Math.min(merged.length, start + solo.length);
                    queueMicrotask(function () {
                        telEl.setSelectionRange(pos, pos);
                    });
                });
                telEl.addEventListener('keydown', function (e) {
                    if (e.isComposing || e.key === 'Dead') {
                        return;
                    }
                    var nav = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                    if (nav.includes(e.key)) {
                        return;
                    }
                    if (e.ctrlKey || e.metaKey) {
                        return;
                    }
                    if (e.key.length !== 1) {
                        return;
                    }
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                        return;
                    }
                    var cur = String(telEl.value ?? '');
                    var a = telEl.selectionStart ?? cur.length;
                    var b = telEl.selectionEnd ?? cur.length;
                    if (cur.length - (b - a) + 1 > MILLENNIUM_TEL_MOVIL_MAX) {
                        e.preventDefault();
                    }
                });
                sanitizar();
            }

            /**
             * Millennium — igual que rif-numeros / cod_cotizacion en tu SISPRE: solo dígitos, tope, sin depender de Alpine.
             * Se enlaza una sola vez por campo (#documento_numero).
             */
            function millenniumBindClienteDocumentoNativo() {
                const docEl = document.getElementById('documento_numero');
                const tipoEl = document.getElementById('tipo_documento');
                if (!docEl || docEl.dataset.millenniumDocNativo === '1') {
                    return;
                }
                docEl.dataset.millenniumDocNativo = '1';

                function aplicarMaxlengthHtml() {
                    const mx = millenniumMaxDigitosCliente(tipoEl ? tipoEl.value : 'V');
                    docEl.setAttribute('maxlength', String(mx));
                }

                function sanitizarYNotificar() {
                    aplicarMaxlengthHtml();
                    const mx = millenniumMaxDigitosCliente(tipoEl ? tipoEl.value : 'V');
                    let s = String(docEl.value).replace(/\D/g, '').slice(0, mx);
                    if (docEl.value !== s) {
                        docEl.value = s;
                    }
                    window.dispatchEvent(new CustomEvent('millennium-cliente-doc-input', { detail: { value: s } }));
                }

                docEl.addEventListener('input', sanitizarYNotificar);
                docEl.addEventListener('blur', function () {
                    window.dispatchEvent(new CustomEvent('millennium-cliente-doc-blur'));
                });

                tipoEl?.addEventListener('change', function () {
                    sanitizarYNotificar();
                });

                docEl.addEventListener('paste', function (e) {
                    e.preventDefault();
                    const clip = (e.clipboardData || window.clipboardData).getData('text') || '';
                    const solo = clip.replace(/\D/g, '');
                    const start = this.selectionStart ?? this.value.length;
                    const end = this.selectionEnd ?? this.value.length;
                    const mx = millenniumMaxDigitosCliente(tipoEl ? tipoEl.value : 'V');
                    const cur = String(this.value);
                    let merged = (cur.slice(0, start) + solo + cur.slice(end)).replace(/\D/g, '').slice(0, mx);
                    this.value = merged;
                    sanitizarYNotificar();
                    const pos = Math.min(merged.length, start + solo.length);
                    queueMicrotask(function () {
                        docEl.setSelectionRange(pos, pos);
                    });
                });

                docEl.addEventListener('keydown', function (e) {
                    if (e.isComposing || e.key === 'Dead') {
                        return;
                    }
                    const nav = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                    if (nav.includes(e.key)) {
                        return;
                    }
                    if (e.ctrlKey || e.metaKey) {
                        return;
                    }
                    if (e.key.length !== 1) {
                        return;
                    }
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                        return;
                    }
                    const mx = millenniumMaxDigitosCliente(tipoEl ? tipoEl.value : 'V');
                    const cur = String(docEl.value ?? '');
                    const a = docEl.selectionStart ?? cur.length;
                    const b = docEl.selectionEnd ?? cur.length;
                    const nextLen = cur.length - (b - a) + 1;
                    if (nextLen > mx) {
                        e.preventDefault();
                    }
                });

                sanitizarYNotificar();
            }

            function millenniumBindClienteCamposNativos() {
                millenniumBindClienteDocumentoNativo();
                millenniumBindClienteTelefonoNativo();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', millenniumBindClienteCamposNativos);
            } else {
                millenniumBindClienteCamposNativos();
            }

            function clienteFormMillennium(opts) {
                return {
                    checkUrl: opts.checkUrl,
                    ciudadesUrl: opts.ciudadesUrl,
                    municipiosUrl: opts.municipiosUrl,
                    parroquiasUrl: opts.parroquiasUrl,
                    estadosData: Array.isArray(opts.estadosData) ? opts.estadosData : [],
                    clienteId: opts.clienteId,
                    tipoDocumento: 'V',
                    documentoNumero: opts.documentoInicial || '',
                    estadoInicial: opts.estadoInicial || '',
                    ciudadInicial: opts.ciudadInicial || '',
                    municipioInicial: opts.municipioInicial || '',
                    parroquiaInicial: opts.parroquiaInicial || '',
                    docClienteMsg: '',
                    docDuplicadoMsg: '',
                    nombreMsg: '',
                    telMsg: '',
                    zonaSelect: opts.zonaSelectInicial || '',
                    zonaMsg: '',
                    ubicMsg: '',
                    estadoId: '',
                    estadoTexto: '',
                    estadoQuery: '',
                    estadoOpen: false,
                    estadoActivoIdx: 0,
                    estadosFiltrados: [],
                    ciudadesLista: [],
                    ciudadId: '',
                    ciudadTexto: '',
                    ciudadQuery: '',
                    ciudadOpen: false,
                    ciudadActivoIdx: 0,
                    ciudadesFiltrados: [],
                    municipiosLista: [],
                    municipioId: '',
                    municipioTexto: '',
                    municipioQuery: '',
                    municipioOpen: false,
                    municipioActivoIdx: 0,
                    municipiosFiltrados: [],
                    parroquiasLista: [],
                    parroquiaId: '',
                    parroquiaTexto: '',
                    parroquiaQuery: '',
                    parroquiaOpen: false,
                    parroquiaActivoIdx: 0,
                    parroquiasFiltrados: [],
                    ubicCargandoCiudades: false,
                    ubicCargandoMunicipios: false,
                    ubicCargandoParroquias: false,
                    hintTipo: '',
                    hintsPorTipo: {
                        V: 'Cédula venezolana: 6 a 8 dígitos (solo números).',
                        E: 'Cédula extranjero: 6 a 8 dígitos (solo números).',
                        J: 'RIF persona jurídica: exactamente 9 dígitos.',
                        G: 'RIF gubernamental: exactamente 9 dígitos.',
                        P: 'Pasaporte: 8 o 9 dígitos.',
                    },
                    async init() {
                        const sel = document.getElementById('tipo_documento');
                        if (sel) {
                            this.tipoDocumento = sel.value || 'V';
                        }
                        this._onDocNativo = (e) => {
                            this.documentoNumero = e.detail.value;
                            this.docClienteMsg = '';
                            this.docDuplicadoMsg = '';
                            clearTimeout(this._debounceDoc);
                            this._debounceDoc = setTimeout(() => this.verificarDocumentoRemoto(), 450);
                        };
                        this._onDocBlurNativo = () => this.verificarDocumentoRemoto();
                        window.addEventListener('millennium-cliente-doc-input', this._onDocNativo);
                        window.addEventListener('millennium-cliente-doc-blur', this._onDocBlurNativo);
                        millenniumBindClienteCamposNativos();
                        const docEl = document.getElementById('documento_numero');
                        if (docEl) {
                            this.documentoNumero = String(docEl.value).replace(/\D/g, '');
                        }
                        this.actualizarHintTipo();

                        // Millennium: inicializar buscador de estado con el id guardado.
                        this.estadoId = this.estadoInicial ? String(this.estadoInicial) : '';
                        const estadoInicialRow = (this.estadosData || []).find(r => String(r.id) === String(this.estadoId));
                        this.estadoTexto = estadoInicialRow ? String(estadoInicialRow.nombre) : '';
                        this.estadoQuery = this.estadoTexto;
                        this.estadoOpen = false;
                        this.estadoActivoIdx = 0;
                        this.refrescarEstadosFiltrados();

                        await this.cargarUbicacionInicial();
                    },
                    refrescarEstadosFiltrados() {
                        const q = String(this.estadoQuery || '').trim().toLowerCase();
                        const base = this.estadosData || [];
                        const rows = q
                            ? base.filter(r => String(r.nombre || '').toLowerCase().includes(q))
                            : base;
                        this.estadosFiltrados = rows.slice(0, 50);
                        if (this.estadoActivoIdx >= this.estadosFiltrados.length) {
                            this.estadoActivoIdx = Math.max(0, this.estadosFiltrados.length - 1);
                        }
                    },
                    onEstadoTextoInput(ev) {
                        this.ubicMsg = '';
                        this.estadoOpen = true;
                        this.estadoQuery = String(ev?.target?.value ?? '');
                        this.estadoTexto = this.estadoQuery;
                        this.estadoId = '';
                        this.estadoActivoIdx = 0;
                        this.refrescarEstadosFiltrados();
                    },
                    estadoMover(delta) {
                        if (!this.estadoOpen) this.estadoOpen = true;
                        const n = this.estadosFiltrados.length;
                        if (n === 0) return;
                        const next = this.estadoActivoIdx + delta;
                        this.estadoActivoIdx = Math.min(n - 1, Math.max(0, next));
                    },
                    estadoConfirmarActivo() {
                        const e = this.estadosFiltrados[this.estadoActivoIdx];
                        if (e) {
                            this.seleccionarEstado(e);
                        }
                    },
                    seleccionarEstado(e) {
                        this.estadoId = String(e.id);
                        this.estadoTexto = String(e.nombre);
                        this.estadoQuery = this.estadoTexto;
                        this.estadoOpen = false;
                        this.onCambioEstado();
                    },
                    deshabilitarUbicacionSinEstado() {
                        this.ciudadesLista = [];
                        this.municipiosLista = [];
                        this.parroquiasLista = [];
                        this.ciudadId = '';
                        this.ciudadTexto = '';
                        this.ciudadQuery = '';
                        this.ciudadOpen = false;
                        this.ciudadActivoIdx = 0;
                        this.municipioId = '';
                        this.municipioTexto = '';
                        this.municipioQuery = '';
                        this.municipioOpen = false;
                        this.municipioActivoIdx = 0;
                        this.parroquiaId = '';
                        this.parroquiaTexto = '';
                        this.parroquiaQuery = '';
                        this.parroquiaOpen = false;
                        this.parroquiaActivoIdx = 0;
                        this.refrescarCiudadesFiltrados();
                        this.refrescarMunicipiosFiltrados();
                        this.refrescarParroquiasFiltrados();
                    },
                    textoCiudadRow(c) {
                        return String(c.nombre || '') + (c.es_capital ? ' (capital)' : '');
                    },
                    refrescarCiudadesFiltrados() {
                        const q = String(this.ciudadQuery || '').trim().toLowerCase();
                        const base = this.ciudadesLista || [];
                        const rows = q ? base.filter(r => String(r.nombre || '').toLowerCase().includes(q)) : base;
                        this.ciudadesFiltrados = rows.slice(0, 80);
                        if (this.ciudadActivoIdx >= this.ciudadesFiltrados.length) {
                            this.ciudadActivoIdx = Math.max(0, this.ciudadesFiltrados.length - 1);
                        }
                    },
                    onCiudadTextoInput(ev) {
                        this.ubicMsg = '';
                        this.ciudadOpen = true;
                        this.ciudadQuery = String(ev?.target?.value ?? '');
                        this.ciudadTexto = this.ciudadQuery;
                        this.ciudadId = '';
                        this.ciudadActivoIdx = 0;
                        this.refrescarCiudadesFiltrados();
                    },
                    ciudadMover(delta) {
                        if (!this.ciudadOpen) this.ciudadOpen = true;
                        const n = this.ciudadesFiltrados.length;
                        if (n === 0) return;
                        const next = this.ciudadActivoIdx + delta;
                        this.ciudadActivoIdx = Math.min(n - 1, Math.max(0, next));
                    },
                    ciudadConfirmarActivo() {
                        const c = this.ciudadesFiltrados[this.ciudadActivoIdx];
                        if (c) this.seleccionarCiudad(c);
                    },
                    seleccionarCiudad(c) {
                        this.ciudadId = String(c.id);
                        this.ciudadTexto = this.textoCiudadRow(c);
                        this.ciudadQuery = this.ciudadTexto;
                        this.ciudadOpen = false;
                    },
                    refrescarMunicipiosFiltrados() {
                        const q = String(this.municipioQuery || '').trim().toLowerCase();
                        const base = this.municipiosLista || [];
                        const rows = q ? base.filter(r => String(r.nombre || '').toLowerCase().includes(q)) : base;
                        this.municipiosFiltrados = rows.slice(0, 80);
                        if (this.municipioActivoIdx >= this.municipiosFiltrados.length) {
                            this.municipioActivoIdx = Math.max(0, this.municipiosFiltrados.length - 1);
                        }
                    },
                    onMunicipioTextoInput(ev) {
                        this.ubicMsg = '';
                        this.municipioOpen = true;
                        this.municipioQuery = String(ev?.target?.value ?? '');
                        this.municipioTexto = this.municipioQuery;
                        this.municipioId = '';
                        this.municipioActivoIdx = 0;
                        this.parroquiaId = '';
                        this.parroquiaTexto = '';
                        this.parroquiaQuery = '';
                        this.parroquiaOpen = false;
                        this.parroquiaActivoIdx = 0;
                        this.parroquiasLista = [];
                        this.refrescarMunicipiosFiltrados();
                        this.refrescarParroquiasFiltrados();
                    },
                    municipioMover(delta) {
                        if (!this.municipioOpen) this.municipioOpen = true;
                        const n = this.municipiosFiltrados.length;
                        if (n === 0) return;
                        const next = this.municipioActivoIdx + delta;
                        this.municipioActivoIdx = Math.min(n - 1, Math.max(0, next));
                    },
                    municipioConfirmarActivo() {
                        const m = this.municipiosFiltrados[this.municipioActivoIdx];
                        if (m) this.seleccionarMunicipio(m);
                    },
                    seleccionarMunicipio(m) {
                        this.municipioId = String(m.id);
                        this.municipioTexto = String(m.nombre || '');
                        this.municipioQuery = this.municipioTexto;
                        this.municipioOpen = false;
                        this.cargarParroquiasPorMunicipio(this.municipioId, '');
                    },
                    refrescarParroquiasFiltrados() {
                        const q = String(this.parroquiaQuery || '').trim().toLowerCase();
                        const base = this.parroquiasLista || [];
                        const rows = q ? base.filter(r => String(r.nombre || '').toLowerCase().includes(q)) : base;
                        this.parroquiasFiltrados = rows.slice(0, 80);
                        if (this.parroquiaActivoIdx >= this.parroquiasFiltrados.length) {
                            this.parroquiaActivoIdx = Math.max(0, this.parroquiasFiltrados.length - 1);
                        }
                    },
                    onParroquiaTextoInput(ev) {
                        this.ubicMsg = '';
                        this.parroquiaOpen = true;
                        this.parroquiaQuery = String(ev?.target?.value ?? '');
                        this.parroquiaTexto = this.parroquiaQuery;
                        this.parroquiaId = '';
                        this.parroquiaActivoIdx = 0;
                        this.refrescarParroquiasFiltrados();
                    },
                    parroquiaMover(delta) {
                        if (!this.parroquiaOpen) this.parroquiaOpen = true;
                        const n = this.parroquiasFiltrados.length;
                        if (n === 0) return;
                        const next = this.parroquiaActivoIdx + delta;
                        this.parroquiaActivoIdx = Math.min(n - 1, Math.max(0, next));
                    },
                    parroquiaConfirmarActivo() {
                        const p = this.parroquiasFiltrados[this.parroquiaActivoIdx];
                        if (p) this.seleccionarParroquia(p);
                    },
                    seleccionarParroquia(p) {
                        this.parroquiaId = String(p.id);
                        this.parroquiaTexto = String(p.nombre || '');
                        this.parroquiaQuery = this.parroquiaTexto;
                        this.parroquiaOpen = false;
                    },
                    async cargarUbicacionInicial() {
                        const est = String(this.estadoId || '').trim();
                        if (!est) {
                            this.deshabilitarUbicacionSinEstado();
                            return;
                        }
                        await Promise.all([
                            this.cargarCiudadesPorEstado(est, this.ciudadInicial ? String(this.ciudadInicial) : ''),
                            this.cargarMunicipiosPorEstado(est, this.municipioInicial ? String(this.municipioInicial) : ''),
                        ]);
                        const mid = String(this.municipioId || '').trim();
                        if (mid) {
                            await this.cargarParroquiasPorMunicipio(mid, this.parroquiaInicial ? String(this.parroquiaInicial) : '');
                        } else {
                            this.parroquiasLista = [];
                            this.parroquiaId = '';
                            this.parroquiaTexto = '';
                            this.parroquiaQuery = '';
                            this.refrescarParroquiasFiltrados();
                        }
                    },
                    async onCambioEstado() {
                        this.ubicMsg = '';
                        const est = String(this.estadoId || '').trim();
                        if (!est) {
                            this.deshabilitarUbicacionSinEstado();
                            return;
                        }
                        this.ciudadOpen = false;
                        this.municipioOpen = false;
                        this.parroquiaOpen = false;
                        await Promise.all([
                            this.cargarCiudadesPorEstado(est, ''),
                            this.cargarMunicipiosPorEstado(est, ''),
                        ]);
                        this.parroquiasLista = [];
                        this.parroquiaId = '';
                        this.parroquiaTexto = '';
                        this.parroquiaQuery = '';
                        this.refrescarParroquiasFiltrados();
                    },
                    async cargarCiudadesPorEstado(idEstado, seleccionarId) {
                        this.ubicCargandoCiudades = true;
                        this.ciudadOpen = false;
                        try {
                            const res = await fetch(this.ciudadesUrl + '?' + new URLSearchParams({ id_estado: idEstado }).toString(), {
                                headers: { Accept: 'application/json' },
                                credentials: 'same-origin',
                            });
                            const data = await res.json();
                            if (!data.ok) throw new Error('bad');
                            this.ciudadesLista = (data.ciudades || []).map(c => ({
                                id: String(c.id_ciudad),
                                nombre: String(c.nombre_ciudad || ''),
                                es_capital: !!c.es_capital,
                            }));
                            const sid = seleccionarId ? String(seleccionarId) : '';
                            if (sid) {
                                const row = this.ciudadesLista.find(r => r.id === sid);
                                if (row) {
                                    this.ciudadId = row.id;
                                    this.ciudadTexto = this.textoCiudadRow(row);
                                    this.ciudadQuery = this.ciudadTexto;
                                } else {
                                    this.ciudadId = '';
                                    this.ciudadTexto = '';
                                    this.ciudadQuery = '';
                                }
                            } else {
                                this.ciudadId = '';
                                this.ciudadTexto = '';
                                this.ciudadQuery = '';
                            }
                        } catch (e) {
                            this.ciudadesLista = [];
                            this.ciudadId = '';
                            this.ciudadTexto = '';
                            this.ciudadQuery = '';
                        } finally {
                            this.ubicCargandoCiudades = false;
                        }
                        this.refrescarCiudadesFiltrados();
                    },
                    async cargarMunicipiosPorEstado(idEstado, seleccionarId) {
                        this.ubicCargandoMunicipios = true;
                        this.municipioOpen = false;
                        try {
                            const res = await fetch(this.municipiosUrl + '?' + new URLSearchParams({ id_estado: idEstado }).toString(), {
                                headers: { Accept: 'application/json' },
                                credentials: 'same-origin',
                            });
                            const data = await res.json();
                            if (!data.ok) throw new Error('bad');
                            this.municipiosLista = (data.municipios || []).map(m => ({
                                id: String(m.id_municipio),
                                nombre: String(m.nombre_municipio || ''),
                            }));
                            const sid = seleccionarId ? String(seleccionarId) : '';
                            if (sid) {
                                const row = this.municipiosLista.find(r => r.id === sid);
                                if (row) {
                                    this.municipioId = row.id;
                                    this.municipioTexto = row.nombre;
                                    this.municipioQuery = this.municipioTexto;
                                } else {
                                    this.municipioId = '';
                                    this.municipioTexto = '';
                                    this.municipioQuery = '';
                                }
                            } else {
                                this.municipioId = '';
                                this.municipioTexto = '';
                                this.municipioQuery = '';
                            }
                        } catch (e) {
                            this.municipiosLista = [];
                            this.municipioId = '';
                            this.municipioTexto = '';
                            this.municipioQuery = '';
                        } finally {
                            this.ubicCargandoMunicipios = false;
                        }
                        this.refrescarMunicipiosFiltrados();
                    },
                    async cargarParroquiasPorMunicipio(idMunicipio, seleccionarId) {
                        if (!idMunicipio) {
                            this.parroquiasLista = [];
                            this.parroquiaId = '';
                            this.parroquiaTexto = '';
                            this.parroquiaQuery = '';
                            this.refrescarParroquiasFiltrados();
                            return;
                        }
                        this.ubicCargandoParroquias = true;
                        this.parroquiaOpen = false;
                        try {
                            const res = await fetch(this.parroquiasUrl + '?' + new URLSearchParams({ id_municipio: idMunicipio }).toString(), {
                                headers: { Accept: 'application/json' },
                                credentials: 'same-origin',
                            });
                            const data = await res.json();
                            if (!data.ok) throw new Error('Respuesta no OK');
                            this.parroquiasLista = (data.parroquias || []).map(p => ({
                                id: String(p.id_parroquia),
                                nombre: String(p.nombre_parroquia || ''),
                            }));
                            const sid = seleccionarId ? String(seleccionarId) : '';
                            if (sid) {
                                const row = this.parroquiasLista.find(r => r.id === sid);
                                if (row) {
                                    this.parroquiaId = row.id;
                                    this.parroquiaTexto = row.nombre;
                                    this.parroquiaQuery = this.parroquiaTexto;
                                } else {
                                    this.parroquiaId = '';
                                    this.parroquiaTexto = '';
                                    this.parroquiaQuery = '';
                                }
                            } else {
                                this.parroquiaId = '';
                                this.parroquiaTexto = '';
                                this.parroquiaQuery = '';
                            }
                        } catch (e) {
                            this.parroquiasLista = [];
                            this.parroquiaId = '';
                            this.parroquiaTexto = '';
                            this.parroquiaQuery = '';
                        } finally {
                            this.ubicCargandoParroquias = false;
                        }
                        this.refrescarParroquiasFiltrados();
                    },
                    maxDigitosDoc() {
                        return millenniumMaxDigitosCliente(this.tipoDocumento);
                    },
                    actualizarHintTipo() {
                        this.hintTipo = this.hintsPorTipo[this.tipoDocumento] || '';
                    },
                    onCambioTipo() {
                        const max = this.maxDigitosDoc();
                        let s = String(this.documentoNumero).replace(/\D/g, '');
                        if (s.length > max) {
                            s = s.slice(0, max);
                            this.documentoNumero = s;
                            const docEl = document.getElementById('documento_numero');
                            if (docEl) {
                                docEl.value = s;
                            }
                        }
                        this.actualizarHintTipo();
                        this.docDuplicadoMsg = '';
                        this.docClienteMsg = '';
                        this.verificarDocumentoRemoto();
                    },
                    digitosDocumentoDesdeDom() {
                        const el = document.getElementById('documento_numero');
                        return String(el?.value ?? this.documentoNumero).replace(/\D/g, '');
                    },
                    async verificarDocumentoRemoto() {
                        const digitos = this.digitosDocumentoDesdeDom();
                        if (digitos.length === 0) {
                            this.docDuplicadoMsg = '';
                            return;
                        }
                        const params = new URLSearchParams({
                            tipo_documento: this.tipoDocumento,
                            documento_numero: digitos,
                        });
                        if (this.clienteId) {
                            params.set('cliente_id', String(this.clienteId));
                        }
                        try {
                            const res = await fetch(this.checkUrl + '?' + params.toString(), {
                                headers: { Accept: 'application/json' },
                                credentials: 'same-origin',
                            });
                            const data = await res.json();
                            if (!data.ok) {
                                return;
                            }
                            if (data.formato_valido === false) {
                                this.docClienteMsg = data.mensaje || '';
                                this.docDuplicadoMsg = '';
                                return;
                            }
                            this.docClienteMsg = '';
                            if (data.disponible === false) {
                                this.docDuplicadoMsg = data.mensaje || 'Ya existe un cliente con este documento.';
                            } else {
                                this.docDuplicadoMsg = '';
                            }
                        } catch (e) {
                            /* Millennium: sin red no bloqueamos el envío; el servidor valida */
                        }
                    },
                    validarNombre() {
                        const el = document.getElementById('nombre_razon_social');
                        const v = (el?.value || '').trim();
                        if (v.length > 0 && v.length < 3) {
                            this.nombreMsg = 'Usá al menos 3 caracteres para el nombre o razón social.';
                        } else {
                            this.nombreMsg = '';
                        }
                    },
                    validarZona() {
                        const sel = String(this.zonaSelect || '').trim();
                        if (sel === '__otra__') {
                            const otra = (document.getElementById('zona_otra')?.value || '').trim();
                            if (otra.length > 0 && otra.length < 2) {
                                this.zonaMsg = 'Indicá la ruta o sector con al menos 2 caracteres.';
                            } else {
                                this.zonaMsg = '';
                            }
                            return;
                        }
                        this.zonaMsg = '';
                    },
                    validarTelefono() {
                        const el = document.getElementById('telefono');
                        const raw = (el?.value || '').trim();
                        if (raw === '') {
                            this.telMsg = '';
                            return;
                        }
                        const d = raw.replace(/\D/g, '');
                        if (!/^04\d{9}$/.test(d)) {
                            this.telMsg = 'Si cargás teléfono, usá 11 dígitos comenzando por 04 (móvil).';
                        } else {
                            this.telMsg = '';
                        }
                    },
                    async enviarCliente($event) {
                        this.ubicMsg = '';
                        const idEst = String(this.estadoId || '').trim();
                        if (!idEst) {
                            this.ubicMsg = 'Elegí el estado del cliente (obligatorio para reportes por zona).';
                            $event.preventDefault();
                            return;
                        }
                        this.validarNombre();
                        this.validarZona();
                        this.validarTelefono();
                        const docInput = document.getElementById('documento_numero');
                        if (docInput) {
                            docInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        const telInput = document.getElementById('telefono');
                        if (telInput) {
                            telInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        const digitos = this.digitosDocumentoDesdeDom();
                        if (digitos.length === 0) {
                            this.docClienteMsg = 'Ingresá el número de documento.';
                            $event.preventDefault();
                            return;
                        }
                        await this.verificarDocumentoRemoto();
                        if (this.docClienteMsg || this.docDuplicadoMsg) {
                            $event.preventDefault();
                            return;
                        }
                        if (this.nombreMsg || this.zonaMsg || this.telMsg) {
                            $event.preventDefault();
                            return;
                        }
                        const nom = (document.getElementById('nombre_razon_social')?.value || '').trim();
                        if (nom.length < 3) {
                            this.nombreMsg = 'Completá el nombre (mín. 3 caracteres).';
                            $event.preventDefault();
                            return;
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
