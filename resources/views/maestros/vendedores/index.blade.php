<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                    Vendedores
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Personas que pueden elegirse en <strong>Cliente → Vendedor asignado</strong> (vendedor, verificador o administrador activo).
                </p>
            </div>
            <a href="{{ route('vendedores.create') }}">
                <x-primary-button type="button">Nuevo vendedor</x-primary-button>
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

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Para crear administradores o verificadores usá <a href="{{ route('usuarios.index') }}" class="text-millennium-dark dark:text-millennium-sand font-medium hover:underline">Usuarios</a>.
            </p>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nombre</th>
                                <th class="px-4 py-3 font-medium">Correo</th>
                                <th class="px-4 py-3 font-medium">Rol</th>
                                <th class="px-4 py-3 font-medium text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($vendedores as $v)
                                <tr class="text-gray-900 dark:text-gray-100">
                                    <td class="px-4 py-3">{{ $v->name }}</td>
                                    <td class="px-4 py-3">{{ $v->email }}</td>
                                    <td class="px-4 py-3">{{ $roleLabels[$v->role] ?? $v->role }}</td>
                                    <td class="px-4 py-3 text-end align-middle">
                                        <x-maestro-fila-acciones :edit-url="route('usuarios.edit', $v)" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        No hay usuarios activos asignables. Creá un vendedor con el botón de arriba.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
