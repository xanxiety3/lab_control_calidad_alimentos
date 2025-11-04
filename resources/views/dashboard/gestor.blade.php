<x-app-layout>
    <div class="p-8 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-3">
            <div>
                <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
                    Panel del Gestor Técnico
                </h1>
                <p class="text-gray-600 text-sm mt-1">
                    Revisión y validación de resultados finales de laboratorio.
                </p>
            </div>

            <div class="bg-white px-4 py-2 rounded-xl shadow flex items-center gap-2 border border-gray-200">
                <x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />
                <span class="text-sm text-gray-700">
                    {{ $solicitudes->count() }} solicitudes listas para revisión
                </span>
            </div>
        </div>

        @if ($solicitudes->isEmpty())
            <div class="bg-white border border-gray-200 rounded-2xl shadow-md p-10 text-center text-gray-500">
            
                <p class="text-lg font-medium">No hay solicitudes finalizadas para revisión.</p>
                <p class="text-sm text-gray-400 mt-1">
                    Las solicitudes aparecerán aquí una vez el analista complete todos los ensayos.
                </p>
            </div>
        @else
            <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($solicitudes as $solicitud)
                    <div
                        class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                        <div class="bg-primary text-white px-5 py-3 flex justify-between items-center">
                            <h2 class="text-lg font-semibold tracking-wide">
                                Solicitud #{{ $solicitud->numero_solicitud }}
                            </h2>
                            <x-heroicon-o-document-text class="w-6 h-6 text-white/80" />
                        </div>

                        <div class="p-5 space-y-3">
                            <p class="text-gray-700">
                                <span class="font-semibold text-gray-900">Cliente:</span>
                                {{ $solicitud->cliente->persona->nombre_completo ?? 'Sin nombre' }}
                            </p>

                            <p class="text-gray-500 text-sm">
                                Fecha de solicitud:
                                <span class="font-medium text-gray-800">
                                    {{ $solicitud->created_at->format('Y-m-d') }}
                                </span>
                            </p>

                            <div class="flex justify-between items-center mt-4">
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1.5 rounded-full shadow-sm">
                                    {{ $solicitud->muestras->count() }} muestras finalizadas
                                </span>

                                <a href="{{ route('gestor.revisar', $solicitud->id) }}"
                                    class="inline-flex items-center gap-1 text-secondary hover:text-primary text-sm font-semibold transition">
                                    Revisar resultados
                                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                                </a>
                            </div>
                        </div>

                        {{-- Muestras asociadas --}}
                        <div class="bg-gray-50 px-5 py-4 border-t border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">Muestras:</h3>
                            <div class="space-y-1">
                                @foreach ($solicitud->muestras as $muestra)
                                    <div
                                        class="flex justify-between items-center text-sm text-gray-700 bg-white rounded-md px-3 py-2 border border-gray-100 hover:bg-gray-100 transition">
                                        <span>
                                            <span class="font-medium text-gray-800">
                                                {{ $muestra->codigo_interno }}
                                            </span>
                                            <span class="text-gray-500">({{ $muestra->tipoMuestra->nombre }})</span>
                                        </span>
                                        <a href="{{ route('gestor.ver', $muestra->id) }}"
                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium transition">
                                            Ver →
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
