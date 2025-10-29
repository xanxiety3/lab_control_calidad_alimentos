<div class="grid sm:grid-cols-2 gap-3">
    <div>
        <label class="font-semibold text-sm text-gray-700">Dilución 1 - C1</label>
        <input type="number" step="0.01" class="w-full border-gray-300 rounded-lg" name="micro_c1_1">
    </div>
    <div>
        <label class="font-semibold text-sm text-gray-700">Dilución 1 - C2</label>
        <input type="number" step="0.01" class="w-full border-gray-300 rounded-lg" name="micro_c2_1">
    </div>
    <div>
        <label class="font-semibold text-sm text-gray-700">Dilución 2 - C1</label>
        <input type="number" step="0.01" class="w-full border-gray-300 rounded-lg" name="micro_c1_2">
    </div>
    <div>
        <label class="font-semibold text-sm text-gray-700">Dilución 2 - C2</label>
        <input type="number" step="0.01" class="w-full border-gray-300 rounded-lg" name="micro_c2_2">
    </div>
</div>

<button type="button" onclick="calcularMicro('{{ $muestra->id }}','{{ $ensayo->id }}')"
    class="mt-3 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm shadow-sm">
    Calcular
</button>

<div class="mt-3">
    <label class="font-semibold text-sm text-gray-700">Resultado (UFC/{{ strtolower($muestra->tipoMuestra->nombre) === 'queso' ? 'g' : 'ml' }})</label>
    <input type="text" id="micro_resultado_{{ $muestra->id }}_{{ $ensayo->id }}" readonly
        class="w-40 border-gray-300 rounded-lg bg-gray-100 text-center font-semibold">
</div>
