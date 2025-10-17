<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio de Control de Calidad de Alimentos - LCCA</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Encabezado -->
    <header class="bg-white py-4 shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto flex items-center justify-center">
            <img src="{{ asset('images/logo-lcca.png') }}" alt="Logo LCCA" class="h-16 w-auto">
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="flex-grow flex flex-col justify-center items-center px-4">
        <div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full text-center border-t-4 border-secondary">
            <h2 class="text-2xl font-bold text-primary mb-3">Bienvenida al Sistema</h2>
            <p class="text-gray-600 mb-8 leading-relaxed">
                Acceda con su usuario institucional para gestionar solicitudes, resultados y control de muestras del Laboratorio de Control de Calidad de Alimentos.
            </p>

            <a href="{{ route('login') }}"
               class="bg-secondary hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold text-lg transition-all shadow-md hover:shadow-lg inline-block">
                Iniciar Sesión
            </a>
        </div>
    </main>

    <!-- Pie de página -->
    <footer class="bg-primary text-gray-100 py-4 text-center text-sm mt-10">
        © {{ date('Y') }} Laboratorio de Control de Calidad de Alimentos - LCCA. Todos los derechos reservados.
    </footer>

</body>
</html>
