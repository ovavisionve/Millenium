import './bootstrap';

import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
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

    Alpine.data('cobranzaPagoPorMetodo', (opts = {}) => ({
        metodo: opts.metodoInicial ?? 'zelle',
        pagoMovil: opts.pagoMovil ?? 'pago_movil',
        efectivo: opts.efectivo ?? 'efectivo',
        usdt: opts.usdt ?? 'usdt',
        valorTasa: opts.oldValorTasa != null ? String(opts.oldValorTasa) : '',
        montoBs: opts.oldMontoBs != null ? String(opts.oldMontoBs) : '',
        /** Solo pago de una factura: rellena monto USD al cambiar Bs o tasa. */
        sincronizarUsdDesdeBs: opts.sincronizarUsdDesdeBs ?? false,
        montoUsdPm:
            opts.oldMontoAplicadoUsd != null && opts.oldMontoAplicadoUsd !== ''
                ? String(opts.oldMontoAplicadoUsd)
                : '',
        init() {
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
            this.$watch('montoBs', () => this.aplicarEquivUsdAPm());
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
        equivUsd() {
            const t = parseFloat(String(this.valorTasa).replace(',', '.'));
            const b = parseFloat(String(this.montoBs).replace(',', '.'));
            if (!t || t <= 0 || !b || b <= 0) {
                return '';
            }
            return (Math.round((b / t) * 100) / 100).toFixed(2);
        },
    }));
});

window.Alpine = Alpine;

Alpine.start();
