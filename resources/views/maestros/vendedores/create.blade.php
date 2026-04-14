<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Nuevo vendedor
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Se creará un usuario con rol <strong>Vendedor</strong> (aparecerá en el selector de clientes).
        </p>
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
