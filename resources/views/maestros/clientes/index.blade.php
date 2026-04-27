<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-1">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Clientes</h2>
                <a href="{{ route('datos-maestros.index') }}" class="text-sm text-millennium-dark dark:text-millennium-sand hover:underline w-fit">← Datos maestros</a>
            </div>
            <a href="{{ route('clientes.create') }}"><x-primary-button type="button">Nuevo cliente</x-primary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <form
                method="get"
                class="flex flex-wrap gap-3 items-end"
                x-data="{
                    estados: @js($estadosData),
                    estadoId: @js((string) request('id_estado', '')),
                    estadoTexto: '',
                    estadoOpen: false,
                    estadoActivoIdx: 0,
                    init() {
                        this.syncEstadoTexto();
                    },
                    syncEstadoTexto() {
                        const id = String(this.estadoId ?? '');
                        if (!id) {
                            this.estadoTexto = '';
                            return;
                        }
                        const row = (this.estados || []).find((e) => String(e.id) === id);
                        this.estadoTexto = row ? String(row.nombre) : '';
                    },
                    estadosFiltrados() {
                        const q = String(this.estadoTexto || '').trim().toLowerCase();
                        const base = this.estados || [];
                        const rows = q
                            ? base.filter((e) => String(e.nombre || '').toLowerCase().includes(q))
                            : base;
                        return rows.slice(0, 50);
                    },
                    estadoMover(delta) {
                        if (!this.estadoOpen) this.estadoOpen = true;
                        const lista = this.estadosFiltrados();
                        const n = lista.length;
                        if (n <= 0) return;
                        this.estadoActivoIdx = (this.estadoActivoIdx + delta + n) % n;
                    },
                    estadoConfirmarActivo() {
                        const lista = this.estadosFiltrados();
                        const e = lista[this.estadoActivoIdx];
                        if (e) this.seleccionarEstado(e);
                    },
                    seleccionarEstado(e) {
                        if (!e) return;
                        this.estadoId = String(e.id);
                        this.estadoTexto = String(e.nombre);
                        this.estadoOpen = false;
                    },
                    limpiarEstado() {
                        this.estadoId = '';
                        this.estadoTexto = '';
                        this.estadoOpen = false;
                        this.estadoActivoIdx = 0;
                    },
                    onEstadoBlur() {
                        window.setTimeout(() => {
                            if (this.estadoOpen) return;
                            if (String(this.estadoId || '').trim() !== '') return;
                            const q = String(this.estadoTexto || '').trim().toLowerCase();
                            if (!q) return;
                            const exact = (this.estados || []).some((e) => String(e.nombre || '').trim().toLowerCase() === q);
                            if (!exact) this.estadoTexto = '';
                        }, 0);
                    },
                }"
            >
                <div class="flex-1 min-w-[180px]">
                    <x-input-label for="buscar" value="Buscar" />
                    <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full" value="{{ request('buscar') }}" placeholder="Documento, nombre, teléfono…" />
                </div>
                <div class="min-w-[220px] flex-1 sm:max-w-xs">
                    <x-input-label for="estado_buscar_clientes" value="Ubicado" />
                    <div class="relative mt-1" @click.outside="estadoOpen = false">
                        <input type="hidden" name="id_estado" :value="estadoId" />
                        <input
                            id="estado_buscar_clientes"
                            type="text"
                            autocomplete="off"
                            placeholder="Escribí: Portuguesa, Aragua…"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-millennium-dark focus:ring-millennium-sand"
                            x-model="estadoTexto"
                            @focus="estadoOpen = true"
                            @input="estadoId = ''; estadoOpen = true; estadoActivoIdx = 0"
                            @keydown.down.prevent="estadoMover(1)"
                            @keydown.up.prevent="estadoMover(-1)"
                            @keydown.enter.prevent="estadoConfirmarActivo()"
                            @keydown.escape.prevent="estadoOpen = false"
                            @blur="onEstadoBlur()"
                        />
                        <div
                            x-show="estadoOpen"
                            x-cloak
                            class="absolute left-0 right-0 z-50 mt-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
                            style="top: calc(100% + 0.25rem);"
                        >
                            <div class="max-h-64 overflow-auto py-1">
                                <button
                                    type="button"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                    @click="limpiarEstado()"
                                >
                                    <span class="font-medium">Todas</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400"> · sin filtrar por estado</span>
                                </button>
                                <template x-for="(e, idx) in estadosFiltrados()" :key="e.id">
                                    <button
                                        type="button"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
                                        :class="idx === estadoActivoIdx ? 'bg-gray-50 dark:bg-gray-800' : ''"
                                        @click="seleccionarEstado(e)"
                                    >
                                        <span class="font-medium" x-text="e.nombre"></span>
                                    </button>
                                </template>
                                <div x-show="estadosFiltrados().length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    No hay coincidencias.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="min-w-[220px]">
                    <x-input-label for="vendedor_id" value="Vendedor" />
                    <select id="vendedor_id" name="vendedor_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        @foreach ($vendedores as $v)
                        <option value="{{ $v->id }}" @selected((string) request('vendedor_id')===(string) $v->id)>{{ $v->name }} ({{ \App\Models\User::roleLabels()[$v->role] ?? $v->role }})</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit" class="h-10">Filtrar</x-secondary-button>
                @if (request()->anyFilled(['buscar', 'vendedor_id', 'id_estado']))
                <a href="{{ route('clientes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Limpiar</a>
                @endif
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-3 font-medium">Documento</th>
                                <th class="px-4 py-3 font-medium">Nombre / razón social</th>
                                <th class="px-4 py-3 font-medium">Teléfono</th>
                                <th class="px-4 py-3 font-medium">Estado / ubicación</th>
                                <th class="px-4 py-3 font-medium">Vendedor</th>
                                <th class="sticky right-0 z-10 min-w-[16rem] bg-gray-50 px-4 py-3 text-end font-medium shadow-[-4px_0_8px_-2px_rgba(0,0,0,0.06)] dark:bg-gray-700 sm:min-w-[17.5rem]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($clientes as $c)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-4 py-3 font-mono text-xs">{{ $c->full_identificacion }}</td>
                                <td class="px-4 py-3">{{ $c->nombre_razon_social }}</td>
                                <td class="px-4 py-3">{{ $c->telefono ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $u = [];
                                        if ($c->estado) {
                                            $u[] = $c->estado->nombre_estado;
                                        }
                                        if ($c->ciudad) {
                                            $u[] = $c->ciudad->nombre_ciudad;
                                        }
                                        if ($c->municipio) {
                                            $u[] = $c->municipio->nombre_municipio;
                                        }
                                        if ($c->parroquia) {
                                            $u[] = $c->parroquia->nombre_parroquia;
                                        }
                                    @endphp
                                    {{ $u !== [] ? implode(' · ', $u) : '—' }}
                                </td>
                                <td class="px-4 py-3">{{ $c->vendedor?->name ?? '—' }}</td>
                                <td class="sticky right-0 z-10 min-w-[16rem] bg-white px-3 py-3 text-end align-middle shadow-[-4px_0_8px_-2px_rgba(0,0,0,0.06)] dark:bg-gray-800 sm:min-w-[17.5rem]">
                                    <x-maestro-fila-acciones
                                        :edit-url="route('clientes.edit', $c)"
                                        :delete-url="route('clientes.destroy', $c)"
                                        delete-confirm="¿Eliminar este cliente?"
                                        :delete-aria-label="'Eliminar cliente ' . $c->nombre_razon_social"
                                    />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay clientes.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($clientes->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">{{ $clientes->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>