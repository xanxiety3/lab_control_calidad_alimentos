<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LCCA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col">

        <!-- NAVBAR SUPERIOR -->
        <nav class="bg-primary text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">

                    <!-- Logo -->
                    <div class="flex items-center space-x-1">
                        <x-application-logo class="h-16 w-auto" />
                        <span class="font-semibold text-lg tracking-wide">LCCA</span>
                    </div>
                    <!-- Menú principal -->
                    <div class="hidden md:flex space-x-6 text-sm">
                        {{-- 🏠 Inicio --}}
                        <a href="{{ rutaDashboardPorRol() }}"
                            class="hover:text-secondary flex items-center space-x-1 {{ request()->routeIs('dashboard.*') ? 'text-secondary font-semibold' : '' }}">
                            <x-heroicon-o-home class="w-5 h-5" />
                            <span>Inicio</span>
                        </a>


                        {{-- 📋 Registrar remisión (solo recepción) --}}
                        @permiso('crear_solicitud')
                            <a href="{{ route('remisiones.create') }}" class="hover:text-secondary flex items-center space-x-1">
                                <x-heroicon-o-document-text class="w-5 h-5" />
                                <span>Registrar remisión</span>
                            </a>
                        @endpermiso

                        {{-- 🧪 Ensayos (solo analista) --}}
                        @permiso('registrar_resultado')
                            <a href="#" class="hover:text-secondary flex items-center space-x-1">
                                <x-heroicon-o-beaker class="w-5 h-5" />
                                <span>Ensayos</span>
                            </a>
                        @endpermiso

                        {{-- 👥 Clientes (visible para recepción y gestor técnico) --}}
                        @permiso('registrar_muestra')
                            <a href="{{ route('clientes.index') }}" class="hover:text-secondary flex items-center space-x-1">
                                <x-heroicon-o-users class="w-5 h-5" />
                                <span>Clientes</span>
                            </a>
                        @endpermiso

                        {{-- ⚙️ Usuarios (solo admin) --}}
                        @permiso('gestionar_usuarios')
                            <a href="{{ route('usuarios.index') }}"
                                class="hover:text-secondary flex items-center space-x-1">
                                <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                                <span>Usuarios</span>
                            </a>
                        @endpermiso

                        {{-- 📊 Informes (solo consulta) --}}
                        @permiso('ver_informes')
                            <a href="{{ route('reportes') }}" class="hover:text-secondary flex items-center space-x-1">
                                <x-heroicon-o-chart-bar class="w-5 h-5" />
                                <span>Informes</span>
                            </a>
                        @endpermiso
                    </div>


                    <!-- Menú de usuario -->
                    <div class="relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="flex items-center text-sm font-medium text-white hover:text-secondary focus:outline-none transition">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ms-2">
                                        <x-heroicon-o-chevron-down class="w-4 h-4" />
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <!-- Perfil -->
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Editar perfil') }}
                                </x-dropdown-link>

                                <!-- Cerrar sesión -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Cerrar sesión') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>

        <!-- FOOTER -->
        <footer class="bg-gray-200 text-gray-600 text-sm text-center py-3">
            © {{ date('Y') }} Laboratorio de Control de Calidad de Alimentos - LCCA.
        </footer>
    </div>
</body>

</html>
