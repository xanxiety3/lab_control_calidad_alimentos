<x-app-layout>
    <div class="max-w-7xl mx-auto p-6 space-y-6">

        <!-- Encabezado -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-primary">Panel del Administrador</h1>
            <p class="text-gray-600 mt-1 text-sm">
                Bienvenido, {{ auth()->user()->name }} 👋
                <br>Selecciona una sección para comenzar.
            </p>
        </div>

        <!-- Grid de tarjetas -->
<div class="grid gap-x-10 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">

            <!-- Usuarios -->
            <a href="{{ route('usuarios.index') }}"
                class="group bg-white p-5 rounded-xl shadow hover:shadow-lg hover:bg-primary hover:text-white transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <x-heroicon-o-user-group class="w-6 h-6 text-primary group-hover:text-white" />
                    <span
                        class="bg-primary/10 group-hover:bg-white/20 text-primary group-hover:text-white text-xs font-semibold px-2 py-0.5 rounded-full">Gestión</span>
                </div>
                <h2 class="text-lg font-semibold mb-1">Usuarios</h2>
                <p class="text-gray-600 text-sm group-hover:text-white">Administra usuarios, roles y permisos del
                    sistema.</p>
            </a>

            <!-- Clientes -->
            <a href="{{ route('clientes.index') }}"
                class="group bg-white p-5 rounded-xl shadow hover:shadow-lg hover:bg-primary hover:text-white transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <x-heroicon-o-users class="w-6 h-6 text-primary group-hover:text-white" />
                    <span
                        class="bg-primary/10 group-hover:bg-white/20 text-primary group-hover:text-white text-xs font-semibold px-2 py-0.5 rounded-full">Datos</span>
                </div>
                <h2 class="text-lg font-semibold mb-1">Clientes</h2>
                <p class="text-gray-600 text-sm group-hover:text-white">Consulta y gestiona la información de los
                    clientes.</p>
            </a>

            <!-- Remisiones -->
            <a href="{{ route('remisiones.create') }}"
                class="group bg-white p-5 rounded-xl shadow hover:shadow-lg hover:bg-primary hover:text-white transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary group-hover:text-white" />
                    <span
                        class="bg-primary/10 group-hover:bg-white/20 text-primary group-hover:text-white text-xs font-semibold px-2 py-0.5 rounded-full">Registro</span>
                </div>
                <h2 class="text-lg font-semibold mb-1">Remisiones</h2>
                <p class="text-gray-600 text-sm group-hover:text-white">Registra nuevas remisiones y verifica su estado.
                </p>
            </a>

            <!-- Ensayos -->
            <a href="{{ route('resultados.index') }}"
                class="group bg-white p-5 rounded-xl shadow hover:shadow-lg hover:bg-primary hover:text-white transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <x-heroicon-o-beaker class="w-6 h-6 text-primary group-hover:text-white" />
                    <span
                        class="bg-primary/10 group-hover:bg-white/20 text-primary group-hover:text-white text-xs font-semibold px-2 py-0.5 rounded-full">Laboratorio</span>
                </div>
                <h2 class="text-lg font-semibold mb-1">Ensayos</h2>
                <p class="text-gray-600 text-sm group-hover:text-white">Accede al módulo de ensayos y resultados.</p>
            </a>

            <!-- Informes -->
            <a href="{{ route('reportes') }}"
                class="group bg-white p-5 rounded-xl shadow hover:shadow-lg hover:bg-primary hover:text-white transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-primary group-hover:text-white" />
                    <span
                        class="bg-primary/10 group-hover:bg-white/20 text-primary group-hover:text-white text-xs font-semibold px-2 py-0.5 rounded-full">Reportes</span>
                </div>
                <h2 class="text-lg font-semibold mb-1">Informes</h2>
                <p class="text-gray-600 text-sm group-hover:text-white">Consulta reportes y estadísticas del
                    laboratorio.</p>
            </a>

            <!-- Configuración -->
            <a href="#"
                class="group bg-white p-5 rounded-xl shadow hover:shadow-lg hover:bg-primary hover:text-white transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-primary group-hover:text-white" />
                    <span
                        class="bg-primary/10 group-hover:bg-white/20 text-primary group-hover:text-white text-xs font-semibold px-2 py-0.5 rounded-full">Sistema</span>
                </div>
                <h2 class="text-lg font-semibold mb-1">Configuración</h2>
                <p class="text-gray-600 text-sm group-hover:text-white">Gestiona parámetros generales del sistema.</p>
            </a>
        </div>
    </div>
</x-app-layout>
