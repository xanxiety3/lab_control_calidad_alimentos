<x-app-layout>
    <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-md p-6 mt-8">
        <h1 class="text-2xl font-bold text-primary mb-4 flex items-center">
            <x-heroicon-o-clipboard-document-list class="h-6 w-6 mr-2" />
            Detalle de Solicitud
        </h1>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">Datos del Cliente</h2>
                <p><strong>Nombre:</strong> {{ $solicitud->cliente->persona->nombre_completo }}</p>
                <p><strong>Documento:</strong> {{ $solicitud->cliente->persona->numero_documento }}</p>
                <p><strong>Teléfono:</strong> {{ $solicitud->cliente->telefono }}</p>
                <p><strong>Dirección:</strong> {{ $solicitud->cliente->direccion }}</p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">Datos de la Solicitud</h2>
                <p><strong>ID:</strong> {{ $solicitud->numero_solicitud }}</p>
                <p><strong>Fecha:</strong> {{ $solicitud->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Forma de entrega de resultados:</strong> {{ $solicitud->entrega_resultados }}</p>

                
            </div>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Muestras Asociadas</h2>
            <table class="w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Tipo</th>
                        <th class="border p-2 text-left">Cantidad</th>
                        <th class="border p-2 text-left">Refrigerada</th>
                        <th class="border p-2 text-left">Estado</th>
                        <th class="border p-2 text-left">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($solicitud->muestras as $muestra)
                        <tr>
                            <td class="border p-2">{{ $muestra->tipoMuestra->nombre ?? '-' }}</td>
                            <td class="border p-2">{{ $muestra->cantidad }}</td>
                            <td class="border p-2">{{ $muestra->refrigerada ? 'Sí' : 'No' }}</td>
                             <td class="border p-2">{{ ucfirst($muestra->estado ?? '-') }}</td>
                            <td class="border p-2">{{ $muestra->observaciones ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('dashboard.recepcion') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                Volver
            </a>
        </div>
    </div>
</x-app-layout>
