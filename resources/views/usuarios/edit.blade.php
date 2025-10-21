<x-app-layout>
    <div class="p-6 max-w-lg mx-auto bg-white shadow-md rounded-lg">
        <h1 class="text-2xl font-bold text-primary mb-6">Editar usuario</h1>

        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <x-input-label value="Nombre completo" />
                <x-text-input name="name" class="w-full" value="{{ old('name', $usuario->name) }}" required />
            </div>

            <div class="mb-4">
                <x-input-label value="Correo electrónico" />
                <x-text-input name="email" type="email" class="w-full" value="{{ old('email', $usuario->email) }}" required />
            </div>

            <div class="mb-4">
                <x-input-label value="Rol del usuario" />
                <select name="role_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}" @selected($usuario->role_id == $rol->id)>
                            {{ $rol->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <x-primary-button>Actualizar</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
