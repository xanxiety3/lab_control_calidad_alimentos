<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-8">
        <h1 class="text-3xl font-bold text-center text-blue-900 mb-8 flex items-center justify-center gap-2">
            <x-heroicon-o-beaker class="w-8 h-8 text-blue-700" />
            Dashboard del Laboratorio
        </h1>

        {{-- Contenedor general --}}
        <div class="flex flex-wrap justify-center gap-8">

            {{-- 📅 Solicitudes por mes --}}
            <div class="bg-white rounded-2xl shadow-lg p-4 w-full md:w-[45%]">
                <h2 class="text-lg font-semibold text-center mb-2 flex items-center justify-center gap-1">
                    <x-heroicon-o-calendar class="w-5 h-5 text-blue-600" />
                    Solicitudes por mes
                </h2>
                <div class="chart-container">
                    <canvas id="solicitudesMes"></canvas>
                </div>
                <button id="descargarSolicitudes" class="mt-3 w-full bg-blue-600 text-white py-1 rounded-lg hover:bg-blue-700 transition">Descargar</button>
            </div>

            {{-- 🧪 Muestras por tipo --}}
            <div class="bg-white rounded-2xl shadow-lg p-4 w-full md:w-[45%]">
                <h2 class="text-lg font-semibold text-center mb-2 flex items-center justify-center gap-1">
                    <x-heroicon-o-beaker class="w-5 h-5 text-green-600" />

                    Muestras por tipo
                </h2>
                <div class="chart-container">
                    <canvas id="muestrasTipo"></canvas>
                </div>
                <button id="descargarMuestras" class="mt-3 w-full bg-green-600 text-white py-1 rounded-lg hover:bg-green-700 transition">Descargar</button>
            </div>

            {{-- 📈 Promedio diario --}}
            <div class="bg-white rounded-2xl shadow-lg p-4 w-full md:w-[45%]">
                <h2 class="text-lg font-semibold text-center mb-2 flex items-center justify-center gap-1">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-pink-600" />
                    Promedio diario
                </h2>
                <div class="chart-container">
                    <canvas id="promedioDiario"></canvas>
                </div>
                <button id="descargarPromedio" class="mt-3 w-full bg-pink-600 text-white py-1 rounded-lg hover:bg-pink-700 transition">Descargar</button>
            </div>

            {{-- 🔬 Ensayos frecuentes --}}
            <div class="bg-white rounded-2xl shadow-lg p-4 w-full md:w-[45%]">
                <h2 class="text-lg font-semibold text-center mb-2 flex items-center justify-center gap-1">
                    <x-heroicon-o-magnifying-glass-circle class="w-5 h-5 text-indigo-600" />
                    Ensayos frecuentes
                </h2>
                <div class="chart-container">
                    <canvas id="ensayosFrecuentes"></canvas>
                </div>
                <button id="descargarEnsayos" class="mt-3 w-full bg-indigo-600 text-white py-1 rounded-lg hover:bg-indigo-700 transition">Descargar</button>
            </div>
        </div>
    </div>

    <style>
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.font.size = 13;

        // 📅 Solicitudes por mes
        const solicitudesChart = new Chart(document.getElementById('solicitudesMes'), {
            type: 'bar',
            data: {
                labels: @json($solicitudesPorMes->pluck('mes_nombre')),
                datasets: [{
                    label: 'Solicitudes por mes',
                    data: @json($solicitudesPorMes->pluck('total')),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderRadius: 5,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 🧪 Muestras por tipo
        const muestrasChart = new Chart(document.getElementById('muestrasTipo'), {
            type: 'pie',
            data: {
                labels: @json($muestrasPorTipo->pluck('nombre')),
                datasets: [{
                    data: @json($muestrasPorTipo->pluck('total')),
                    backgroundColor: ['#4BC0C0', '#FF9F40', '#9966FF', '#36A2EB', '#FF6384'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 📈 Promedio diario
        const promedioChart = new Chart(document.getElementById('promedioDiario'), {
            type: 'line',
            data: {
                labels: @json($solicitudesDiarias->pluck('fecha')),
                datasets: [{
                    label: 'Solicitudes diarias',
                    data: @json($solicitudesDiarias->pluck('total')),
                    borderColor: '#FF6384',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 🔬 Ensayos frecuentes
        const ensayosChart = new Chart(document.getElementById('ensayosFrecuentes'), {
            type: 'doughnut',
            data: {
                labels: @json($ensayosFrecuentes->pluck('nombre')),
                datasets: [{
                    data: @json($ensayosFrecuentes->pluck('total')),
                    backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 🧾 Función para descargar una gráfica como imagen
        function descargarGrafica(canvasId, nombreArchivo) {
            const canvas = document.getElementById(canvasId);
            const url = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.href = url;
            link.download = `${nombreArchivo}.png`;
            link.click();
        }

        document.getElementById('descargarSolicitudes').addEventListener('click', () => descargarGrafica('solicitudesMes', 'solicitudes_por_mes'));
        document.getElementById('descargarMuestras').addEventListener('click', () => descargarGrafica('muestrasTipo', 'muestras_por_tipo'));
        document.getElementById('descargarPromedio').addEventListener('click', () => descargarGrafica('promedioDiario', 'promedio_diario'));
        document.getElementById('descargarEnsayos').addEventListener('click', () => descargarGrafica('ensayosFrecuentes', 'ensayos_frecuentes'));
    </script>
</x-app-layout>
