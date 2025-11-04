<x-app-layout>
    <div class="p-8 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">
        {{-- 🔹 Encabezado --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-check class="w-8 h-8 text-secondary" />
                    Revisión de la solicitud #{{ $solicitud->numero_solicitud }}
                </h1>
                <p class="text-gray-600 text-sm mt-1">
                    Cliente:
                    <span class="font-semibold text-gray-800">
                        {{ $solicitud->cliente->persona->nombre_completo ?? 'Sin nombre' }}
                    </span>
                    • Fecha: {{ $solicitud->created_at->format('Y-m-d') }}
                </p>
            </div>
            <a href="{{ route('dashboard.gestor') }}"
               class="text-sm text-gray-600 hover:text-gray-800 transition flex items-center gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Volver
            </a>
        </div>

        {{-- 🔹 Muestras --}}
        @foreach ($solicitud->muestras as $muestra)
            <form action="{{ route('gestor.actualizarMuestra', $muestra->id) }}" method="POST" class="mb-8">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="bg-primary text-white px-6 py-3 flex justify-between items-center">
                        <h2 class="font-semibold text-lg">
                            {{ $muestra->codigo_interno }} — {{ $muestra->tipoMuestra->nombre }}
                        </h2>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">
                            {{ $muestra->muestraEnsayos->count() }} ensayos
                        </span>
                    </div>

                    <div class="p-6 space-y-6">
                        @foreach ($muestra->muestraEnsayos as $ensayo)
                            @php
                                $fisico = $ensayo->fisicoquimico;
                                $micro = $ensayo->microbiologia;
                            @endphp

                            <div class="border border-gray-200 rounded-xl p-5 bg-gray-50 hover:bg-gray-100 transition">
                                <h3 class="text-primary font-semibold text-lg mb-3 flex items-center gap-2">
                                    <x-heroicon-o-beaker class="w-5 h-5 text-secondary" />
                                    {{ ucfirst($ensayo->ensayo->nombre) }}
                                </h3>

                                {{-- 🧫 MICROBIOLOGÍA --}}
                                @if ($micro)
                                    <div class="text-sm text-gray-700 leading-relaxed space-y-2">
                                        <input type="hidden" name="micro[{{ $micro->id }}][id]" value="{{ $micro->id }}">
                                        <p><strong>Dilución 1:</strong></p>
                                        <div class="flex gap-3">
                                            <input type="number" step="0.01" name="micro[{{ $micro->id }}][dilucion1_c1]"
                                                value="{{ $micro->dilucion1_c1 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                            <input type="number" step="0.01" name="micro[{{ $micro->id }}][dilucion1_c2]"
                                                value="{{ $micro->dilucion1_c2 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                        </div>

                                        <p><strong>Dilución 2:</strong></p>
                                        <div class="flex gap-3">
                                            <input type="number" step="0.01" name="micro[{{ $micro->id }}][dilucion2_c1]"
                                                value="{{ $micro->dilucion2_c1 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                            <input type="number" step="0.01" name="micro[{{ $micro->id }}][dilucion2_c2]"
                                                value="{{ $micro->dilucion2_c2 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                        </div>

                                        <div class="mt-3">
                                            <strong>Resultado:</strong>
                                            <input type="number" step="0.01" name="micro[{{ $micro->id }}][resultado]"
                                                value="{{ $micro->resultado }}" class="w-28 border-gray-300 rounded-lg p-2 ml-2">
                                            <span>{{ $micro->unidad ?? 'UFC/mL' }}</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- 🧈 GRASA --}}
                                @if ($fisico && $fisico->tipo === 'grasa')
                                    <div class="text-sm text-gray-700 leading-relaxed space-y-2 mt-2">
                                        <input type="hidden" name="fisico[{{ $fisico->id }}][id]" value="{{ $fisico->id }}">
                                        <p><strong>Réplica 1:</strong></p>
                                        <div class="flex gap-3">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica1_b]"
                                                value="{{ $fisico->replica1_b }}" class="w-24 border-gray-300 rounded-lg p-2">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica1_a]"
                                                value="{{ $fisico->replica1_a }}" class="w-24 border-gray-300 rounded-lg p-2">
                                        </div>

                                        <p><strong>Réplica 2:</strong></p>
                                        <div class="flex gap-3">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica2_b]"
                                                value="{{ $fisico->replica2_b }}" class="w-24 border-gray-300 rounded-lg p-2">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica2_a]"
                                                value="{{ $fisico->replica2_a }}" class="w-24 border-gray-300 rounded-lg p-2">
                                        </div>

                                        <div class="mt-3">
                                            <strong>Resultado:</strong>
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][resultado_grasa]"
                                                value="{{ $fisico->resultado_grasa }}" class="w-28 border-gray-300 rounded-lg p-2 ml-2">
                                            {{ str_contains($muestra->tipoMuestra->nombre, 'Leche') ? 'ml/100ml' : 'g/100g' }}
                                        </div>
                                    </div>
                                @endif

                                {{-- 💧 SÓLIDOS TOTALES / HUMEDAD --}}
                                @if ($fisico && in_array($fisico->tipo, ['solidos_totales', 'humedad']))
                                    <div class="text-sm text-gray-700 leading-relaxed mt-2">
                                        <p><strong>Réplica 1:</strong></p>
                                        <div class="flex gap-3">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica1_m0]"
                                                value="{{ $fisico->replica1_m0 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica1_m1]"
                                                value="{{ $fisico->replica1_m1 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][replica1_m2]"
                                                value="{{ $fisico->replica1_m2 }}" class="w-24 border-gray-300 rounded-lg p-2">
                                        </div>
                                        <div class="mt-3">
                                            <strong>Resultado:</strong>
                                            <input type="number" step="0.01" name="fisico[{{ $fisico->id }}][resultado_porcentaje]"
                                                value="{{ $fisico->resultado_porcentaje }}" class="w-28 border-gray-300 rounded-lg p-2 ml-2"> %
                                        </div>
                                    </div>
                                @endif

                                {{-- ⚖️ DENSIDAD --}}
                                @if ($fisico && $fisico->tipo === 'densidad')
                                    <div class="text-sm text-gray-700 leading-relaxed mt-2">
                                        <strong>Densidad:</strong>
                                        <input type="text" name="fisico[{{ $fisico->id }}][densidad]"
                                            value="{{ $fisico->densidad }}" class="w-28 border-gray-300 rounded-lg p-2 ml-2">
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- ✅ Botón individual --}}
                        <div class="flex justify-end pt-4">
                            <button
                                class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                                Aceptar resultados
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</x-app-layout>
