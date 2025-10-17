<section class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-8 border-t-4 border-primary">
    <header class="mb-6">
        <h2 class="text-2xl font-bold text-primary">Actualizar contraseña</h2>
        <p class="mt-1 text-sm text-gray-600">
            Usa una contraseña segura y única para mantener tu cuenta protegida.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <!-- Contraseña actual -->
        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" class="font-semibold text-gray-800" />
            <x-text-input id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-2 block w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                autocomplete="current-password"
                required
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <!-- Nueva contraseña -->
        <div>
            <x-input-label for="update_password_password" value="Nueva contraseña" class="font-semibold text-gray-800" />
            <x-text-input id="update_password_password"
                name="password"
                type="password"
                class="mt-2 block w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                autocomplete="new-password"
                required
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar contraseña -->
        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar contraseña" class="font-semibold text-gray-800" />
            <x-text-input id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-2 block w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                autocomplete="new-password"
                required
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="bg-secondary hover:bg-green-700 text-white font-semibold">
                Guardar cambios
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm text-green-600 font-medium">
                    Contraseña actualizada correctamente.
                </p>
            @endif
        </div>
    </form>
</section>
