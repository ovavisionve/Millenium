<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Cobranza</h2>
            <a href="{{ route('facturas.index') }}"><x-secondary-button type="button">Facturas</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Buscá el cliente por nombre, zona o documento. Al tocarlo, vas directo a sus facturas con saldo para registrar abonos.
            </p>

            <div
                class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-gray-200 dark:border-gray-600 space-y-3"
                x-data="cobranzaClientePicker({
                    query: @js(old('q', $q)),
                    baseUrl: @js(str_replace('/0', '', route('cobranza.cliente', ['cliente' => 0]))),
                    clientesData: @js($clientesTodos->map(fn ($c) => [
                        'id' => (int) $c->id,
                        'nombre' => (string) $c->nombre_razon_social,
                        'rif' => (string) $c->full_identificacion,
                        'zona' => $c->zona ? (string) $c->zona : null,
                    ])->values()->all()),
                })"
                @click.outside="cerrar()"
            >
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Buscar cliente</p>
                <div class="flex flex-col gap-3">
                    <div>
                        <x-input-label for="q" value="Cliente" />
                        <x-text-input
                            id="q"
                            name="q"
                            type="text"
                            class="mt-1 block w-full"
                            x-model="query"
                            @focus="open = true"
                            @input="onInput($event)"
                            @keydown.down.prevent="mover(1)"
                            @keydown.up.prevent="mover(-1)"
                            @keydown.enter.prevent="confirmarActivo()"
                            @keydown.escape.prevent="cerrar()"
                            placeholder="Nombre, zona o documento"
                            autocomplete="off"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tip: escribí 2–3 letras y seleccioná en la lista.</p>
                    </div>

                    <div x-show="open" x-cloak class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <template x-if="filtrados.length === 0">
                            <p class="p-4 text-sm text-gray-500">No se encontraron clientes.</p>
                        </template>
                        <ul x-ref="list" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-72 overflow-auto">
                            <template x-for="(c, idx) in filtrados" :key="c.id">
                                <li>
                                    <button
                                        type="button"
                                        class="block w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        :class="idx === activoIdx ? 'bg-gray-50 dark:bg-gray-700/50' : ''"
                                        :data-idx="idx"
                                        @click="seleccionar(c)"
                                    >
                                        <span class="font-medium text-gray-900 dark:text-gray-100" x-text="c.nombre"></span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400" x-text="' · ' + c.rif"></span>
                                        <span class="text-xs text-gray-400" x-show="c.zona" x-text="' · ' + c.zona"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
