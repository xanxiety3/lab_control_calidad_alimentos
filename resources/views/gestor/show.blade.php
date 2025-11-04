<x-app-layout>
    <div class="p-6 bg-gray-50 min-h-screen">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-primary">
                Visualización de resultados – {{ $muestra->tipoMuestra->nombre }}
            </h1>
            <a href="{{ route('dashboard.gestor') }}"
               class="text-sm text-gray-600 hover:text-gray-800 transition">← Volver</a>
        </div>

        <div class="bg-white shadow rounded-xl p-6 border border-gray-200">
            <h2 class="font-semibold text-lg text-primary mb-4">
                Muestra: {{ $muestra->codigo_interno }}
            </h2>

            @foreach ($muestra->muestraEnsayos as $ensayo)
                <div class="mb-4 p-4 border rounded-lg bg-gray-50">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        {{ $ensayo->ensayo->nombre }}
                    </h3>

                    @if ($ensayo->ensayoFisicoquimico)
                        <p class="text-sm text-gray-700">
                            <strong>Tipo:</strong> {{ $ensayo->ensayoFisicoquimico->tipo }} <br>
                            <strong>Resultado:</strong> {{ $ensayo->ensayoFisicoquimico->resultado_grasa ?? $ensayo->ensayoFisicoquimico->resultado_porcentaje ?? 'N/A' }}
                            {{ $ensayo->ensayoFisicoquimico->unidad_grasa ?? '%' }}
                        </p>
                    @endif

                    @if ($ensayo->ensayoMicrobiologia)
                        <p class="text-sm text-gray-700 mt-2">
                            <strong>Resultado:</strong> {{ $ensayo->ensayoMicrobiologia->resultado ?? 'N/A' }}
                            {{ $ensayo->ensayoMicrobiologia->unidad }} <br>
                            <strong>Dilución 1:</strong> C1={{ $ensayo->ensayoMicrobiologia->dilucion1_c1 }},
                            C2={{ $ensayo->ensayoMicrobiologia->dilucion1_c2 }} <br>
                            <strong>Dilución 2:</strong> C1={{ $ensayo->ensayoMicrobiologia->dilucion2_c1 }},
                            C2={{ $ensayo->ensayoMicrobiologia->dilucion2_c2 }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
