<section class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-8 border-t-4 border-primary">
    <header class="mb-6">
        <h2 class="text-2xl font-bold text-primary">
            Información del Perfil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Actualiza los datos de tu cuenta y dirección de correo electrónico.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- Nombre -->
        <div>
            <x-input-label for="name" value="Nombre completo" class="text-gray-800 font-semibold" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-2 block w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Correo electrónico" class="text-gray-800 font-semibold" />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-2 block w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-gray-800">
                        Tu dirección de correo no está verificada.
                        <button form="send-verification"
                            class="underline text-sm text-primary hover:text-secondary font-semibold">
                            Reenviar enlace de verificación
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            Se ha enviado un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Botón Guardar -->
        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="bg-secondary hover:bg-green-700 text-white font-semibold">
                Guardar cambios
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm text-green-600 font-medium"
                >
                    Guardado correctamente.
                </p>
            @endif
        </div>
    </form>
</section>
