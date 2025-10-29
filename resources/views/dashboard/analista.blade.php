<x-app-layout>
    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8">
        {{-- 🔹 Tarjeta principal --}}
        <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100/50 ring-1 ring-primary/10">
            {{-- 🔸 Encabezado --}}
            <div class="flex flex-col md:flex-row justify-between md:items-center mb-8 gap-6">
                <h1 class="text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                    <x-heroicon-o-beaker class="w-9 h-9 text-primary" />
                    Panel del Analista
                </h1>

                {{-- 🔸 Contadores --}}
                <div class="flex flex-wrap gap-3">
                    <button onclick="filtrar('todas')"
                        class="estado-btn bg-gray-50 hover:bg-gray-100 text-gray-800 border border-gray-200 px-5 py-2 rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow-md focus:ring-2 focus:ring-primary focus:ring-offset-1">
                        Todas <span class="ml-1 font-bold text-primary">({{ $contadores['todas'] }})</span>
                    </button>

                    <button onclick="filtrar('pendiente')"
                        class="estado-btn bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 px-5 py-2 rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow-md focus:ring-2 focus:ring-primary focus:ring-offset-1">
                        Pendientes <span class="ml-1 font-bold text-primary">({{ $contadores['pendiente'] }})</span>
                    </button>

                    <button onclick="filtrar('en_proceso')"
                        class="estado-btn bg-secondary/10 hover:bg-secondary/20 text-secondary border border-secondary/20 px-5 py-2 rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow-md focus:ring-2 focus:ring-secondary focus:ring-offset-1">
                        En proceso <span class="ml-1 font-bold text-secondary">({{ $contadores['en_proceso'] }})</span>
                    </button>

                    <button onclick="filtrar('finalizada')"
                        class="estado-btn bg-emerald-50/50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200/50 px-5 py-2 rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow-md focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                        Finalizadas <span
                            class="ml-1 font-bold text-emerald-800">({{ $contadores['finalizada'] }})</span>
                    </button>
                </div>
            </div>

            {{-- ✅ Mensaje de éxito --}}
            @if (session('success'))
                <div
                    class="bg-emerald-50/80 border-l-4 border-secondary text-emerald-800 px-5 py-3 rounded-lg mb-6 flex items-center gap-3 shadow-sm transform transition-all hover:scale-[1.01]">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-secondary" />
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- 🧪 Tabla de solicitudes --}}
            <div class="overflow-hidden border border-gray-100/50 rounded-xl shadow-sm">
                <table class="w-full text-sm text-gray-700">
                    <thead class="bg-primary/5 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                        <tr>
                            <th class="p-4 text-left">N° Solicitud</th>
                            <th class="p-4 text-left">Cliente</th>
                            <th class="p-4 text-center">Fecha</th>
                            <th class="p-4 text-center">Muestras</th>
                            <th class="p-4 text-center">Entrega</th>
                            <th class="p-4 text-center">Estado</th>
                            <th class="p-4 text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100/50">
                        @foreach ($solicitudes as $solicitud)
                            <tr class="solicitud-row hover:bg-primary/5 transition-all duration-200"
                                data-estado="{{ $solicitud->estado_global }}">
                                <td class="p-4 font-semibold text-primary">
                                    {{ $solicitud->numero_solicitud }}
                                </td>

                                <td class="p-4 text-gray-800">
                                    {{ $solicitud->cliente->persona->nombre_completo ?? ($solicitud->cliente->razon_social ?? '—') }}
                                </td>

                                <td class="p-4 text-center text-gray-600">
                                    {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('Y-m-d') }}
                                </td>

                                <td class="p-4 text-center font-medium text-gray-700">
                                    {{ $solicitud->muestras->count() }}
                                </td>

                                <td class="p-4 text-center capitalize text-gray-700">
                                    {{ $solicitud->entrega_resultados }}
                                </td>

                                <td class="p-4 text-center">
                                    @php
                                        $estadoConfig = [
                                            'pendiente' => ['color' => 'primary', 'texto' => 'Pendiente'],
                                            'en_proceso' => ['color' => 'secondary', 'texto' => 'En proceso'],
                                            'finalizada' => ['color' => 'emerald', 'texto' => 'Finalizada'],
                                        ];
                                        $config =
                                            $estadoConfig[$solicitud->estado_global] ?? $estadoConfig['pendiente'];
                                    @endphp

                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 shadow-sm">
                                        @if ($solicitud->estado_global === 'pendiente')
                                            <x-heroicon-o-clock class="w-4 h-4" />
                                        @elseif($solicitud->estado_global === 'en_proceso')
                                            <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                                        @else
                                            <x-heroicon-o-check-badge class="w-4 h-4" />
                                        @endif
                                        {{ $config['texto'] }}
                                    </span>
                                </td>

                                {{-- 🎯 Acciones --}}
                                <td class="p-4 text-center">
                                    @if ($solicitud->estado_global !== 'finalizada')
                                        @if ($solicitud->muestras->isNotEmpty())
                                            <a href="{{ route('resultados.edit', $solicitud->id) }}"
                                                class="inline-flex items-center gap-1 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                                                <x-heroicon-o-pencil-square class="w-4 h-4 " />
                                                @if ($solicitud->estado_global === 'pendiente')
                                                    Iniciar resultados
                                                @else
                                                    Continuar resultados
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Sin muestras</span>
                                        @endif
                                    @else
                                        <a href="{{ route('resultados.show', $solicitud->id) }}"
                                            class="inline-flex items-center gap-1 px-4 py-2 bg-secondary hover:bg-secondary/90 text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            Ver resultados
                                        </a>
                                        <a href="{{ route('resultados.exportar.simple', $solicitud->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg shadow-sm transition-all">
                                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                            Excel
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 🧭 Filtro dinámico de estados (sin cambios) --}}
    <script>
        function filtrar(estado) {
            const rows = document.querySelectorAll('tr.solicitud-row');

            rows.forEach(row => {
                if (estado === 'todas' || row.getAttribute('data-estado') === estado) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            document.querySelectorAll('.estado-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-offset-1', 'ring-primary');
            });

            const botones = document.querySelectorAll('.estado-btn');
            botones.forEach(boton => {
                if (boton.textContent.includes(estado.charAt(0).toUpperCase() + estado.slice(1)) ||
                    (estado === 'todas' && boton.textContent.includes('Todas'))) {
                    boton.classList.add('ring-2', 'ring-offset-1', 'ring-primary');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            filtrar('todas');
        });
    </script>
</x-app-layout>
