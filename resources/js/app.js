import './bootstrap';

import Alpine from 'alpinejs';

/**
 * Normaliza texto numérico a string con punto decimal (compatible con PHP/Laravel).
 * @param {string|number} evValue
 * @returns {string}
 */
function millenniumNormalizarNumero(evValue) {
    const s = String(evValue ?? '').trim();
    if (!s) return '';
    const cleaned = s.replace(/[^\d.,-]/g, '');
    const neg = cleaned.startsWith('-');
    const body = neg ? cleaned.slice(1) : cleaned;

    if (!body.includes(',')) {
        const parts = body.split('.');
        if (parts.length >= 2 && parts.every((p) => /^\d+$/.test(p))) {
            const firstOk = parts[0].length >= 1 && parts[0].length <= 3;
            const restAreThree = parts.slice(1).every((p) => p.length === 3);
            if (firstOk && restAreThree) {
                return (neg ? '-' : '') + parts.join('');
            }
        }
    }

    const lastComma = body.lastIndexOf(',');
    const lastDot = body.lastIndexOf('.');
    const decSep = lastComma > lastDot ? ',' : lastDot > -1 ? '.' : '';
    let intPart = body;
    let decPart = '';
    if (decSep) {
        const idx = body.lastIndexOf(decSep);
        intPart = body.slice(0, idx);
        decPart = body.slice(idx + 1);
    }
    intPart = intPart.replace(/[.,]/g, '');
    decPart = decPart.replace(/[.,]/g, '');
    const out = (neg ? '-' : '') + intPart + (decPart ? '.' + decPart : '');
    return out === '-' ? '' : out;
}

/**
 * Quita letras y basura; el último , o . define decimales (coma gana si está a la derecha del punto).
 * Salida con punto decimal; máx. maxFrac dígitos en la parte fraccionaria.
 */
function millenniumSanitizarDecimalTeclado(s, maxFrac) {
    let t = String(s ?? '').replace(/[^\d.,-]/g, '');
    const neg = t.startsWith('-');
    if (neg) t = t.slice(1).replace(/-/g, '');
    if (t === '') return neg ? '' : '';

    const lastComma = t.lastIndexOf(',');
    const lastDot = t.lastIndexOf('.');
    const endsWithSep = /[.,]$/.test(t);

    if (lastComma < 0 && lastDot < 0) {
        const digits = t.replace(/\D/g, '');
        if (!digits) return '';
        const stripLead = digits.replace(/^0+(?=\d)/, '') || '0';
        return (neg ? '-' : '') + stripLead;
    }

    const commaIsDec = lastComma > lastDot;
    const decPos = commaIsDec ? lastComma : lastDot;
    let intRaw = t.slice(0, decPos);
    const decRaw = t.slice(decPos + 1);

    if (commaIsDec) {
        intRaw = intRaw.replace(/\./g, '');
    } else {
        intRaw = intRaw.replace(/,/g, '');
    }
    let entero = intRaw.replace(/[^\d]/g, '');
    if (entero === '') {
        entero = '0';
    } else {
        entero = String(parseInt(entero, 10));
    }
    const dec = decRaw.replace(/[^\d]/g, '').slice(0, maxFrac);

    let body;
    if (dec.length > 0) {
        body = `${entero}.${dec}`;
    } else if (endsWithSep) {
        body = `${entero}.`;
    } else {
        body = entero;
    }
    return neg ? `-${body}` : body;
}

function millenniumSanitizarMontoUsdTeclado(s) {
    return millenniumSanitizarDecimalTeclado(s, 2);
}

function millenniumSanitizarValorTasaTeclado(s) {
    return millenniumSanitizarDecimalTeclado(s, 10);
}

function millenniumBlurMontoUsd2dec(el) {
    const raw = String(el?.value ?? '');
    const v = millenniumSanitizarMontoUsdTeclado(raw);
    if (v === '' || v === '-' || v === '.' || v === '-.') {
        el.value = '';
        return;
    }
    const toParse = v.endsWith('.') ? v.slice(0, -1) : v;
    const n = parseFloat(toParse);
    if (Number.isNaN(n)) {
        el.value = '';
        return;
    }
    el.value = n.toFixed(2);
}

