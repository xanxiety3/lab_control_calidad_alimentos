<x-app-layout>
    <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{-- Encabezado --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-document-text class="w-8 h-8 text-primary" />
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Resultados de Análisis</h1>
                        <p class="text-gray-600">Solicitud {{ $solicitud->codigo }}</p>
                    </div>
                </div>
                <a href="{{ route('resultados.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Volver al listado
                </a>
            </div>
                        <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-primary">Resultados de la solicitud
                    #{{ $solicitud->numero_solicitud }}</h1>

                <div class="flex space-x-3">
                    {{-- Botón Excel --}}
                    <a href="{{ route('resultados.exportar.simple', $solicitud->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow hover:bg-green-700 transition">
                        <x-heroicon-o-document-arrow-down class="w-5 h-5 mr-2" />
                        Exportar Excel
                    </a>

                    {{-- Botón Enviar correo --}}
                    <button id="btnEnviarCorreo"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow hover:bg-blue-700 transition">
                        <x-heroicon-o-envelope class="w-5 h-5 mr-2" />
                        Enviar a Gestor Técnico
                    </button>
                </div>
            </div>

            {{-- Modal Enviar Correo --}}
            <div id="modalCorreo"
                class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Enviar reporte al Gestor Técnico</h2>

                    <form id="formEnviarCorreo" method="POST" action="{{ route('reportes.enviar', $solicitud->id) }}">
                        @csrf
                        <p class="text-gray-600 mb-2">Selecciona a quién deseas enviar el archivo:</p>

                        <div class="max-h-60 overflow-y-auto border rounded-md p-3 mb-4 space-y-2">
                            @foreach ($gestores as $gestor)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="usuarios[]" value="{{ $gestor->id }}"
                                        class="text-primary focus:ring-primary">
                                    <span>{{ $gestor->name }} ({{ $gestor->email }})</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" id="btnCancelarCorreo"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.getElementById('modalCorreo');
                    const openBtn = document.getElementById('btnEnviarCorreo');
                    const closeBtn = document.getElementById('btnCancelarCorreo');

                    openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
                    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
                });
            </script>


            {{-- Información de la solicitud --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Cliente</h3>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $solicitud->cliente->persona->nombre_completo }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Fecha de solicitud</h3>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Estado</h3>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <x-heroicon-o-check-badge class="w-4 h-4" />
                            Finalizada
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resultados por muestra --}}
        <div class="space-y-6">
            @foreach($solicitud->muestras as $muestra)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    {{-- Encabezado de la muestra --}}
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <x-heroicon-o-cube class="w-5 h-5 text-primary" />
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ strtoupper($muestra->tipo) }} – {{ $muestra->tipoMuestra->nombre }}
                                    </h3>
                                    <p class="text-sm text-gray-600">Código: {{ $muestra->codigo_cliente }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $muestra->muestraEnsayos->count() }} ensayos
                            </span>
                        </div>
                    </div>

                    {{-- Resultados de ensayos --}}
                    <div class="p-6">
                        <div class="space-y-6">
                            @foreach($muestra->muestraEnsayos as $muestraEnsayo)
                                @php
                                    $ensayo = $muestraEnsayo->ensayo;
                                @endphp

                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                            <x-heroicon-o-beaker class="w-5 h-5 text-primary" />
                                            {{ $ensayo->nombre }}
                                        </h4>
                                        @if($muestraEnsayo->resultado)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Completado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                Pendiente
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Resultado principal --}}
                                    @if($muestraEnsayo->resultado)
                                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-500">Resultado</p>
                                                    <p class="text-2xl font-bold text-primary">
                                                        {{ $muestraEnsayo->resultado }} 
                                                        <span class="text-lg font-normal text-gray-600">{{ $muestraEnsayo->unidad_medida }}</span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-500">Fecha de análisis</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ \Carbon\Carbon::parse($muestraEnsayo->fecha_analisis)->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Detalles específicos por tipo de ensayo --}}
                                    <div class="space-y-4">
                                        {{-- 🧫 MICROBIOLOGÍA --}}
                                        @if($muestraEnsayo->microbiologia && in_array($ensayo->nombre, [
                                            'Mohos y levaduras', 'Coliformes totales', 'E. coli', 
                                            'Staphylococcu coagulasa (+)', 'Aerobios mesófilos'
                                        ]))
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">Dilución 1</p>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <span class="text-gray-500">C1:</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->microbiologia->dilucion1_c1 ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">C2:</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->microbiologia->dilucion1_c2 ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">Dilución 2</p>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <span class="text-gray-500">C1:</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->microbiologia->dilucion2_c1 ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">C2:</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->microbiologia->dilucion2_c2 ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 🧈 GRASA --}}
                                        @if($muestraEnsayo->fisicoquimico && $ensayo->nombre === 'Determinación de grasa')
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">Réplica 1</p>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <span class="text-gray-500">B (sup):</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica1_b ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">A (inf):</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica1_a ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">Réplica 2</p>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <span class="text-gray-500">B (sup):</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica2_b ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">A (inf):</span>
                                                            <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica2_a ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- 💧 SÓLIDOS TOTALES / HUMEDAD --}}
                                        @if($muestraEnsayo->fisicoquimico && in_array($ensayo->nombre, ['Determinación de solidos totales', 'Determinación de humedad']))
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-700 mb-2">Réplica 1</p>
                                                <div class="grid grid-cols-3 gap-4">
                                                    <div>
                                                        <span class="text-gray-500">m0:</span>
                                                        <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica1_m0 ?? '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">m1:</span>
                                                        <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica1_m1 ?? '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">m2:</span>
                                                        <span class="font-medium">{{ $muestraEnsayo->fisicoquimico->replica1_m2 ?? '—' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- ⚖️ DENSIDAD --}}
                                        @if($muestraEnsayo->fisicoquimico && $ensayo->nombre === 'Determinación de densidad')
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-700 mb-2">Observaciones</p>
                                                <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                    {{ $muestraEnsayo->fisicoquimico->densidad ?? 'Sin observaciones' }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Observaciones generales --}}
                                        @if($muestraEnsayo->observaciones)
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-700 mb-2">Observaciones adicionales</p>
                                                <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                    {{ $muestraEnsayo->observaciones }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Mensaje cuando no hay resultados --}}
        @if($solicitud->muestras->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <x-heroicon-o-exclamation-triangle class="w-12 h-12 text-yellow-600 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-yellow-900 mb-2">No hay muestras registradas</h3>
                <p class="text-yellow-700">Esta solicitud no tiene muestras asociadas para mostrar resultados.</p>
            </div>
        @endif
    </div>
</x-app-layout>