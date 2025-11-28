<x-app-layout>
    <div class="max-w-3xl mx-auto p-8 bg-gray-100 min-h-screen rounded-xl shadow-inner">

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

                {{-- Nombre del ensayo --}}
                <div>
                    <p class="text-gray-700 text-sm mb-1 font-semibold">Tipo de ensayo:</p>
                    <p class="text-lg text-primary font-bold">
                        {{ $ensayo->ensayo->nombre }}
                    </p>
                </div>

                {{-- RESULTADO --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Resultado</label>
                    <input type="text"
                           name="resultado"
                           value="{{ old('resultado', $ensayo->resultado) }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                </div>

                {{-- OBSERVACIONES --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="observaciones"
                              class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2"
                              rows="4">{{ old('observaciones', $ensayo->observaciones) }}</textarea>
                </div>

                {{-- UNIDAD DE MEDIDA --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Unidad de medida</label>
                    <input type="text"
                           name="unidad_medida"
                           value="{{ old('unidad_medida', $ensayo->unidad_medida) }}"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-secondary focus:border-secondary px-3 py-2">
                </div>

            

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
