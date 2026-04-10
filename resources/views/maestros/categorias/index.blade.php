<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Categorías de producto</h2>
            <a href="{{ route('categorias.create') }}"><x-primary-button type="button">Nueva categoría</x-primary-button></a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif
            @if ($errors->has('error'))
                <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-800 dark:text-red-200">{{ $errors->first('error') }}</div>
            @endif

            <form method="get" class="flex flex-wrap gap-2 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="buscar" value="Buscar por nombre o código" />
                    <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full" value="{{ request('buscar') }}" />
                </div>
                <x-secondary-button type="submit" class="h-10">Buscar</x-secondary-button>
                @if (request('buscar'))
                    <a href="{{ route('categorias.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Limpiar</a>
                @endif
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-3 font-medium">Código</th>
                                <th class="px-4 py-3 font-medium">Nombre</th>
                                <th class="px-4 py-3 font-medium">Descripción</th>
                                <th class="px-4 py-3 font-medium text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($categorias as $c)
                                <tr class="text-gray-900 dark:text-gray-100">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $c->codigo }}</td>
                                    <td class="px-4 py-3">{{ $c->nombre }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-md truncate">{{ $c->descripcion }}</td>
                                    <td class="px-4 py-3 text-end space-x-2 whitespace-nowrap">
                                        <a href="{{ route('categorias.edit', $c) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Editar</a>
                                        <form action="{{ route('categorias.destroy', $c) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay categorías.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($categorias->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">{{ $categorias->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
