<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10">
        {{-- 🔹 Tarjeta principal --}}
        <div class="bg-white shadow-lg rounded-2xl p-8 border border-gray-100">
            {{-- 🔸 Encabezado --}}
            <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-indigo-600" />
                    Panel del Gestor Técnico 
                </h1>
            </div>

            {{-- ✅ Mensaje de éxito --}}
            @if (session('success'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 rounded-lg mb-5 flex items-center gap-2 shadow-sm">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- 🧪 Tabla --}}
            <div class="overflow-hidden border border-gray-100 rounded-xl">
                <table class="w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="p-3 text-left font-semibold">N° Solicitud</th>
                            <th class="p-3 text-left font-semibold">Cliente</th>
                            <th class="p-3 text-center font-semibold">Fecha</th>
                            <th class="p-3 text-center font-semibold">Muestras</th>
                            <th class="p-3 text-center font-semibold">Entrega</th>
                            <th class="p-3 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @php
                            $solicitudesFinalizadas = $solicitudes->where('estado_global', 'finalizada');
                        @endphp

                        @forelse ($solicitudesFinalizadas as $solicitud)
                            <tr class="hover:bg-gray-50 transition-all">
                                <td class="p-3 font-semibold text-indigo-700">
                                    {{ $solicitud->numero_solicitud }}
                                </td>

                                <td class="p-3">
                                    {{ $solicitud->cliente->persona->nombre_completo ?? ($solicitud->cliente->razon_social ?? '—') }}
                                </td>

                                <td class="p-3 text-center text-gray-600">
                                    {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('Y-m-d') }}
                                </td>

                                <td class="p-3 text-center font-medium text-gray-700">
                                    {{ $solicitud->muestras->count() }}
                                </td>

                                <td class="p-3 text-center capitalize text-gray-700">
                                    {{ $solicitud->entrega_resultados }}
                                </td>

                                {{-- 🎯 Acciones --}}
                                <td class="p-3 text-center space-x-2">
                                    {{-- Descargar --}}
                                    <a href="{{ route('resultados.exportar', ['id' => $solicitud->id]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs rounded-lg shadow-sm transition-all">
                                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                        Descargar
                                    </a>

                                    {{-- Enviar correo (si aplica) --}}
                                    @if (in_array($solicitud->entrega_resultados, ['correo', 'ambos']))
                                        <a href="{{ route('resultados.enviarCorreo', $solicitud->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs rounded-lg shadow-sm transition-all">
                                            <x-heroicon-o-envelope class="w-4 h-4" />
                                            Enviar correo
                                        </a>
                                    @endif

                                    {{-- Editar --}}
                                    <a href="{{ route('resultados.edit', $solicitud->muestras->first()->id) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg shadow-sm transition-all">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        Editar resultados
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-500 py-6">
                                    No hay solicitudes finalizadas para mostrar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
