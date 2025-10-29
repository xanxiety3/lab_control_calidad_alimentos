<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10">
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-8">

            {{-- 🔹 Encabezado --}}
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Registro de resultados de análisis
                </h1>
            </div>

            {{-- 🧾 Info de la muestra --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
                <p class="font-semibold text-gray-700">Código muestra: 
                    <span class="text-blue-600">{{ $muestraEnsayo->muestra->codigo_interno }}</span>
                </p>
                <p class="text-gray-600 text-sm">Ensayo: {{ $muestraEnsayo->ensayo->nombre }}</p>
            </div>

            {{-- 🧩 Formulario dinámico según tipo --}}
            <form method="POST" action="{{ route('analista.store', $muestraEnsayo->id) }}">
                @csrf

                @if ($tipo === 'microbiologia')
                    @include('analista.partials._microbiologia')
                @elseif (in_array($tipo, ['grasa', 'solidos_totales', 'humedad', 'densidad']))
                    @include('analista.partials._fisicoquimico', ['tipo' => $tipo])
                @endif

                <div class="mt-8 flex justify-end gap-3 border-t pt-4">
                    <a href="{{ route('resultados.index') }}" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 text-gray-700 font-medium">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Guardar resultado
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 🧮 Scripts para cálculos --}}
    <script src="{{ asset('js/calculosAnalista.js') }}"></script>
</x-app-layout>
