<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GestorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\RemisionController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\UserController;
use App\Models\Ensayo;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// 🧭 Dashboard general — cualquier usuario autenticado y verificado
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// 🔐 Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {

    // ⚙️ Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 🧩 Dashboards según rol
    Route::get('/dashboard/admin', fn() => view('dashboard.admin'))->name('dashboard.admin');
    Route::get('/dashboard/recepcion', [RecepcionController::class, 'index'])->name('dashboard.recepcion');
    Route::get('/dashboard/analista', [ResultadoController::class, 'index'])->name('dashboard.analista');
    Route::get('/dashboard/gestor', [GestorController::class, 'index'])->name('dashboard.gestor');
    Route::get('/dashboard/consulta', fn() => view('dashboard.consulta'))->name('dashboard.consulta');


    /*
    |--------------------------------------------------------------------------
    | USUARIOS (solo Admin o quien tenga permiso "gestionar_usuarios")
    |--------------------------------------------------------------------------
    */
    Route::middleware(['permiso:gestionar_usuarios'])->group(function () {
        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::patch('usuarios/{usuario}/estado', [UserController::class, 'cambiarEstado'])
            ->name('usuarios.estado');
    });


    // Ejemplo: solo quien tenga permiso registrar_remisiones
    Route::middleware(['auth', 'permiso:crear_solicitud'])->group(function () {
        Route::get('/remisiones/create', [RemisionController::class, 'create'])->name('remisiones.create');
        Route::post('/remisiones', [RemisionController::class, 'store'])->name('remisiones.store');

        Route::get('/api/municipios/{departamento}', [ClienteController::class, 'porDepartamento']);

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');

        Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{id}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{id}', [ClienteController::class, 'update'])->name('clientes.update');

        Route::get('/ensayos/{tipoMuestraId}', function ($id) {
            return Ensayo::where('tipo_muestra_id', $id)
                ->where('activo', true)
                ->get(['id', 'nombre']);
        });


        Route::get('/solicitudes/{id}/descargar', [RemisionController::class, 'exportar'])
            ->name('solicitudes.exportar');

        Route::get('/recepcion/{id}', [RecepcionController::class, 'show'])->name('recepcion.show');

        Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes');
    });


    Route::middleware(['permiso:registrar_resultados'])->group(function () {
        Route::get('/resultados', [ResultadoController::class, 'index'])->name('resultados.index');

        // ✅ Para el analista - Nuevo formato simple
        Route::get('/resultados/{solicitud}/exportar-simple', [ResultadoController::class, 'exportarDatos'])
            ->name('resultados.exportar.simple');

        // Registrar resultados
        Route::get('/resultados/{solicitud}', [ResultadoController::class, 'edit'])->name('resultados.edit');

        Route::put('/resultados/{solicitud}', [ResultadoController::class, 'update'])
            ->name('resultados.update');

        Route::post('/reportes/{id}/enviar', [ResultadoController::class, 'enviarReporte'])
            ->name('reportes.enviar');

        Route::get('/resultados/{solicitud}/show', [ResultadoController::class, 'show'])->name('resultados.show');
    });

    Route::middleware(['permiso:registrar_resultado_final'])->group(function () {
        Route::get('/gestor-tecnico', [GestorController::class, 'index'])->name('gestor.index');

        Route::get('/solicitud/{id}/edit', [GestorController::class, 'edit'])->name('gestor.edit');
        Route::put('/solicitud/{id}/aprobar', [GestorController::class, 'aprobar'])->name('gestor.aprobar');
        Route::post('/gestor/accion-ajax', [GestorController::class, 'accionAjax'])->name('gestor.accion.ajax');

        Route::post('/gestor/acciones/{id}', [GestorController::class, 'accion'])->name('gestor.accion');
        Route::get('/solicitud/{id}/descargar', [GestorController::class, 'descargar'])->name('gestor.descargar');
        Route::post('/solicitud/{id}/enviar', [GestorController::class, 'enviar'])->name('gestor.enviar');

        Route::post('/gestor/cambiar-estado/{id}', [GestorController::class, 'cambiarEstado'])
            ->name('gestor.cambiarEstado');

        Route::get('/gestor/ensayo/{id}/editar', [GestorController::class, 'editEnsayo'])
            ->name('gestor.editEnsayo');

        Route::put('/gestor/ensayo/{id}/update', [GestorController::class, 'updateEnsayo'])
            ->name('gestor.update');


        // ✅ Para el gestor - Formato completo (mantener el original)
        Route::get('/resultados/{solicitud}/exportar-completo', [GestorController::class, 'exportar'])
            ->name('gestor.exportar');

        Route::get('/resultados/enviar/{id}', [GestorController::class, 'enviarCorreo'])
            ->name('gestor.enviarCorreo');
    });
});

require __DIR__ . '/auth.php';
