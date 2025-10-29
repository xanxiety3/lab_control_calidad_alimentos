<div class="space-y-6" id="formFisicoquimico">

    @if ($tipo === 'grasa')
        <h2 class="text-lg font-semibold text-blue-700">Ensayo de Grasa</h2>

        <div class="grid grid-cols-2 gap-6">
            <div class="border rounded-xl p-4 bg-gray-50">
                <h3 class="font-medium text-gray-800 mb-2">Réplica 1</h3>
                <x-input label="Menisco Superior (B)" id="r1_b" name="replica1_b" type="number" step="any" />
                <x-input label="Menisco Inferior (A)" id="r1_a" name="replica1_a" type="number" step="any" />
            </div>
            <div class="border rounded-xl p-4 bg-gray-50">
                <h3 class="font-medium text-gray-800 mb-2">Réplica 2</h3>
                <x-input label="Menisco Superior (B)" id="r2_b" name="replica2_b" type="number" step="any" />
                <x-input label="Menisco Inferior (A)" id="r2_a" name="replica2_a" type="number" step="any" />
            </div>
        </div>

        <div class="mt-4 text-right">
            <button type="button" id="btnCalcularGrasa" class="btn-azul">Calcular</button>
        </div>

        <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
            <p class="font-semibold text-blue-800 text-sm">Resultado (g/100g o ml/100ml):</p>
            <p id="resultadoGrasa" class="text-lg font-bold text-blue-900 mt-1">—</p>
            <input type="hidden" name="resultado" id="resultado" />
        </div>

    @elseif(in_array($tipo, ['solidos_totales', 'humedad']))
        <h2 class="text-lg font-semibold text-blue-700">Ensayo de {{ ucfirst(str_replace('_', ' ', $tipo)) }}</h2>

        <div class="border rounded-xl p-4 bg-gray-50 grid grid-cols-3 gap-4">
            <x-input label="m0" id="m0" name="replica1_m0" type="number" step="any" />
            <x-input label="m1" id="m1" name="replica1_m1" type="number" step="any" />
            <x-input label="m2" id="m2" name="replica1_m2" type="number" step="any" />
        </div>

        <div class="mt-4 text-right">
            <button type="button" id="btnCalcularPorcentaje" class="btn-azul">Calcular</button>
        </div>

        <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
            <p class="font-semibold text-blue-800 text-sm">Resultado (%):</p>
            <p id="resultadoPorcentaje" class="text-lg font-bold text-blue-900 mt-1">—</p>
            <input type="hidden" name="resultado" id="resultado" />
        </div>
    @endif
</div>
