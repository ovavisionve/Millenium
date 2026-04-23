<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start gap-3">
            <a href="{{ route('vendedores.index') }}"
               class="mt-0.5 inline-flex shrink-0 items-center gap-2 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-millennium-sand">
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 4.158a.75.75 0 1 1-1.06 1.06l-5.5-5.5a.75.75 0 0 1 0-1.06l5.5-5.5a.75.75 0 1 1 1.06 1.06L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                </svg>
                <span>Atrás</span>
            </a>
            <div class="min-w-0">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                    Nuevo vendedor
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Se creará un usuario con rol <strong>Vendedor</strong> (aparecerá en el selector de clientes).
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('vendedores.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Nombre visible" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Correo (inicio de sesión)" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Contraseña" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700 text-millennium-dark shadow-sm focus:ring-millennium-sand"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <x-input-label for="is_active" value="Usuario activo (puede iniciar sesión)" class="!mb-0" />
                    </div>

                    <div class="flex items-center gap-2 pt-4">
                        <x-primary-button>Crear vendedor</x-primary-button>
                        <a href="{{ route('vendedores.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
