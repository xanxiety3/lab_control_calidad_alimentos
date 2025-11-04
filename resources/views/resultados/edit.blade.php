<x-app-layout>
    <div class="p-6 max-w-6xl mx-auto bg-gray-100 min-h-screen rounded-xl shadow-inner">
        <h1 class="text-3xl font-bold text-primary mb-6 flex items-center gap-2">
            <x-heroicon-o-clipboard-document-check class="w-8 h-8 text-secondary" />
            Registrar resultados – Solicitud {{ $solicitud->codigo }}
        </h1>

        <div class="mb-6 bg-white rounded-xl shadow p-4 border border-gray-200">
            <p class="text-gray-700"><strong>Cliente:</strong>
                <span class="text-primary">{{ $solicitud->cliente->persona->nombre_completo }}</span>
            </p>
        </div>

        @if ($solicitud->muestras->count() > 0)
            <form id="form-resultados" action="{{ route('resultados.update', $solicitud->id) }}" method="POST"
                class="space-y-6">
                @csrf
                @method('PUT')

                @foreach ($solicitud->muestras as $muestra)
                    <div x-data="{ openMuestra{{ $muestra->id }}: true }"
                        class="border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden transition-all duration-300">

                        {{-- Encabezado de muestra --}}
                        <button type="button" @click="openMuestra{{ $muestra->id }} = !openMuestra{{ $muestra->id }}"
                            class="w-full flex justify-between items-center bg-primary text-white px-5 py-3 text-left font-semibold hover:bg-primary/90 transition-colors">
                            <span class="tracking-wide uppercase">
                                {{ $muestra->tipoMuestra->nombre }} -
                                {{ $muestra->codigo_cliente }}
                            </span>
                            <x-heroicon-o-chevron-down x-show="!openMuestra{{ $muestra->id }}" class="w-5 h-5" />
                            <x-heroicon-o-chevron-up x-show="openMuestra{{ $muestra->id }}" class="w-5 h-5" />
                        </button>

                        {{-- Contenido de muestra --}}
                        <div x-show="openMuestra{{ $muestra->id }}" x-transition class="p-5 space-y-4 bg-gray-50">
                            @foreach ($muestra->muestraEnsayos as $muestraEnsayo)
                                @php $ensayo = $muestraEnsayo->ensayo; @endphp

                                <input type="hidden" name="muestra_ensayo_id[]" value="{{ $muestraEnsayo->id }}">

                                <div x-data="{ openEnsayo{{ $ensayo->id }}: false }"
                                    class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden transition-all duration-200">

                                    {{-- Encabezado de ensayo --}}
                                    <button type="button"
                                        @click="openEnsayo{{ $ensayo->id }} = !openEnsayo{{ $ensayo->id }}"
                                        class="w-full flex justify-between items-center px-4 py-2 text-left font-semibold bg-gray-100 hover:bg-gray-200 transition-colors">
                                        <span class="flex items-center gap-2 text-gray-800">
                                            <x-heroicon-o-beaker class="w-5 h-5 text-secondary" />
                                            {{ $ensayo->nombre }}
                                        </span>
                                        <x-heroicon-o-chevron-down x-show="!openEnsayo{{ $ensayo->id }}"
                                            class="w-5 h-5 text-gray-600" />
                                        <x-heroicon-o-chevron-up x-show="openEnsayo{{ $ensayo->id }}"
                                            class="w-5 h-5 text-gray-600" />
                                    </button>

                                    {{-- Contenido del ensayo --}}
                                    <div x-show="openEnsayo{{ $ensayo->id }}" x-transition
                                        class="p-4 bg-gray-100 rounded-xl shadow-inner space-y-5 text-sm text-gray-700 border border-gray-200">

                                        {{-- 🧫 MICROBIOLOGÍA --}}
                                        @if (in_array($ensayo->nombre, [
                                                'Mohos y levaduras',
                                                'Coliformes totales',
                                                'E. coli',
                                                'Staphylococcu coagulasa (+)',
                                                'Aerobios mesófilos',
                                            ]))
                                            <div class="space-y-4 text-center">
                                                <h4 class="font-semibold text-gray-800 text-base">Dilución 1</h4>
                                                <div class="flex justify-center">
                                                    <div class="flex gap-3 flex-wrap justify-center">
                                                        <input type="number" name="c1_{{ $muestraEnsayo->id }}"
                                                            id="c1_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-40"
                                                            placeholder="C1">
                                                        <input type="number" name="c2_{{ $muestraEnsayo->id }}"
                                                            id="c2_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-40"
                                                            placeholder="C2">
                                                    </div>
                                                </div>

                                                <h4 class="font-semibold text-gray-800 text-base mt-3">Dilución 2</h4>
                                                <div class="flex justify-center">
                                                    <div class="flex gap-3 flex-wrap justify-center">
                                                        <input type="number" name="c3_{{ $muestraEnsayo->id }}"
                                                            id="c3_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-40"
                                                            placeholder="C1">
                                                        <input type="number" name="c4_{{ $muestraEnsayo->id }}"
                                                            id="c4_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-40"
                                                            placeholder="C2">
                                                    </div>
                                                </div>

                                                <button type="button"
                                                    onclick="calcularMicro('{{ $muestraEnsayo->id }}', '{{ $ensayo->unidad_medida }}')"
                                                    class="mt-3 bg-secondary hover:bg-secondary/90 text-white px-4 py-1.5 rounded-lg text-sm transition shadow">
                                                    Calcular
                                                </button>
                                                <p class="mt-1 text-gray-800 font-medium">
                                                    Resultado:
                                                    <span id="resultado_{{ $muestraEnsayo->id }}"
                                                        class="text-primary font-semibold">—</span>
                                                </p>
                                            </div>
                                        @endif

                                        {{-- 🧈 GRASA --}}
                                        @if ($ensayo->nombre === 'Determinación de grasa')
                                            <div class="space-y-4 text-center">
                                                <h4 class="font-semibold text-gray-800 text-base">Réplica 1</h4>
                                                <div class="flex justify-center">
                                                    <div class="flex gap-3 flex-wrap justify-center">
                                                        <input type="number" name="b1_{{ $muestraEnsayo->id }}"
                                                            id="b1_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-44"
                                                            placeholder="Menisco superior (B)">
                                                        <input type="number" name="a1_{{ $muestraEnsayo->id }}"
                                                            id="a1_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-44"
                                                            placeholder="Menisco inferior (A)">
                                                    </div>
                                                </div>

                                                <h4 class="font-semibold text-gray-800 text-base mt-3">Réplica 2</h4>
                                                <div class="flex justify-center">
                                                    <div class="flex gap-3 flex-wrap justify-center">
                                                        <input type="number" name="b2_{{ $muestraEnsayo->id }}"
                                                            id="b2_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-44"
                                                            placeholder="Menisco superior (B)">
                                                        <input type="number" name="a2_{{ $muestraEnsayo->id }}"
                                                            id="a2_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-44"
                                                            placeholder="Menisco inferior (A)">
                                                    </div>
                                                </div>

                                                <button type="button"
                                                    onclick="calcularGrasa('{{ $muestraEnsayo->id }}', '{{ $ensayo->unidad_medida }}')"
                                                    class="mt-3 bg-secondary hover:bg-secondary/90 text-white px-4 py-1.5 rounded-lg text-sm transition shadow">
                                                    Calcular
                                                </button>
                                                <p class="mt-1 text-gray-800 font-medium">
                                                    Resultado:
                                                    <span id="resultado_grasa_{{ $muestraEnsayo->id }}"
                                                        class="text-primary font-semibold">—</span>
                                                </p>
                                            </div>
                                        @endif

                                        {{-- ⚖️ DENSIDAD --}}
                                        @if ($ensayo->nombre === 'Determinación de densidad')
                                            <div class="text-center">
                                                <textarea name="densidad_{{ $muestraEnsayo->id }}" rows="2"
                                                    class="w-2/3 border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary text-sm p-3 resize-none"
                                                    placeholder="Ingrese observaciones o cálculo de densidad..."></textarea>
                                            </div>
                                        @endif

                                        {{-- 💧 SÓLIDOS TOTALES / HUMEDAD --}}
                                        @if (in_array($ensayo->nombre, ['Determinación de solidos totales', 'Determinación de humedad']))
                                            <div class="space-y-4 text-center">
                                                <h4 class="font-semibold text-gray-800 text-base">Réplica 1</h4>
                                                <div class="flex justify-center">
                                                    <div class="flex gap-3 flex-wrap justify-center">
                                                        <input type="number" name="m0_{{ $muestraEnsayo->id }}"
                                                            id="m0_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-32"
                                                            placeholder="m0">
                                                        <input type="number" name="m1_{{ $muestraEnsayo->id }}"
                                                            id="m1_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-32"
                                                            placeholder="m1">
                                                        <input type="number" name="m2_{{ $muestraEnsayo->id }}"
                                                            id="m2_{{ $muestraEnsayo->id }}"
                                                            class="border-gray-300 bg-white rounded-xl shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2 w-32"
                                                            placeholder="m2">
                                                    </div>
                                                </div>

                                                <button type="button"
                                                    onclick="calcularSolidos('{{ $muestraEnsayo->id }}', '{{ $ensayo->unidad_medida }}')"
                                                    class="mt-3 bg-secondary hover:bg-secondary/90 text-white px-4 py-1.5 rounded-lg text-sm transition shadow">
                                                    Calcular
                                                </button>
                                                <p class="mt-1 text-gray-800 font-medium">
                                                    Resultado:
                                                    <span id="resultado_solidos_{{ $muestraEnsayo->id }}"
                                                        class="text-primary font-semibold">—</span>
                                                </p>
                                            </div>
                                        @endif
                                    </div>


                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('resultados.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md shadow-sm transition">
                        Cancelar
                    </a>
                    <button
                        class="bg-secondary hover:bg-secondary/90 text-white px-5 py-2 rounded-md shadow-sm font-semibold transition">
                        Guardar resultados
                    </button>
                </div>
            </form>
        @else
            <div class="bg-green-50 border border-green-400 text-green-800 px-5 py-4 rounded-lg shadow-sm">
                <p class="font-semibold">¡Todos los ensayos han sido completados!</p>
                <p class="mt-1 text-sm">No hay ensayos pendientes por registrar para esta solicitud.</p>
                <a href="{{ route('resultados.index') }}"
                    class="mt-3 inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition">
                    Volver al listado
                </a>
            </div>
        @endif
    </div>

    {{-- Scripts (no modificados) --}}
    <script>
        function calcularMicro(id, unidad) {
            const c1 = parseFloat(document.getElementById(`c1_${id}`).value) || 0;
            const c2 = parseFloat(document.getElementById(`c2_${id}`).value) || 0;
            const c3 = parseFloat(document.getElementById(`c3_${id}`).value) || 0;
            const c4 = parseFloat(document.getElementById(`c4_${id}`).value) || 0;
            const resultado = ((c1 + c2 + c3 + c4) / 4) * 1000;
            document.getElementById(`resultado_${id}`).textContent = `${resultado.toFixed(2)} ${unidad}`;
        }

        function calcularGrasa(id, unidad) {
            const b1 = parseFloat(document.getElementById(`b1_${id}`).value) || 0;
            const a1 = parseFloat(document.getElementById(`a1_${id}`).value) || 0;
            const b2 = parseFloat(document.getElementById(`b2_${id}`).value) || 0;
            const a2 = parseFloat(document.getElementById(`a2_${id}`).value) || 0;
            const r1 = b1 - a1;
            const r2 = b2 - a2;
            const promedio = (r1 + r2) / 2;
            document.getElementById(`resultado_grasa_${id}`).textContent = `${promedio.toFixed(2)} ${unidad}`;
        }

        function calcularSolidos(id, unidad) {
            const m0 = parseFloat(document.getElementById(`m0_${id}`).value);
            const m1 = parseFloat(document.getElementById(`m1_${id}`).value);
            const m2 = parseFloat(document.getElementById(`m2_${id}`).value);

            const resultadoSpan = document.getElementById(`resultado_solidos_${id}`);
            const hiddenInput = document.getElementById(`resultado_solidos_hidden_${id}`);

            // Validaciones básicas
            if (isNaN(m0) || isNaN(m1) || isNaN(m2)) {
                resultadoSpan.textContent = "⚠️ Datos incompletos";
                hiddenInput.value = "";
                return;
            }

            if (m1 === m0) {
                resultadoSpan.textContent = "⚠️ m1 y m0 no pueden ser iguales";
                hiddenInput.value = "";
                return;
            }

            const resultado = ((m2 - m0) / (m1 - m0)) * 100;

            // Validar que no sea negativo o infinito
            if (!isFinite(resultado) || resultado < 0) {
                resultadoSpan.textContent = "⚠️ Verifique los datos";
                hiddenInput.value = "";
                return;
            }

            // Mostrar y guardar resultado
            resultadoSpan.textContent = `${resultado.toFixed(2)} ${unidad}`;
            hiddenInput.value = resultado.toFixed(2);
        }
    </script>
</x-app-layout>
