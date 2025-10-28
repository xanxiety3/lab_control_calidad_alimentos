<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10">
        {{-- 🔹 Tarjeta principal --}}
        <div class="bg-white shadow-lg rounded-2xl p-8 border border-gray-100">
            {{-- 🔸 Encabezado --}}
            <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                    <x-heroicon-o-beaker class="w-8 h-8 text-blue-600" />
                    Panel del Analista
                </h1>

                {{-- 🔸 Contadores --}}
                <div class="flex flex-wrap gap-2">
                    <button onclick="filtrar('todas')"
                        class="estado-btn bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 px-4 py-1.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                        Todas <span class="ml-1 font-semibold text-gray-800">({{ $solicitudes->count() }})</span>
                    </button>

                    <button onclick="filtrar('pendiente')"
                        class="estado-btn bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-4 py-1.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                        Pendientes <span
                            class="ml-1 font-semibold text-blue-800">({{ $solicitudes->where('estado_global', 'pendiente')->count() }})</span>
                    </button>

                    <button onclick="filtrar('en_proceso')"
                        class="estado-btn bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-4 py-1.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                        En proceso <span
                            class="ml-1 font-semibold text-amber-800">({{ $solicitudes->where('estado_global', 'en_proceso')->count() }})</span>
                    </button>

                    <button onclick="filtrar('finalizada')"
                        class="estado-btn bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-4 py-1.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                        Finalizadas <span
                            class="ml-1 font-semibold text-emerald-800">({{ $solicitudes->where('estado_global', 'finalizada')->count() }})</span>
                    </button>
                </div>
            </div>

            {{-- ✅ Mensaje de éxito --}}
            @if (session('success'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 rounded-lg mb-5 flex items-center gap-2 shadow-sm">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- 🧪 Tabla de solicitudes --}}
            <div class="overflow-hidden border border-gray-100 rounded-xl">
                <table class="w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="p-3 text-left font-semibold">N° Solicitud</th>
                            <th class="p-3 text-left font-semibold">Cliente</th>
                            <th class="p-3 text-center font-semibold">Fecha</th>
                            <th class="p-3 text-center font-semibold">Muestras</th>
                            <th class="p-3 text-center font-semibold">Entrega</th>
                            <th class="p-3 text-center font-semibold">Estado</th>
                            <th class="p-3 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($solicitudes as $solicitud)
                            <tr data-estado="{{ $solicitud->estado_global }}" class="hover:bg-gray-50 transition-all">
                                <td class="p-3 font-semibold text-blue-700">
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

                                <td class="p-3 text-center">
                                    @if ($solicitud->estado_global === 'pendiente')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">Pendiente</span>
                                    @elseif ($solicitud->estado_global === 'en_proceso')
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">En
                                            proceso</span>
                                    @else
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">Finalizada</span>
                                    @endif
                                </td>

                                {{-- 🎯 Acciones (solo registrar resultados) --}}
                                <td class="p-3 text-center">
                                    @if ($solicitud->estado_global !== 'finalizada')
                                        <a href="{{ route('resultados.edit', $solicitud->muestras->first()->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg shadow-sm transition-all">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                            Registrar resultados
                                        </a>
                                    @else
                                        <span
                                            class="text-gray-400 text-xs italic">Resultados finalizados</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-6">
                                    No hay solicitudes registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 🧭 Filtro dinámico de estados --}}
    <script>
        function filtrar(estado) {
            document.querySelectorAll('tbody tr').forEach(row => {
                row.classList.toggle('hidden', estado !== 'todas' && row.dataset.estado !== estado);
            });

            // Efecto visual de selección en botones
            document.querySelectorAll('.estado-btn').forEach(btn => {
                btn.classList.remove('ring', 'ring-offset-1');
            });
            event.target.classList.add('ring', 'ring-offset-1');
        }
    </script>
</x-app-layout>
