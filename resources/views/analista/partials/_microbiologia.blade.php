<div class="space-y-6" id="formMicrobiologia">

    <h2 class="text-lg font-semibold text-blue-700">Ensayo de Microbiología</h2>

    <div class="grid grid-cols-2 gap-6">
        {{-- Dilución 1 --}}
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <h3 class="font-semibold text-gray-800 mb-2">Dilución 1</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600">C1</label>
                    <input type="number" step="any" name="dilucion1_c1" id="dilucion1_c1" class="input-analista" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">C2</label>
                    <input type="number" step="any" name="dilucion1_c2" id="dilucion1_c2" class="input-analista" />
                </div>
            </div>
        </div>

        {{-- Dilución 2 --}}
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <h3 class="font-semibold text-gray-800 mb-2">Dilución 2</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600">C1</label>
                    <input type="number" step="any" name="dilucion2_c1" id="dilucion2_c1" class="input-analista" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">C2</label>
                    <input type="number" step="any" name="dilucion2_c2" id="dilucion2_c2" class="input-analista" />
                </div>
            </div>
        </div>
    </div>

    {{-- Botón calcular --}}
    <div class="mt-4 text-right">
        <button type="button" id="btnCalcularMicro" class="btn-azul">Calcular</button>
    </div>

    {{-- Resultado --}}
    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
        <p class="font-semibold text-blue-800 text-sm">Resultado (calculado):</p>
        <p id="resultadoMicro" class="text-lg font-bold text-blue-900 mt-1">—</p>
        <input type="hidden" name="resultado" id="resultado" />
    </div>
</div>
