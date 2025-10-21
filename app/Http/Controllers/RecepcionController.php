<?php

namespace App\Http\Controllers;

use App\Models\Muestra;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class RecepcionController extends Controller
{
    public function index()
    { // Contadores según el estado de las muestras
        $totalSolicitudes = Solicitud::count();
        $pendientes = Solicitud::whereHas('muestras', function ($q) {
            $q->where('estado', 'pendiente');
        })->count();
        $enProceso = Solicitud::whereHas('muestras', function ($q) {
            $q->where('estado', 'en proceso');
        })->count();

        // Últimas solicitudes registradas
        $solicitudes = Solicitud::with(['muestras', 'cliente'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.recepcion', compact('totalSolicitudes', 'pendientes', 'enProceso', 'solicitudes'));
    }
}
