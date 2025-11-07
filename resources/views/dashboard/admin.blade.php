<x-app-layout>
   
        <!-- ENCABEZADO -->
        <div class="text-center space-y-2">
            <h1 class="text-3xl font-extrabold text-primary tracking-tight">Panel del Administrador</h1>
            <p class="text-gray-600 text-base">
                Bienvenido, <span class="font-semibold text-primary">{{ auth()->user()->name }}</span> 👋<br>
                Selecciona una sección para comenzar .
            </p>
        </div>

</x-app-layout>
