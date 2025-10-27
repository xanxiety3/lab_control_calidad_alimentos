<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10">
        {{-- 🧾 Contenedor principal --}}
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-8">

            {{-- 🔹 Encabezado --}}
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                    <x-heroicon-o-pencil-square class="w-8 h-8 text-blue-600" />
                    Registrar resultados – <span class="text-gray-600">Solicitud {{ $muestra->solicitud->numero_solicitud }}</span>
                </h1>
            </div>

            {{-- 🧍 Info general del cliente --}}
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-5 mb-8">
                <div class="grid sm:grid-cols-2 gap-4 text-sm text-gray-700">
                    <p>
                        <span class="font-semibold text-gray-900">Cliente:</span><br>
                        {{ $muestra->solicitud->cliente->persona->nombre_completo ?? $muestra->solicitud->cliente->razon_social }}
                    </p>
                    <p>
                        <span class="font-semibold text-gray-900">Fecha de solicitud:</span><br>
                        {{ \Carbon\Carbon::parse($muestra->solicitud->fecha_solicitud)->format('Y-m-d') }}
                    </p>
                </div>
            </div>

            {{-- 🧪 Formulario --}}
            <form method="POST" action="{{ route('resultados.update', $muestra->id) }}" class="space-y-8">
                @csrf
                @method('PUT')

                @foreach ($muestras as $m)
                    <div class="border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all bg-gray-50/50">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-blue-700 flex items-center gap-2">
                                <x-heroicon-o-beaker class="w-5 h-5 text-blue-700" />
                                Muestra {{ $m->codigo_interno }} – {{ $m->tipoMuestra->nombre }}
                            </h2>

                            <span class="text-xs bg-white border border-gray-200 rounded-full px-3 py-1 text-gray-600">
                                Código cliente: <strong>{{ $m->codigo_cliente }}</strong>
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                                    <tr>
                                        <th class="border p-3 text-left">Ensayo</th>
                                        <th class="border p-3 text-center">Fecha de análisis</th>
                                        <th class="border p-3 text-center">Resultado</th>
                                        <th class="border p-3 text-center">Unidad</th>
                                        <th class="border p-3 text-center">Código trazabilidad</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($m->ensayos as $ensayo)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="border p-3 font-medium text-gray-800">
                                                {{ $ensayo->nombre }}
                                            </td>

                                            <td class="border p-3 text-center">
                                                <input type="datetime-local"
                                                    name="ensayos[{{ $m->id }}][{{ $ensayo->id }}][fecha_analisis]"
                                                    value="{{ old('ensayos.'.$m->id.'.'.$ensayo->id.'.fecha_analisis', $ensayo->pivot->fecha_analisis ? \Carbon\Carbon::parse($ensayo->pivot->fecha_analisis)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </td>

                                            <td class="border p-3 text-center">
                                                <input type="text"
                                                    name="ensayos[{{ $m->id }}][{{ $ensayo->id }}][resultado]"
                                                    value="{{ old('ensayos.'.$m->id.'.'.$ensayo->id.'.resultado', $ensayo->pivot->resultado) }}"
                                                    placeholder="Ej: 5.2"
                                                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                            </td>

                                            <td class="border p-3 text-center">
                                                <input type="text"
                                                    name="ensayos[{{ $m->id }}][{{ $ensayo->id }}][unidad_medida]"
                                                    value="{{ old('ensayos.'.$m->id.'.'.$ensayo->id.'.unidad_medida', $ensayo->pivot->unidad_medida ?? $ensayo->unidad_medida) }}"
                                                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm text-center">
                                            </td>

                                            <td class="border p-3 text-center font-semibold text-gray-700">
                                                {{ $m->codigo_interno }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                {{-- 🔘 Acciones --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('resultados.index') }}"
                        class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-all font-medium shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm flex items-center gap-2 transition-all">
                        <x-heroicon-o-check class="w-5 h-5" />
                        Guardar resultados
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
