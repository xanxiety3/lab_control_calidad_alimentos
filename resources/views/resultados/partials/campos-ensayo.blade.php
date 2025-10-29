@php
    $nombre = strtolower($ensayo->nombre);
@endphp

<div class="border border-gray-100 rounded-lg p-4 mb-4 bg-white">
    <h4 class="text-md font-semibold text-gray-700 mb-3">{{ $ensayo->nombre }}</h4>

    {{-- MICROBIOLOGÍA --}}
    @if (in_array($nombre, ['mohos', 'levadura', 'coliformes', 'e.coli', 'stafilococus', 'mesofilos']))
        @include('resultados.partials.microbiologia', compact('muestra', 'ensayo'))
    
    {{-- FÍSICO-QUÍMICOS --}}
    @elseif ($nombre === 'grasa')
        @include('resultados.partials.grasa', compact('muestra', 'ensayo'))
    @elseif (in_array($nombre, ['solidos totales', 'humedad']))
        @include('resultados.partials.solidos', compact('muestra', 'ensayo'))

    {{-- DENSIDAD (manual) --}}
    @elseif ($nombre === 'densidad')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Densidad</label>
            <input type="text" name="ensayos[{{ $muestra->id }}][{{ $ensayo->id }}][resultado]"
                class="w-40 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm text-center"
                placeholder="Ej: 1.030">
            <span class="ml-2 text-gray-600 text-sm">g/ml</span>
        </div>
    @endif
</div>
