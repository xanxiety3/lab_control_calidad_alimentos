<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-10 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">

        {{-- Título principal --}}
        <h1 class="text-3xl font-extrabold text-gray-800 mb-10 flex items-center gap-3">
            <x-heroicon-o-clock class="h-8 w-8 text-primary" />
            Panel del Gestor Técnico
        </h1>

        @forelse ($muestras as $muestra)
            {{-- Tarjeta principal de muestra --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl transition-all duration-300 p-6 mb-8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <x-heroicon-o-beaker class="w-6 h-6 text-indigo-500" />
                            Muestra: <span class="text-gray-900">{{ $muestra->codigo_interno ?? 'Sin código' }}</span>
                        </h2>

                        <p class="text-sm text-gray-600 mt-1">
                            Estado:
                            <span class="font-semibold px-2 py-1 rounded-full text-xs shadow-sm
                                            @if ($muestra->estado === 'finalizada') bg-green-100 text-green-700
                                            @elseif($muestra->estado === 'en proceso') bg-yellow-100 text-yellow-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($muestra->estado ?? 'Pendiente') }}
                            </span>
                        </p>
                    </div>
                </div>

                @foreach ($muestra->ensayos as $ensayo)
                    <div class="p-3 border rounded mb-3">

                        <strong>Ensayo:</strong>
                        {{ $ensayo->nombre ?? '— sin nombre —' }}
                        <br>

                        <strong>Estado:</strong>
                        {{ ucfirst($ensayo->pivot->estado ?? 'pendiente') }}
                        <br>

                        <strong>Resultado:</strong>
                        {{ $ensayo->pivot->resultado !== null && $ensayo->pivot->resultado !== '' ? $ensayo->pivot->resultado : 'Sin resultado' }}
                        <br>

                        <strong>Observaciones:</strong>
                        {{ $ensayo->pivot->observaciones ?? '-' }}
                        <br>

                        <strong>Unidad:</strong>
                        {{ $ensayo->pivot->unidad_medida ?? '-' }}
                        <br>


                        <div class="mt-3 flex gap-3">

                            <a href="{{ route('gestor.editEnsayo', $ensayo->pivot->id) }}"
                                class="px-3 py-1 bg-blue-600 text-white rounded">
                                Editar
                            </a>

                            <form action="{{ route('gestor.accion', $ensayo->pivot->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="accion" value="aceptado">
                                <button class="px-3 py-1 bg-green-600 text-white rounded">Aceptar</button>
                            </form>

                            <form action="{{ route('gestor.accion', $ensayo->pivot->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="accion" value="rechazado">
                                <button class="px-3 py-1 bg-red-600 text-white rounded">Rechazar</button>
                            </form>

                        </div>

                    </div>
                @endforeach

            </div>
        @empty
            <div
                class="bg-white border border-gray-200 rounded-2xl shadow-md p-10 text-center text-gray-500 hover:shadow-lg transition">
                <x-heroicon-o-information-circle class="w-10 h-10 mx-auto mb-2 text-gray-400" />
                <p class="text-lg font-medium">No hay muestras finalizadas disponibles.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>