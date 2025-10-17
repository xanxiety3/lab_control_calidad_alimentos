<x-guest-layout>
    <div class="flex items-center justify-center h-screen bg-gray-100">
        <div class="bg-white rounded-xl shadow-lg p-10 w-full max-w-lg border-t-4 border-secondary">
            
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/logo-lcca.png') }}" alt="Logo LCCA" class="h-16 w-auto">
            </div>

            <!-- Título -->
            <h2 class="text-2xl font-bold text-primary text-center mb-4">
                ¿Olvidó su contraseña?
            </h2>

            <p class="text-gray-600 text-center mb-6">
                No hay problema. Ingrese su correo electrónico y le enviaremos un enlace para restablecer su contraseña.
            </p>

            <!-- Estado de sesión -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Formulario -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Correo electrónico" />
                    <x-text-input id="email"
                        class="block mt-1 w-full border-gray-300 focus:border-primary focus:ring-primary"
                        type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('login') }}"
                       class="text-sm text-primary hover:underline">
                        ← Volver
                    </a>

                    <x-primary-button class="bg-secondary hover:bg-green-700 text-white font-semibold">
                        Enviar enlace
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
