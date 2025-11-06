<x-app-layout>
    <div class="max-w-7xl mx-auto px-8 py-12 bg-gradient-to-b from-gray-100 to-gray-200 min-h-screen">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-primary flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-check class="w-8 h-8 text-primary" />
                    Panel del Gestor Técnico
                </h1>
                <p class="text-gray-700 text-sm mt-2">
                    Revisión, validación y entrega de resultados finales de laboratorio.
                </p>
            </div>

            <div
                class="bg-gray-50 px-6 py-3 rounded-xl shadow-md flex items-center gap-2 border border-gray-300 text-sm font-medium text-gray-700">
                <x-heroicon-o-check-badge class="w-5 h-5 text-green-600" />
                {{ $solicitudes->count() }} solicitudes listas para revisión
            </div>
        </div>

        {{-- Sin solicitudes --}}
        @if ($solicitudes->isEmpty())
            <div
                class="bg-gray-50 border border-gray-200 rounded-2xl shadow-lg p-10 text-center text-gray-600 hover:shadow-xl transition">
                <x-heroicon-o-information-circle class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                <p class="text-lg font-semibold">No hay solicitudes finalizadas para revisión.</p>
                <p class="text-sm text-gray-400 mt-1">
                    Las solicitudes aparecerán aquí una vez el analista complete todos los ensayos.
                </p>
            </div>
        @else
            {{-- Listado --}}
            <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($solicitudes as $solicitud)
                    @php
                        // Validar si todos los ensayos están aceptados
                        $todosAceptados = true;
                        foreach ($solicitud->muestras as $muestra) {
                            foreach ($muestra->MuestraEnsayos as $ensayo) {
                                if ($ensayo->estado !== 'aceptado') {
                                    $todosAceptados = false;
                                    break 2;
                                }
                            }
                        }
                    @endphp

                    <div
                        class="group bg-gray-50 rounded-2xl border border-gray-300 shadow-md hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">

                        {{-- Header de solicitud --}}
                        <div
                            class="bg-gradient-to-r from-primary to-secondary text-white px-5 py-3 flex justify-between items-center">
                            <h2 class="text-lg font-semibold tracking-wide">
                                Solicitud #{{ $solicitud->numero_solicitud }}
                            </h2>
                            <x-heroicon-o-document-text class="w-6 h-6 text-white/90" />
                        </div>

                        {{-- Contenido --}}
                        <div class="p-6 space-y-4">
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

                            {{-- Estado --}}
                            <div class="flex flex-wrap items-center gap-3 mt-2">
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1.5 rounded-full shadow-sm">
                                    {{ $solicitud->muestras->count() }} muestras finalizadas
                                </span>
                                <span
                                    class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1.5 rounded-full shadow-sm">
                                    Entrega: {{ ucfirst($solicitud->entrega_resultados) }}
                                </span>
                            </div>
                        </div>

                        {{-- Enlace de revisión --}}
                        <div class="px-6 pb-2">
                            <a href="{{ route('gestor.edit', $solicitud->id) }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:text-secondary transition">
                                <x-heroicon-o-eye class="w-4 h-4" />
                                Revisar resultados
                            </a>
                        </div>

                        {{-- Botones de entrega o aviso --}}
                        <div
                            class="bg-gray-100 px-6 py-4 border-t border-gray-300 flex flex-col sm:flex-row sm:justify-end gap-3 items-center">
                            @if ($todosAceptados)
                                <a href="{{ route('gestor.exportar', $solicitud->id) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg shadow-sm hover:bg-primary/90 transition-all duration-200">
                                    <x-heroicon-o-document-arrow-down class="w-5 h-5" />
                                    Descargar PDF
                                </a>

                                <a href="{{ route('gestor.enviarCorreo', $solicitud->id) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-secondary text-white text-sm font-medium rounded-lg shadow-sm hover:bg-secondary/90 transition-all duration-200">
                                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                                    Enviar por correo
                                </a>
                            @else
                                <div
                                    class="flex items-center gap-2 text-amber-700 bg-amber-100 border border-amber-300 rounded-lg px-4 py-2 text-sm font-medium">
                                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600" />
                                    Antes de enviar o descargar resultados finales, debe validarlos.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
