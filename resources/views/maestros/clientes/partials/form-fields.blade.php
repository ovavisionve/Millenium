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
@endphp
<div
    class="space-y-4"
    x-data="clienteFormMillennium({
        checkUrl: @json(route('clientes.check-documento')),
        clienteId: @json($isEdit ? $cliente->id : null),
        documentoInicial: @json($oldDocDigits),
    })"
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
            <x-input-label for="zona" value="Zona / sector / ruta" />
            <x-text-input
                id="zona"
                name="zona"
                type="text"
                class="mt-1 block w-full"
                :value="old('zona', $isEdit ? $cliente->zona : '')"
                @blur="validarZona()"
            />
            <p x-show="zonaMsg" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="zonaMsg" role="alert"></p>
            <x-input-error class="mt-2" :messages="$errors->get('zona')" />
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
                    clienteId: opts.clienteId,
                    tipoDocumento: 'V',
                    documentoNumero: opts.documentoInicial || '',
                    docClienteMsg: '',
                    docDuplicadoMsg: '',
                    nombreMsg: '',
                    telMsg: '',
                    zonaMsg: '',
                    hintTipo: '',
                    hintsPorTipo: {
                        V: 'Cédula venezolana: 6 a 8 dígitos (solo números).',
                        E: 'Cédula extranjero: 6 a 8 dígitos (solo números).',
                        J: 'RIF persona jurídica: exactamente 9 dígitos.',
                        G: 'RIF gubernamental: exactamente 9 dígitos.',
                        P: 'Pasaporte: 8 o 9 dígitos.',
                    },
                    init() {
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
                        const el = document.getElementById('zona');
                        const v = (el?.value || '').trim();
                        if (v.length > 0 && v.length < 2) {
                            this.zonaMsg = 'Indicá la zona con al menos 2 caracteres.';
                        } else {
                            this.zonaMsg = '';
                        }
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
                        const zo = (document.getElementById('zona')?.value || '').trim();
                        if (nom.length < 3) {
                            this.nombreMsg = 'Completá el nombre (mín. 3 caracteres).';
                            $event.preventDefault();
                            return;
                        }
                        if (zo.length < 2) {
                            this.zonaMsg = 'Completá la zona (mín. 2 caracteres).';
                            $event.preventDefault();
                            return;
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
