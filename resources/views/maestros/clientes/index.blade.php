<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Clientes</h2>
            <a href="{{ route('clientes.create') }}"><x-primary-button type="button">Nuevo cliente</x-primary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <form method="get" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[180px]">
                    <x-input-label for="buscar" value="Buscar" />
                    <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full" value="{{ request('buscar') }}" placeholder="Nombre, zona o documento" />
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
                @if (request()->anyFilled(['buscar', 'vendedor_id']))
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
                                <th class="px-4 py-3 font-medium">Zona</th>
                                <th class="px-4 py-3 font-medium">Vendedor</th>
                                <th class="px-4 py-3 font-medium text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($clientes as $c)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-4 py-3 font-mono text-xs">{{ $c->full_identificacion }}</td>
                                <td class="px-4 py-3">{{ $c->nombre_razon_social }}</td>
                                <td class="px-4 py-3">{{ $c->telefono ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $c->zona }}</td>
                                <td class="px-4 py-3">{{ $c->vendedor?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-end space-x-2 whitespace-nowrap">
                                    <a href="{{ route('clientes.edit', $c) }}" class="text-millennium-dark dark:text-millennium-sand hover:underline">Editar</a>
                                    <form action="{{ route('clientes.destroy', $c) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar este cliente?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Eliminar</button>
                                    </form>
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