function millenniumBlurValorTasa(el) {
    let v = millenniumSanitizarValorTasaTeclado(String(el?.value ?? ''));
    if (v === '' || v === '-' || v === '.' || v === '-.') {
        el.value = '';
        return;
    }
    if (v.endsWith('.')) {
        v = v.slice(0, -1);
    }
    const n = parseFloat(v);
    if (Number.isNaN(n)) {
        el.value = '';
        return;
    }
    el.value = v;
}

/** Cobranza: inputs de fila «Abono USD» y similares (sin Alpine en la fila). */
function millenniumOnInputMontoUsdField(el) {
    if (!el) return;
    const v = millenniumSanitizarMontoUsdTeclado(el.value);
    if (el.value !== v) {
        el.value = v;
    }
}

function millenniumOnBlurMontoUsdField(el) {
    if (!el) return;
    millenniumBlurMontoUsd2dec(el);
}

if (typeof window !== 'undefined') {
    window.millenniumSanitizarMontoUsdTeclado = millenniumSanitizarMontoUsdTeclado;
    window.millenniumOnInputMontoUsdField = millenniumOnInputMontoUsdField;
    window.millenniumOnBlurMontoUsdField = millenniumOnBlurMontoUsdField;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('reporteFiltroEstado', (opts = {}) => ({
        estadosData: Array.isArray(opts.estadosData) ? opts.estadosData : [],
        estadoInicial: opts.estadoInicial || '',
        estadoId: '',
        estadoTexto: '',
        estadoQuery: '',
        estadoOpen: false,
        estadoActivoIdx: 0,
        estadosFiltrados: [],
        init() {
            this.estadoId = this.estadoInicial ? String(this.estadoInicial) : '';
            const row = (this.estadosData || []).find((r) => String(r.id) === String(this.estadoId));
            this.estadoTexto = row ? String(row.nombre) : '';
            this.estadoQuery = this.estadoTexto;
            this.refrescarEstadosFiltrados();
        },
        refrescarEstadosFiltrados() {
            const q = String(this.estadoQuery || '').trim().toLowerCase();
            const base = this.estadosData || [];
            const rows = q
                ? base.filter((r) => String(r.nombre || '').toLowerCase().includes(q))
                : base;
            this.estadosFiltrados = rows.slice(0, 50);
            if (this.estadoActivoIdx >= this.estadosFiltrados.length) {
                this.estadoActivoIdx = Math.max(0, this.estadosFiltrados.length - 1);
            }
        },
        onEstadoTextoInput(ev) {
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
            if (e) this.seleccionarEstado(e);
        },
        seleccionarEstado(e) {
            this.estadoId = String(e.id);
            this.estadoTexto = String(e.nombre);
            this.estadoQuery = this.estadoTexto;
            this.estadoOpen = false;
        },
    }));

    Alpine.data('facturasCobranzaSeleccion', (opts = {}) => ({
        base: opts.baseUrl ?? '',
        seleccion: [],
        err: '',
        esta(id) {
            return this.seleccion.some((s) => s.id === id);
        },
        toggle(ev, id, clienteId) {
            if (!ev.target.checked) {
                this.seleccion = this.seleccion.filter((s) => s.id !== id);
                this.err = '';
                return;
            }
            if (this.seleccion.length && this.seleccion[0].cliente_id !== clienteId) {
                ev.target.checked = false;
                this.err = 'Solo podés seleccionar facturas del mismo cliente para cobranza conjunta.';
                return;
            }
            this.seleccion.push({ id, cliente_id: clienteId });
            this.err = '';
        },
        limpiar() {
            this.seleccion = [];
            this.err = '';
            const root = this.$refs.cobWrap;
            if (root) {
                root.querySelectorAll('input[data-cob-fac]').forEach((cb) => {
                    cb.checked = false;
                });
            }
        },
        urlCliente() {
            if (!this.seleccion.length) {
                return '#';
            }
            const cid = this.seleccion[0].cliente_id;
            const ids = this.seleccion.map((s) => s.id).join(',');
            return `${this.base}/${cid}?destacar=${encodeURIComponent(ids)}`;
        },
    }));

    Alpine.data('millenniumAssistant', (opts = {}) => ({
        open: false,
        loading: false,
        input: '',
        messages: [],
        sendUrl: opts.sendUrl ?? '',
        pageRoute: opts.pageRoute ?? '',
        toggle() {
            this.open = !this.open;
        },
        async send() {
            const text = String(this.input ?? '').trim();
            if (!text || this.loading || !this.sendUrl) {
                return;
            }
            this.messages.push({ role: 'user', text });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => {
                const box = this.$refs.msgBox;
                if (box) {
                    box.scrollTop = box.scrollHeight;
                }
            });
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            try {
                const res = await fetch(this.sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        message: text,
                        page_route: this.pageRoute || null,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.messages.push({
                        role: 'assistant',
                        text: typeof data.error === 'string' ? data.error : 'No se pudo completar la solicitud.',
                        isError: true,
                    });
                } else if (data.reply) {
                    this.messages.push({ role: 'assistant', text: data.reply });
                } else {
                    this.messages.push({
                        role: 'assistant',
                        text: 'Respuesta inesperada del servidor.',
                        isError: true,
                    });
                }
            } catch {
                this.messages.push({
                    role: 'assistant',
                    text: 'Error de red. Revisá la conexión.',
                    isError: true,
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    const box = this.$refs.msgBox;
                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                });
            }
        },
    }));

    Alpine.data('cobranzaPagoPorMetodo', (opts = {}) => ({
        metodo: opts.metodoInicial ?? 'zelle',
        pagoMovil: opts.pagoMovil ?? 'pago_movil',
        transferencia: opts.transferencia ?? 'transferencia',
        efectivo: opts.efectivo ?? 'efectivo',
        usdt: opts.usdt ?? 'usdt',
        /** Con “No tengo comprobante”: desactiva el archivo y quita el required en el navegador. */
        sinComprobante: Boolean(opts.sinComprobanteInicial),
        valorTasa: opts.oldValorTasa != null ? String(opts.oldValorTasa) : '',
        montoBsRaw: opts.oldMontoBs != null ? String(opts.oldMontoBs) : '',
        montoBsDisplay: '',
        /** Solo pago de una factura: rellena monto USD al cambiar Bs o tasa. */
        sincronizarUsdDesdeBs: opts.sincronizarUsdDesdeBs ?? false,
        montoUsdPm:
            opts.oldMontoAplicadoUsd != null && opts.oldMontoAplicadoUsd !== ''
                ? String(opts.oldMontoAplicadoUsd)
                : '',
        init() {
            this.montoBsDisplay = this.formatearBsVE(this.montoBsRaw);
            if (this.montoUsdPm !== '' && this.montoUsdPm != null) {
                const v = millenniumSanitizarMontoUsdTeclado(String(this.montoUsdPm));
                const toParse = v.endsWith('.') ? v.slice(0, -1) : v;
                const n = parseFloat(toParse);
                this.montoUsdPm = !Number.isNaN(n) && toParse !== '' && toParse !== '-' ? n.toFixed(2) : v;
            }
            if (this.valorTasa !== '' && this.valorTasa != null) {
                this.valorTasa = millenniumSanitizarValorTasaTeclado(String(this.valorTasa));
            }
            if (this.sincronizarUsdDesdeBs && this.metodo === this.pagoMovil) {
                this.aplicarEquivUsdAPm();
            }
            this.$watch('sinComprobante', (v) => {
                if (v) {
                    const el = this.$refs.comprobante;
                    if (el) {
                        el.value = '';
                    }
                }
            });
            if (!this.sincronizarUsdDesdeBs) {
                return;
            }
            this.$watch('metodo', (m) => {
                if (m === this.pagoMovil) {
                    this.aplicarEquivUsdAPm();
                }
            });
            this.$watch('valorTasa', () => this.aplicarEquivUsdAPm());
            this.$watch('montoBsRaw', () => this.aplicarEquivUsdAPm());
        },
        normalizarNumero(evValue) {
            return millenniumNormalizarNumero(evValue);
        },
        formatearBsVE(raw) {
            const n = parseFloat(String(raw ?? ''));
            if (!raw || isNaN(n)) return '';
            // Formato VE: miles con '.' y decimales con ','
            return n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        /**
         * Solo dígitos, '.' (miles) y ',' (decimal). Máximo 2 dígitos después de la coma.
         * Cualquier letra u otro carácter se elimina.
         */
        sanitizarMontoBsTeclado(s) {
            let t = String(s ?? '').replace(/[^\d.,]/g, '');
            const i = t.indexOf(',');
            if (i === -1) {
                return t.replace(/,/g, '');
            }
            const entero = t.slice(0, i).replace(/[^\d.]/g, '');
            const dec = t
                .slice(i + 1)
                .replace(/[^\d]/g, '')
                .slice(0, 2);
            const e = entero === '' ? '0' : entero;

            return `${e},${dec}`;
        },
        /**
         * Formato VE automático (ej. 100.000,00). Sanitiza siempre.
         * Mientras tipeás montos chicos sin coma (ej. 15 → 150) no reformatea para no cortar el flujo;
         * con coma o miles claros o al terminar de escribir (n ≥ 1000) sí aplica miles y ,00.
         */
        aplicarFormatoMontoBsDesdeTeclado(typed) {
            const limpio = this.sanitizarMontoBsTeclado(typed);
            if (limpio === '') {
                this.montoBsRaw = '';
                this.montoBsDisplay = '';

                return;
            }
            const raw = this.normalizarNumero(limpio);
            this.montoBsRaw = raw;
            const n = parseFloat(String(raw).replace(',', '.'));
            const sinComa = !limpio.includes(',');
            const terminaEnSeparador = /[.,]$/.test(limpio);
            const partesComa = limpio.split(',');
            const decTrasComa = partesComa.length > 1 ? partesComa[1] : '';
            const comaEnCurso = limpio.includes(',') && (terminaEnSeparador || decTrasComa.length < 2);

            if (terminaEnSeparador || comaEnCurso || Number.isNaN(n) || raw === '') {
                this.montoBsDisplay = limpio;

                return;
            }
            if (sinComa && n < 1000 && limpio.length < 4) {
                this.montoBsDisplay = limpio;

                return;
            }
            this.montoBsDisplay = this.formatearBsVE(raw);
        },
        onMontoBsInput(ev) {
            const typed = String(ev?.target?.value ?? '');
            this.aplicarFormatoMontoBsDesdeTeclado(typed);
            this.$nextTick(() => {
                const el = ev?.target;
                if (el && el.value !== this.montoBsDisplay) {
                    el.value = this.montoBsDisplay;
                }
            });
            this.aplicarEquivUsdAPm();
        },
        onMontoUsdInput(ev) {
            const typed = String(ev?.target?.value ?? '');
            this.montoUsdPm = millenniumSanitizarMontoUsdTeclado(typed);
            this.$nextTick(() => {
                const el = ev?.target;
                if (el && el.value !== this.montoUsdPm) {
                    el.value = this.montoUsdPm;
                }
            });
        },
        onMontoUsdBlur(ev) {
            const el = ev?.target;
            if (!el) return;
            millenniumBlurMontoUsd2dec(el);
            this.montoUsdPm = el.value;
        },
        onValorTasaInput(ev) {
            const typed = String(ev?.target?.value ?? '');
            this.valorTasa = millenniumSanitizarValorTasaTeclado(typed);
            this.$nextTick(() => {
                const el = ev?.target;
                if (el && el.value !== this.valorTasa) {
                    el.value = this.valorTasa;
                }
            });
            if (this.sincronizarUsdDesdeBs) {
                this.aplicarEquivUsdAPm();
            }
        },
        onValorTasaBlur(ev) {
            const el = ev?.target;
            if (!el) return;
            millenniumBlurValorTasa(el);
            this.valorTasa = el.value;
            if (this.sincronizarUsdDesdeBs) {
                this.aplicarEquivUsdAPm();
            }
        },
        onMontoBsBlur(ev) {
            const typed = String(ev?.target?.value ?? '');
            const limpio = this.sanitizarMontoBsTeclado(typed);
            if (limpio === '') {
                this.montoBsRaw = '';
                this.montoBsDisplay = '';
            } else {
                this.montoBsRaw = this.normalizarNumero(limpio);
                const n = parseFloat(String(this.montoBsRaw).replace(',', '.'));
                this.montoBsDisplay =
                    this.montoBsRaw !== '' && !Number.isNaN(n) ? this.formatearBsVE(this.montoBsRaw) : limpio;
            }
            this.aplicarEquivUsdAPm();
        },
        aplicarEquivUsdAPm() {
            if (!this.sincronizarUsdDesdeBs || this.metodo !== this.pagoMovil) {
                return;
            }
            const eq = this.equivUsd();
            if (eq) {
                this.montoUsdPm = eq;
            }
        },
        grupo() {
            if (this.metodo === this.pagoMovil) {
                return 'pago_movil';
            }
            if (this.metodo === this.efectivo) {
                return 'efectivo';
            }
            if (this.metodo === this.usdt) {
                return 'usdt';
            }
            return 'transferencia';
        },
        permiteTasaBs() {
            return this.metodo === this.pagoMovil || this.metodo === this.transferencia;
        },
        equivUsd() {
            const t = parseFloat(String(this.valorTasa).replace(',', '.'));
            const b = parseFloat(String(this.montoBsRaw).replace(',', '.'));
            if (!t || t <= 0 || !b || b <= 0) {
                return '';
            }
            return (Math.round((b / t) * 100) / 100).toFixed(2);
        },
    }));

    Alpine.data('cobranzaClientePicker', (opts = {}) => ({
        clientesData: Array.isArray(opts.clientesData) ? opts.clientesData : [],
        baseUrl: opts.baseUrl ?? '',
        query: opts.query ?? '',
        open: false,
        activoIdx: 0,
        init() {
            this.query = String(this.query || '');
        },
        get filtrados() {
            const q = String(this.query || '').trim().toLowerCase();
            const base = this.clientesData || [];
            const rows = q
                ? base.filter((c) => {
                      const nombre = String(c?.nombre || '').toLowerCase();
                      const rif = String(c?.rif || '').toLowerCase();
                      const zona = String(c?.zona || '').toLowerCase();
                      return (
                          nombre.includes(q) ||
                          rif.includes(q) ||
                          zona.includes(q) ||
                          String(c?.id || '').includes(q)
                      );
                  })
                : base;
            return rows.slice(0, 10);
        },
        onInput(ev) {
            this.open = true;
            this.query = String(ev?.target?.value ?? '');
            this.activoIdx = 0;
        },
        mover(delta) {
            if (!this.open) this.open = true;
            const n = this.filtrados.length;
            if (n === 0) return;
            const next = this.activoIdx + delta;
            this.activoIdx = Math.min(n - 1, Math.max(0, next));
            this.$nextTick(() => {
                const list = this.$refs.list;
                const el = list?.querySelector?.(`[data-idx="${this.activoIdx}"]`);
                if (el && typeof el.scrollIntoView === 'function') {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        },
        confirmarActivo() {
            const c = this.filtrados[this.activoIdx];
            if (c) this.seleccionar(c);
        },
        seleccionar(c) {
            if (!c || !this.baseUrl) return;
            window.location.href = `${this.baseUrl}/${encodeURIComponent(String(c.id))}`;
        },
        cerrar() {
            this.open = false;
        },
    }));
});

window.Alpine = Alpine;

Alpine.start();
