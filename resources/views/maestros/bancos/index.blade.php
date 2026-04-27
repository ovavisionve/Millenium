<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Bancos</h2>
            <a href="{{ route('bancos.create') }}"><x-primary-button type="button">Nuevo banco</x-primary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <form method="get" class="flex flex-wrap gap-2 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="buscar" value="Buscar banco" />
                    <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full" value="{{ request('buscar') }}" />
                </div>
                <x-secondary-button type="submit" class="h-10">Buscar</x-secondary-button>
                @if (request('buscar'))
                <a href="{{ route('bancos.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Limpiar</a>
                @endif
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nombre</th>
                                <th class="px-4 py-3 font-medium">Activo</th>
                                <th class="px-4 py-3 font-medium">Descripción</th>
                                <th class="px-4 py-3 font-medium text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($bancos as $banco)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-4 py-3">{{ $banco->nombre }}</td>
                                <td class="px-4 py-3">{{ $banco->activo ? 'Sí' : 'No' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-md truncate">{{ $banco->descripcion }}</td>
                                <td class="px-4 py-3 text-end align-middle">
                                    <x-maestro-fila-acciones
                                        :edit-url="route('bancos.edit', $banco)"
                                        :delete-url="route('bancos.destroy', $banco)"
                                        delete-confirm="¿Eliminar este banco?"
                                        :delete-aria-label="'Eliminar banco ' . $banco->nombre"
                                    />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay bancos cargados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($bancos->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">{{ $bancos->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

