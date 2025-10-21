<x-app-layout>
    <div class="p-8 bg-gray-50 min-h-screen">

        <!-- 🧭 Encabezado -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary">Panel de Recepción</h1>
            <p class="text-gray-600 mt-2">
                Bienvenido/a,
                <span class="font-semibold text-primary">{{ auth()->user()->name }}</span>.
                Desde aquí puede gestionar las solicitudes y el estado de las muestras.
            </p>
        </div>

        <!-- 📊 Tarjetas resumen -->
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-6 mb-10">
            <!-- Total -->
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-gray-400">
                <div class="flex items-center">
                    <x-heroicon-o-rectangle-stack class="h-8 w-8 text-gray-500 mr-3" />
                    <div>
                        <h3 class="text-gray-600 text-sm font-semibold">Total solicitudes</h3>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalSolicitudes ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Pendientes -->
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-400">
                <div class="flex items-center">
                    <x-heroicon-o-clock class="h-8 w-8 text-yellow-500 mr-3" />
                    <div>
                        <h3 class="text-gray-600 text-sm font-semibold">Pendientes</h3>
                        <p class="text-2xl font-bold text-gray-800">{{ $pendientes ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- En proceso -->
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-400">
                <div class="flex items-center">
                    <x-heroicon-o-cog-6-tooth class="h-8 w-8 text-blue-500 mr-3" />
                    <div>
                        <h3 class="text-gray-600 text-sm font-semibold">En proceso</h3>
                        <p class="text-2xl font-bold text-gray-800">{{ $enProceso ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🧾 Últimas solicitudes -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-primary mr-2" />
                    Últimas solicitudes
                </h2>
                <a href="#" class="text-sm text-primary hover:underline font-semibold">Ver todas</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left bg-gray-100 text-gray-700 uppercase text-xs">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes ?? [] as $solicitud)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-800 font-medium">{{ $solicitud->id }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $solicitud->cliente->persona->nombre_completo ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $solicitud->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    @php
                                        $estados = $solicitud->muestras->pluck('estado')->unique();
                                    @endphp
                                    @foreach ($estados as $estado)
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-semibold 
                                            {{ match ($estado) {
                                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                                'en proceso' => 'bg-blue-100 text-blue-800',
                                                'finalizado' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            } }}">
                                            {{ ucfirst($estado) }}
                                        </span>
                                    @endforeach
                                </td>

                                <td class="px-4 py-3 flex space-x-3">
                                    <!-- Ver -->
                                    <a 
                                        class="text-blue-600 hover:underline font-semibold flex items-center">
                                        <x-heroicon-o-eye class="h-4 w-4 mr-1" /> Ver
                                    </a>

                                    <!-- Descargar -->
                                    <a href="{{ route('solicitudes.exportar', $solicitud->id) }}"
                                        class="text-green-600 hover:underline font-semibold flex items-center">
                                        <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-1" /> Descargar
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-500">
                                    No hay solicitudes registradas aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
