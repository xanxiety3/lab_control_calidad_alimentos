<x-app-layout>
    <div class="max-w-4xl mx-auto p-8 bg-gray-100 min-h-screen rounded-xl shadow-inner">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-primary flex items-center gap-2">
                <x-heroicon-o-beaker class="w-7 h-7 text-secondary" />
                Editar ensayo – {{ $ensayo->ensayo->nombre }}
            </h1>
            <a href="{{ route('gestor.index') }}"
                class="text-gray-600 hover:text-gray-900 transition text-sm font-medium flex items-center gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Volver
            </a>
        </div>

        {{-- Tarjeta principal --}}
        <div class="bg-white rounded-xl shadow p-6 border border-gray-200 space-y-6">

            <form action="{{ route('gestor.update', $ensayo->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Mostrar tipo de ensayo --}}
                <div>
                    <p class="text-gray-700 text-sm mb-1 font-semibold">Tipo de ensayo:</p>
                    <p class="text-lg text-primary font-bold">{{ $ensayo->ensayo->nombre }}</p>
                </div>

                {{-- CAMPOS SEGÚN EL TIPO DE ENSAYO --}}
                @php
                    $tipo = $ensayo->fisicoquimico->tipo ?? $ensayo->microbiologia->tipo ?? null;
                    $nombre = $ensayo->ensayo->nombre;
                @endphp

                {{-- 🧫 MICROBIOLOGÍA --}}
                @if (in_array($nombre, ['Mohos y levaduras', 'Coliformes totales', 'E. coli', 'Staphylococcu coagulasa (+)', 'Aerobios mesófilos',]))
                    <div class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">Campos de microbiología</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div> <label class="text-sm font-medium text-gray-700">Dilución 1 - C1</label> <input
                                    type="number" name="dilucion1_c1"
                                    value="{{ old('dilucion1_c1', $ensayo->microbiologia->dilucion1_c1 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                            <div> <label class="text-sm font-medium text-gray-700">Dilución 1 - C2</label> <input
                                    type="number" name="dilucion1_c2"
                                    value="{{ old('dilucion1_c2', $ensayo->microbiologia->dilucion1_c2 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                            <div> <label class="text-sm font-medium text-gray-700">Dilución 2 - C1</label> <input
                                    type="number" name="dilucion2_c1"
                                    value="{{ old('dilucion2_c1', $ensayo->microbiologia->dilucion2_c1 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                            <div> <label class="text-sm font-medium text-gray-700">Dilución 2 - C2</label> <input
                                    type="number" name="dilucion2_c2"
                                    value="{{ old('dilucion2_c2', $ensayo->microbiologia->dilucion2_c2 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div> <label class="text-sm font-medium text-gray-700">Resultado</label> <input type="text"
                                    name="resultado" value="{{ old('resultado', $ensayo->microbiologia->resultado ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                            <div> <label class="text-sm font-medium text-gray-700">Unidad</label> <input type="text"
                                    name="unidad"
                                    value="{{ old('unidad', $ensayo->microbiologia->unidad ?? $ensayo->ensayo->unidad_medida) }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                        </div>
                </div> @endif

                {{-- 🧈 GRASA --}}
                @if ($tipo === 'grasa')
                    <div class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">Campos de determinación de grasa</h2>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Replica 1A</label>
                                <input type="number" step="0.01" name="replica1_a"
                                    value="{{ old('replica1_a', $ensayo->fisicoquimico->replica1_a ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Replica 1B</label>
                                <input type="number" step="0.01" name="replica1_b"
                                    value="{{ old('replica1_b', $ensayo->fisicoquimico->replica1_b ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus-border-secondary px-3 py-2">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Replica 2A</label>
                                <input type="number" step="0.01" name="replica2_a"
                                    value="{{ old('replica2_a', $ensayo->fisicoquimico->replica2_a ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus-border-secondary px-3 py-2">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Replica 2B</label>
                                <input type="number" step="0.01" name="replica2_b"
                                    value="{{ old('replica2_b', $ensayo->fisicoquimico->replica2_b ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus-border-secondary px-3 py-2">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Resultado de grasa</label>
                                <input type="number" step="0.01" name="resultado_grasa"
                                    value="{{ old('resultado_grasa', $ensayo->fisicoquimico->resultado_grasa ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus-border-secondary px-3 py-2">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Unidad</label>
                                <input type="text" name="unidad_grasa"
                                    value="{{ old('unidad_grasa', $ensayo->fisicoquimico->unidad_grasa ?? $ensayo->ensayo->unidad_medida) }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus-border-secondary px-3 py-2">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 💧 HUMEDAD / SÓLIDOS TOTALES --}}
                @if (in_array($nombre, ['Determinación de solidos totales', 'Determinación de humedad']))
                    <div class="space-y-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">Campos de determinación de sólidos /
                            humedad</h2>
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div> <label class="text-sm font-medium text-gray-700">m0</label> <input type="number" name="m0"
                                    value="{{ old('m0', $ensayo->fisicoquimico->replica1_m0 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                            <div> <label class="text-sm font-medium text-gray-700">m1</label> <input type="number" name="m1"
                                    value="{{ old('m1', $ensayo->fisicoquimico->replica1_m1 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                            <div> <label class="text-sm font-medium text-gray-700">m2</label> <input type="number" name="m2"
                                    value="{{ old('m2', $ensayo->fisicoquimico->replica1_m2 ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                            </div>
                        </div>
                        <div> <label class="text-sm font-medium text-gray-700">Resultado (%)</label> <input type="number"
                                step="0.01" name="resultado_porcentaje"
                                value="{{ old('resultado_porcentaje', $ensayo->fisicoquimico->resultado_porcentaje ?? '') }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                        </div>
                </div> @endif

                {{-- ⚖️ DENSIDAD --}} @if ($nombre === 'Determinación de densidad')
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">Campo de densidad</h2> <textarea
                            name="resultado_porcentaje" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary text-sm p-3 resize-none"
                            placeholder="Ingrese observaciones o cálculo de densidad...">{{ old('resultado_porcentaje', $ensayo->fisicoquimico->resultado_porcentaje ?? '') }}</textarea>
                </div> @endif


                {{-- BOTONES --}}
                <div class="flex justify-end gap-3 pt-6 border-t">
                    <a href="{{ url()->previous() }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow-sm transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="bg-secondary hover:bg-secondary/90 text-white px-5 py-2 rounded-lg shadow font-semibold transition">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>