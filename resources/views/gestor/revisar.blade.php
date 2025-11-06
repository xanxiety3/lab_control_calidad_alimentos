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
                            <span
                                class="font-semibold px-2 py-1 rounded-full text-xs shadow-sm
                                @if($muestra->estado === 'finalizada') bg-green-100 text-green-700
                                @elseif($muestra->estado === 'en proceso') bg-yellow-100 text-yellow-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($muestra->estado ?? 'Pendiente') }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Ensayos asociados --}}
                @foreach ($muestra->muestraEnsayos as $ensayo)
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                                <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-indigo-500" />
                                Ensayo: <span class="text-gray-900">{{ $ensayo->ensayo->nombre ?? 'Desconocido' }}</span>
                            </h3>

                            <span
                                class="px-3 py-1 text-sm font-medium rounded-full shadow-sm
                                @if($ensayo->estado === 'aceptado') bg-green-100 text-green-700
                                @elseif($ensayo->estado === 'rechazado') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($ensayo->estado ?? 'Pendiente') }}
                            </span>
                        </div>

                        {{-- Contenido técnico del ensayo --}}
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-inner space-y-2">
                            @if ($ensayo->fisicoquimico)
                                <div class="text-sm text-gray-700 space-y-1">
                                    <p><strong>Tipo:</strong> {{ $ensayo->fisicoquimico->tipo }}</p>
                                    <p><strong>Réplicas:</strong>
                                        R1 (A={{ $ensayo->fisicoquimico->replica1_a }},
                                        B={{ $ensayo->fisicoquimico->replica1_b }}) /
                                        R2 (A={{ $ensayo->fisicoquimico->replica2_a }},
                                        B={{ $ensayo->fisicoquimico->replica2_b }})
                                    </p>

                                    @if ($ensayo->fisicoquimico->tipo == 'grasa')
                                        <p><strong>Resultado grasa:</strong>
                                            {{ $ensayo->fisicoquimico->resultado_grasa }}
                                            {{ $ensayo->fisicoquimico->unidad_grasa }}
                                        </p>
                                    @elseif(in_array($ensayo->fisicoquimico->tipo, ['solidos_totales', 'humedad']))
                                        <p><strong>Resultado %:</strong>
                                            {{ $ensayo->fisicoquimico->resultado_porcentaje }}
                                        </p>
                                    @endif
                                </div>
                            @elseif($ensayo->microbiologia)
                                <div class="text-sm text-gray-700 space-y-1">
                                    <p><strong>Dilución 1:</strong>
                                        C1={{ $ensayo->microbiologia->dilucion1_c1 }},
                                        C2={{ $ensayo->microbiologia->dilucion1_c2 }}
                                    </p>
                                    <p><strong>Dilución 2:</strong>
                                        C1={{ $ensayo->microbiologia->dilucion2_c1 }},
                                        C2={{ $ensayo->microbiologia->dilucion2_c2 }}
                                    </p>
                                    <p><strong>Resultado:</strong>
                                        {{ $ensayo->microbiologia->resultado }}
                                        {{ $ensayo->microbiologia->unidad }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <form action="{{ route('gestor.accion', $ensayo->id) }}" method="POST"
    class="flex flex-wrap gap-2 mt-4">
    @csrf

    {{-- Botón Aceptar --}}
    <button type="submit" name="accion" value="aceptado"
        class="flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-md shadow-sm 
        hover:bg-green-700 active:scale-[0.98] transition-all duration-200">
        <x-heroicon-o-check class="w-4 h-4" />
        Aceptar
    </button>

    {{-- Botón Rechazar --}}
    <button type="submit" name="accion" value="rechazado"
        class="flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-md shadow-sm 
        hover:bg-red-700 active:scale-[0.98] transition-all duration-200">
        <x-heroicon-o-x-mark class="w-4 h-4" />
        Rechazar
    </button>
</form>


                        {{-- Entrega de resultados --}}
                        @if ($muestra->entrega == 'personal')
                            <div class="mt-5 flex justify-end">
                                <button type="button"
                                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition-all">
                                    <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                    Descargar PDF
                                </button>
                            </div>
                        @elseif ($muestra->entrega == 'ambos')
                            <div class="mt-5 flex flex-wrap gap-3 justify-end">
                                <button type="button"
                                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition-all">
                                    <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                    Descargar PDF
                                </button>
                                <button type="button"
                                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition-all">
                                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                                    Enviar por correo
                                </button>
                            </div>
                        @endif
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
