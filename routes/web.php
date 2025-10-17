<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/dashboard/admin', fn() => view('dashboard.admin'))->name('dashboard.admin');
    Route::get('/dashboard/recepcion', fn() => view('dashboard.recepcion'))->name('dashboard.recepcion');
    Route::get('/dashboard/analista', fn() => view('dashboard.analista'))->name('dashboard.analista');
    Route::get('/dashboard/gestor', fn() => view('dashboard.gestor'))->name('dashboard.gestor');
    Route::get('/dashboard/consulta', fn() => view('dashboard.consulta'))->name('dashboard.consulta');
});

require __DIR__ . '/auth.php';
