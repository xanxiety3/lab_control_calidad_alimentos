@if (auth()->user()->isAdmin())
    <section class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-8 border-t-4 border-red-600 mt-10">
        <header class="mb-6">
            <h2 class="text-2xl font-bold text-red-600">Eliminar cuenta</h2>
            <p class="mt-1 text-sm text-gray-600">
                Una vez eliminada, todos los datos y recursos asociados se perderán permanentemente.
            </p>
        </header>

        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-md shadow-md">
            Eliminar cuenta
        </x-danger-button>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-semibold text-gray-900">
                    ¿Estás seguro de eliminar esta cuenta?
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Esta acción no se puede deshacer. Introduce tu contraseña para confirmar.
                </p>

                <div class="mt-6">
                    <x-text-input id="password" name="password" type="password"
                        class="block w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                        placeholder="Contraseña" required />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')" class="mr-3">
                        Cancelar
                    </x-secondary-button>
                    <x-danger-button class="bg-red-600 hover:bg-red-700 text-white">
                        Confirmar eliminación
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    </section>

@endif
