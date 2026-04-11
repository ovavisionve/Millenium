{{--
    Millennium / Incapor — login

    Lógica y rutas sin cambios (POST `login`, CSRF). Personalización: inputs/checkbox
    con `text-millennium-dark` / anillo `millennium-sand`; botón primario hereda tema marca.
--}}
<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Caja negra / UX: aviso visible cuando hay errores de validación o credenciales incorrectas --}}
    @if ($errors->any())
    <div class="mb-4 rounded-md border border-amber-300/80 bg-amber-50 px-4 py-3 text-sm text-millennium-dark dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100" role="alert" aria-live="polite">
        <p class="font-semibold">Revisá los datos del formulario</p>
        <p class="mt-1 text-millennium-dark/90 dark:text-amber-100/90">Si el correo o el formato no son válidos, o las credenciales no coinciden, corregí el campo indicado abajo.</p>
    </div>
    @endif

    {{-- novalidate: la validación la responde el servidor (mensajes en español); evita que el navegador bloquee el envío antes de tiempo. --}}
    @php
        $loginInputError = $errors->has('email') || $errors->has('password');
    @endphp
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <!-- Email Address — mismo contenedor visual que contraseña (millennium-login-field + app.css autofill) -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div @class([
                'millennium-login-field mt-1 flex min-h-[42px] w-full flex-nowrap items-stretch overflow-hidden rounded-md border bg-white shadow-sm focus-within:border-millennium-dark focus-within:ring-1 focus-within:ring-millennium-sand dark:bg-gray-900',
                'border-red-500 ring-1 ring-red-500/25 dark:border-red-500' => $loginInputError,
                'border-gray-300 dark:border-gray-700' => ! $loginInputError,
            ])>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    maxlength="255"
                    aria-describedby="email-error"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    class="min-h-[42px] min-w-0 flex-1 border-0 bg-white px-3 py-2 text-sm text-gray-900 shadow-none ring-0 placeholder:text-gray-400 focus:border-transparent focus:outline-none focus:ring-0 focus:ring-offset-0 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>
            <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password: ojo mostrar/ocultar (Alpine en resources/js/app.js); type nativo password + toggle por ref (sin atributo type duplicado). -->
        <div
            class="mt-4"
            x-data="{
                showPassword: false,
                togglePassword() {
                    this.showPassword = !this.showPassword;
                    this.$refs.pwd.type = this.showPassword ? 'text' : 'password';
                },
            }"
        >
            <x-input-label for="password" :value="__('Password')" />

            {{-- Flex + nowrap: una fila; ojo a la derecha. Si el layout falla, regenerá assets: npm run build --}}
            <div @class([
                'millennium-login-field mt-1 flex min-h-[42px] w-full flex-nowrap items-stretch overflow-hidden rounded-md border bg-white shadow-sm focus-within:border-millennium-dark focus-within:ring-1 focus-within:ring-millennium-sand dark:bg-gray-900',
                'border-red-500 ring-1 ring-red-500/25 dark:border-red-500' => $loginInputError,
                'border-gray-300 dark:border-gray-700' => ! $loginInputError,
            ])>
                <input
                    x-ref="pwd"
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    maxlength="255"
                    aria-describedby="password-error"
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    class="min-h-[42px] min-w-0 flex-1 border-0 bg-white px-3 py-2 text-sm text-gray-900 shadow-none ring-0 placeholder:text-gray-400 focus:border-transparent focus:outline-none focus:ring-0 focus:ring-offset-0 dark:bg-gray-900 dark:text-gray-100"
                />
                <button
                    type="button"
                    x-on:click="togglePassword()"
                    class="flex w-11 shrink-0 items-center justify-center border-0 bg-white p-0 text-gray-500 shadow-none outline-none ring-0 hover:bg-white hover:text-millennium-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-millennium-sand active:bg-white dark:bg-gray-900 dark:hover:bg-gray-900 dark:hover:text-millennium-sand"
                    x-bind:aria-pressed="showPassword"
                    x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                    <span x-show="!showPassword" x-cloak class="inline-flex" aria-hidden="true">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <span x-show="showPassword" x-cloak class="inline-flex" aria-hidden="true">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.182 4.182L12 12" />
                        </svg>
                    </span>
                </button>
            </div>

            <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-millennium-dark shadow-sm focus:ring-millennium-sand dark:focus:ring-millennium-sand dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
            <a class="underline text-sm text-millennium-dark/80 dark:text-millennium-sand/90 hover:text-millennium-dark dark:hover:text-millennium-sand rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-millennium-sand dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>