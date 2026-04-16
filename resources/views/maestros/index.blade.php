<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Datos maestros</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Un solo lugar para cargar clientes, categorías (líneas de venta), bancos y vendedores. Así el menú principal queda simple y podés sumar módulos nuevos sin perder claridad.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('clientes.index') }}" class="block rounded-lg border border-millennium-dark/15 dark:border-millennium-sand/25 bg-white dark:bg-gray-800 p-5 shadow-sm hover:bg-millennium-sand/10 dark:hover:bg-gray-700/50 transition">
                    <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">Clientes</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Alta, correo, dirección y zona comercial para reportes.</p>
                </a>
                <a href="{{ route('categorias.index') }}" class="block rounded-lg border border-millennium-dark/15 dark:border-millennium-sand/25 bg-white dark:bg-gray-800 p-5 shadow-sm hover:bg-millennium-sand/10 dark:hover:bg-gray-700/50 transition">
                    <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">Categorías</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Líneas de venta (p. ej. vaca, búfalo): código, nombre y cómo se factura (ud. o kg).</p>
                </a>
                <a href="{{ route('bancos.index') }}" class="block rounded-lg border border-millennium-dark/15 dark:border-millennium-sand/25 bg-white dark:bg-gray-800 p-5 shadow-sm hover:bg-millennium-sand/10 dark:hover:bg-gray-700/50 transition">
                    <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">Bancos</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Catálogo para que Cobranza muestre bancos predeterminados como Banesco Karina o Banesco Nelson.</p>
                </a>
                <a href="{{ route('vendedores.index') }}" class="block rounded-lg border border-millennium-dark/15 dark:border-millennium-sand/25 bg-white dark:bg-gray-800 p-5 shadow-sm hover:bg-millennium-sand/10 dark:hover:bg-gray-700/50 transition">
                    <h3 class="font-semibold text-millennium-dark dark:text-millennium-sand">Vendedores</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Usuarios con rol vendedor para asignar a clientes y reportes.</p>
                </a>
            </div>

            @if ($esAdmin)
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Administración de accesos completos: <a href="{{ route('usuarios.index') }}" class="text-millennium-dark dark:text-millennium-sand hover:underline">Usuarios</a>.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
