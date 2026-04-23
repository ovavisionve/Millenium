import './bootstrap';

import Alpine from 'alpinejs';

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
            if (this.sincronizarUsdDesdeBs && this.metodo === this.pagoMovil) {
                this.aplicarEquivUsdAPm();
            }
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
            const s = String(evValue ?? '').trim();
            if (!s) return '';
            // Permite: 340000 | 340.000 | 340.000,50 | 340000,50 | 340000.50
            // Quitamos todo menos dígitos, punto y coma; luego resolvemos decimal.
            const cleaned = s.replace(/[^\d.,-]/g, '');
            const neg = cleaned.startsWith('-');
            const body = neg ? cleaned.slice(1) : cleaned;
            const lastComma = body.lastIndexOf(',');
            const lastDot = body.lastIndexOf('.');
            const decSep = lastComma > lastDot ? ',' : (lastDot > -1 ? '.' : '');
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
            // Evitar cosas raras como '-' solo
            return out === '-' ? '' : out;
        },
        formatearBsVE(raw) {
            const n = parseFloat(String(raw ?? ''));
            if (!raw || isNaN(n)) return '';
            // Formato VE: miles con '.' y decimales con ','
            return n.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        onMontoBsInput(ev) {
            const raw = this.normalizarNumero(ev?.target?.value);
            this.montoBsRaw = raw;
            this.montoBsDisplay = raw ? this.formatearBsVE(raw) : '';
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
