<x-app-layout>
    <div class="p-6 max-w-lg mx-auto bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-primary mb-6">Registrar nuevo usuario</h1>

        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            <div class="mb-4">
                <x-input-label value="Nombre completo" />
                <x-text-input name="name" class="w-full" value="{{ old('name') }}" required />
            </div>

            <div class="mb-4">
                <x-input-label value="Correo electrónico" />
                <x-text-input name="email" type="email" class="w-full" value="{{ old('email') }}" required />
            </div>

            <div class="mb-4">
                <x-input-label value="Contraseña" />
                <x-text-input name="password" type="password" class="w-full" required />
            </div>

            <div class="mb-4">
                <x-input-label value="Confirmar contraseña" />
                <x-text-input name="password_confirmation" type="password" class="w-full" required />
            </div>

            <div class="mb-4">
                <x-input-label value="Rol del usuario" />
                <select name="role_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Seleccione un rol</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <x-primary-button>Guardar</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
