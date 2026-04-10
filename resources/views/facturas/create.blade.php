<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Nueva factura</h2>
    </x-slot>

    @php
        $defaultLineas = old('lineas', [['producto_id' => '', 'cantidad' => '', 'precio_unitario' => '']]);
    @endphp

    <div class="py-6" x-data="{ lineas: @js($defaultLineas) }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Carga manual de control: <strong>fecha</strong>, <strong>cliente</strong> (con su zona y vendedor para reportes), <strong>días de crédito</strong> y líneas con
                <strong>kilos/cantidad</strong> y <strong>precio real</strong> por producto. Al guardar, la factura alimenta la cartera “en la calle” y la cartera se clasifica (vencida / por vencer / al día).
            </p>
            <form method="post" action="{{ route('facturas.store') }}" class="space-y-6">
                @csrf

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Datos generales</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="cliente_id" value="Cliente" />
                            <select id="cliente_id" name="cliente_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                <option value="">— Seleccione —</option>
                                @foreach ($clientes as $c)
                                    <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>{{ $c->nombre_razon_social }} — {{ $c->full_identificacion }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('cliente_id')" />
                        </div>
                        <div>
                            <x-input-label for="numero_factura" value="Nº factura (opcional)" />
                            <x-text-input id="numero_factura" name="numero_factura" type="text" class="mt-1 block w-full font-mono" :value="old('numero_factura')" placeholder="Ej. B-8674" />
                            <x-input-error class="mt-2" :messages="$errors->get('numero_factura')" />
                        </div>
                        <div>
                            <x-input-label for="fecha_emision" value="Fecha de emisión" />
                            <x-text-input id="fecha_emision" name="fecha_emision" type="date" class="mt-1 block w-full" :value="old('fecha_emision', now()->toDateString())" required />
                            <x-input-error class="mt-2" :messages="$errors->get('fecha_emision')" />
                        </div>
                        <div>
                            <x-input-label for="dias_credito" value="Días de crédito" />
                            <x-text-input id="dias_credito" name="dias_credito" type="number" min="0" class="mt-1 block w-full" :value="old('dias_credito', 0)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('dias_credito')" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Líneas de producto</h3>
                        <button type="button" @click="lineas.push({ producto_id: '', cantidad: '', precio_unitario: '' })" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">+ Agregar línea</button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                <tr>
                                    <th class="px-2 py-2 text-left">Producto</th>
                                    <th class="px-2 py-2 text-left w-28">Cantidad</th>
                                    <th class="px-2 py-2 text-left w-32">Precio unit.</th>
                                    <th class="px-2 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(linea, index) in lineas" :key="index">
                                    <tr class="border-t border-gray-200 dark:border-gray-600">
                                        <td class="px-2 py-2">
                                            <select class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][producto_id]'" x-model="linea.producto_id" required>
                                                <option value="">— Producto —</option>
                                                @foreach ($productos as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->categoria->nombre }}) — {{ $p->codigo }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="0.001" min="0.001" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][cantidad]'" x-model="linea.cantidad" required />
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" step="0.0001" min="0" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" x-bind:name="'lineas[' + index + '][precio_unitario]'" x-model="linea.precio_unitario" required />
                                        </td>
                                        <td class="px-1 py-2">
                                            <button type="button" class="text-red-600 text-xs" @click="if (lineas.length > 1) lineas.splice(index, 1)">✕</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <x-input-error :messages="$errors->get('lineas')" class="mt-2" />
                </div>

                <div class="flex items-center gap-2">
                    <x-primary-button>Guardar factura</x-primary-button>
                    <a href="{{ route('facturas.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
