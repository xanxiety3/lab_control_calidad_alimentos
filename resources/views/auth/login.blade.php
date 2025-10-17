<x-guest-layout>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white rounded-xl shadow-lg p-10 max-w-md w-full border-t-4 border-secondary">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-lcca.png') }}" alt="Logo LCCA" class="h-16 w-auto">
            </div>

            <h2 class="text-2xl font-bold text-primary text-center mb-6">
                Iniciar Sesión
            </h2>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Correo electrónico" />
                    <x-text-input id="email"
                        class="block mt-1 w-full border-gray-300 focus:border-primary focus:ring-primary"
                        type="email" name="email" :value="old('email')" required autofocus
                        autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" value="Contraseña" />
                    <x-text-input id="password"
                        class="block mt-1 w-full border-gray-300 focus:border-primary focus:ring-primary"
                        type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox"
                            class="rounded border-gray-300 text-secondary shadow-sm focus:ring-secondary"
                            name="remember">
                        <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-between mt-6">
                    @if (Route::has('password.request'))
                        <a class="text-sm text-primary hover:underline"
                            href="{{ route('password.request') }}">
                            ¿Olvidó su contraseña?
                        </a>
                    @endif

                    <x-primary-button class="bg-secondary hover:bg-green-700 text-white font-semibold">
                        Ingresar
                    </x-primary-button>
                </div>
            </form>

            <!-- Botón volver -->
            <div class="mt-8 text-center">
                <a href="{{ url('/') }}"
                   class="inline-block bg-primary hover:bg-blue-900 text-white px-6 py-2 rounded-lg font-semibold transition">
                    ← Volver al inicio
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
