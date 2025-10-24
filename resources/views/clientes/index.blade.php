<x-app-layout>
    <div class="max-w-6xl mx-auto p-6 bg-white shadow-md rounded-lg mt-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-700">Clientes registrados</h1>
            <a href="{{ route('clientes.create') }}"
                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-blue-800 transition">+ Crear cliente</a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">ID</th>
                    <th class="border p-2 text-left">Nombre / Razón social</th>
                    <th class="border p-2 text-left">Documento</th>
                    <th class="border p-2 text-left">Tipo cliente</th>
                    <th class="border p-2 text-left">Teléfono</th>
                    <th class="border p-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $cliente)
                    <tr>
                        <td class="border p-2">{{ $cliente->id }}</td>
                        <td class="border p-2">
                            {{ $cliente->persona->tipo_persona == 'natural'
                                ? $cliente->persona->nombre_completo
                                : $cliente->persona->razon_social }}
                        </td>
                        <td class="border p-2">{{ $cliente->persona->numero_documento }}</td>
                        <td class="border p-2 capitalize">{{ $cliente->tipo_cliente }}</td>
                        <td class="border p-2">{{ $cliente->telefono }}</td>
                        <td class="border p-2">
                            <a href="{{ route('clientes.edit', $cliente->id) }}"
                                class="text-blue-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-gray-500">No hay clientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
