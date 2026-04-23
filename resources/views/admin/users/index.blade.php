<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Usuarios del sistema
            </h2>
            <a href="{{ route('usuarios.create') }}">
                <x-primary-button type="button">Nuevo usuario</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-200">
                {{ session('status') }}
            </div>
            @endif

            @if ($errors->has('error'))
            <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-800 dark:text-red-200">
                {{ $errors->first('error') }}
            </div>
            @endif

            <form method="get" action="{{ route('usuarios.index') }}" class="flex flex-wrap gap-2 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="buscar" value="Buscar por nombre o correo" />
                    <x-text-input id="buscar" name="buscar" type="text" class="mt-1 block w-full"
                        value="{{ request('buscar') }}" placeholder="Ej. María o @dominio" />
                </div>
                <x-secondary-button type="submit" class="h-10">Buscar</x-secondary-button>
                @if (request('buscar'))
                <a href="{{ route('usuarios.index') }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300">Limpiar</a>
                @endif
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nombre</th>
                                <th class="px-4 py-3 font-medium">Correo</th>
                                <th class="px-4 py-3 font-medium">Rol</th>
                                <th class="px-4 py-3 font-medium">Estado</th>
                                <th class="px-4 py-3 font-medium text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($users as $u)
                            <tr class="text-gray-900 dark:text-gray-100">
                                <td class="px-4 py-3">{{ $u->name }}</td>
                                <td class="px-4 py-3">{{ $u->email }}</td>
                                <td class="px-4 py-3">{{ $roleLabels[$u->role] ?? $u->role }}</td>
                                <td class="px-4 py-3">
                                    @if ($u->is_active)
                                    <span class="text-green-600 dark:text-green-400">Activo</span>
                                    @else
                                    <span class="text-gray-500">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end space-x-2 whitespace-nowrap">
                                    <a href="{{ route('usuarios.edit', $u) }}" class="text-millennium-dark dark:text-millennium-sand hover:underline">Editar</a>
                                    @if ($u->id !== auth()->id())
                                    <form action="{{ route('usuarios.destroy', $u) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Eliminar</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No hay usuarios.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-600">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>