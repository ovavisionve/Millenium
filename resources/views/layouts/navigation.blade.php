{{--
    Millennium / Incapor — barra principal

    Cambios: logo Incapor (`x-brand-logo`) en lugar del texto "Millennium"; contenedor
    `max-w-[148px]` para no robar espacio a los links. Orden de ítems alineado al flujo
    operativo (maestros → facturación/cobranza/reportes). Facturas (vigentes/canceladas), Cobranza,
    Estados de cuenta y Reportes en "Facturación". Usuarios: administración de accesos.
--}}
<nav x-data="{ open: false }" class="bg-white/95 border-b border-millennium-dark/10 shadow-sm backdrop-blur-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex min-w-0 flex-1">
                <!-- Logo -->
                {{-- Millennium: logo acotado — PNG ancho intrínseco sin tope empuja los `<x-nav-link>` fuera de vista --}}
                <div class="shrink-0 flex items-center max-w-[148px] sm:max-w-[160px]">
                    <a href="{{ route('dashboard') }}" class="block w-full rounded-md focus:outline-none focus:ring-2 focus:ring-millennium-sand focus:ring-offset-2">
                        <x-brand-logo variant="nav" class="w-full" />
                        <span class="sr-only">{{ config('app.name', 'Millennium') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                {{-- Millennium: sin scroll horizontal en escritorio (se ve feo y recorta dropdowns); agrupamos maestros para que quepa --}}
                <div class="hidden space-x-6 lg:space-x-8 sm:-my-px sm:ms-8 lg:ms-10 sm:flex min-w-0">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    {{-- Millennium — Datos maestros: agrupa clientes/categorías/productos para simplificar el menú --}}
                    <div class="flex items-center">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                @php
                                    $maestrosActive = request()->routeIs('datos-maestros.*') || request()->routeIs('clientes.*') || request()->routeIs('categorias.*') || request()->routeIs('productos.*')
                                        || request()->routeIs('vendedores.*');
                                    $maestrosClasses = $maestrosActive
                                        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-millennium-sand text-sm font-medium leading-5 text-millennium-dark dark:text-millennium-sand focus:outline-none focus:border-millennium-dark transition duration-150 ease-in-out'
                                        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-millennium-dark dark:hover:text-millennium-sand hover:border-millennium-sand/40 dark:hover:border-millennium-sand/30 focus:outline-none focus:text-millennium-dark dark:focus:text-millennium-sand focus:border-millennium-sand/40 transition duration-150 ease-in-out';
                                @endphp
                                <button type="button" class="{{ $maestrosClasses }}">
                                    Datos maestros
                                    <svg class="ms-1 h-4 w-4 fill-current opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('datos-maestros.index')">Resumen datos maestros</x-dropdown-link>
                                <x-dropdown-link :href="route('clientes.index')">Clientes</x-dropdown-link>
                                <x-dropdown-link :href="route('categorias.index')">Categorías</x-dropdown-link>
                                <x-dropdown-link :href="route('productos.index')">Productos</x-dropdown-link>
                                <x-dropdown-link :href="route('vendedores.index')">Vendedores</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    {{-- Millennium — Facturación: agrupa facturas, historial pagado, cobranza y reportes --}}
                    <div class="flex items-center">
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                @php
                                    $facturacionActive = request()->routeIs('facturas.*') || request()->routeIs('cobranza.*') || request()->routeIs('reportes.*')
                                        || request()->routeIs('cuentas-por-cobrar.*') || request()->routeIs('estados-cuenta.*');
                                    $facturacionClasses = $facturacionActive
                                        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-millennium-sand text-sm font-medium leading-5 text-millennium-dark dark:text-millennium-sand focus:outline-none focus:border-millennium-dark transition duration-150 ease-in-out'
                                        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-millennium-dark dark:hover:text-millennium-sand hover:border-millennium-sand/40 dark:hover:border-millennium-sand/30 focus:outline-none focus:text-millennium-dark dark:focus:text-millennium-sand focus:border-millennium-sand/40 transition duration-150 ease-in-out';
                                @endphp
                                <button type="button" class="{{ $facturacionClasses }}">
                                    Facturación
                                    <svg class="ms-1 h-4 w-4 fill-current opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('facturas.index')">Facturas y cobranza</x-dropdown-link>
                                <x-dropdown-link :href="route('cobranza.index')">Cobranza (por cliente)</x-dropdown-link>
                                <x-dropdown-link :href="route('cuentas-por-cobrar.index')">Estados de cuenta (por zona)</x-dropdown-link>
                                <x-dropdown-link :href="route('reportes.index')">Reportes</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @if (Auth::user()->isAdmin())
                    <x-nav-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">
                        Usuarios
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:shrink-0 sm:ms-4">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center px-3 py-2 min-h-[44px] border border-millennium-dark/10 text-sm leading-4 font-medium rounded-md text-millennium-dark/80 bg-white hover:bg-millennium-sand/15 focus:outline-none focus:ring-2 focus:ring-millennium-sand transition ease-in-out duration-150">
                            <div class="truncate max-w-[12rem] lg:max-w-xs">{{ Auth::user()->name }}</div>

                            <div class="ms-1 shrink-0">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center p-2 min-h-[44px] min-w-[44px] rounded-md text-millennium-dark/60 hover:text-millennium-dark hover:bg-millennium-sand/20 focus:outline-none focus:ring-2 focus:ring-millennium-sand transition ease-in-out duration-150" aria-expanded="false" :aria-expanded="open">
                    <span class="sr-only">Menú</span>
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-millennium-dark/10">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            {{-- Millennium — Datos maestros (móvil): grupo expandible --}}
            <div x-data="{ maestrosOpen: {{ (request()->routeIs('datos-maestros.*') || request()->routeIs('clientes.*') || request()->routeIs('categorias.*') || request()->routeIs('productos.*') || request()->routeIs('vendedores.*')) ? 'true' : 'false' }} }" class="border-l-4 border-transparent">
                <button type="button"
                    class="w-full flex items-center justify-between ps-3 pe-4 py-3 sm:py-2 text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-millennium-dark dark:hover:text-millennium-sand hover:bg-millennium-sand/10 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-millennium-sand/40"
                    @click="maestrosOpen = !maestrosOpen"
                    :aria-expanded="maestrosOpen.toString()"
                >
                    <span>Datos maestros</span>
                    <svg class="h-5 w-5 fill-current opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="maestrosOpen" x-cloak class="space-y-1">
                    <x-responsive-nav-link :href="route('datos-maestros.index')" :active="request()->routeIs('datos-maestros.*')">
                        Resumen datos maestros
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                        Clientes
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')">
                        Categorías
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')">
                        Productos
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('vendedores.index')" :active="request()->routeIs('vendedores.*')">
                        Vendedores
                    </x-responsive-nav-link>
                </div>
            </div>
            <div x-data="{ facturacionOpen: {{ (request()->routeIs('facturas.*') || request()->routeIs('cobranza.*') || request()->routeIs('reportes.*') || request()->routeIs('cuentas-por-cobrar.*') || request()->routeIs('estados-cuenta.*')) ? 'true' : 'false' }} }" class="border-l-4 border-transparent">
                <button type="button"
                    class="w-full flex items-center justify-between ps-3 pe-4 py-3 sm:py-2 text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-millennium-dark dark:hover:text-millennium-sand hover:bg-millennium-sand/10 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-millennium-sand/40"
                    @click="facturacionOpen = !facturacionOpen"
                    :aria-expanded="facturacionOpen.toString()"
                >
                    <span>Facturación</span>
                    <svg class="h-5 w-5 fill-current opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="facturacionOpen" x-cloak class="space-y-1">
                    <x-responsive-nav-link :href="route('facturas.index')" :active="request()->routeIs('facturas.*')">
                        Facturas y cobranza
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('cobranza.index')" :active="request()->routeIs('cobranza.*')">
                        Cobranza (por cliente)
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('cuentas-por-cobrar.index')" :active="request()->routeIs('cuentas-por-cobrar.*')">
                        Estados de cuenta (por zona)
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')">
                        Reportes
                    </x-responsive-nav-link>
                </div>
            </div>
            @if (Auth::user()->isAdmin())
            <x-responsive-nav-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">
                Usuarios
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-millennium-dark/10">
            <div class="px-4">
                <div class="font-medium text-base text-millennium-dark">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-millennium-dark/60 break-all">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>