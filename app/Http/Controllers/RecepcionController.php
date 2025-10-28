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


    public function show($id)
    {
        $solicitud = Solicitud::with([
            'cliente.persona',          // relación con cliente
            'muestras.tipoMuestra',     // tipo de muestra
            'muestras',
            'muestras.ensayos', // técnicas si existen
        ])->findOrFail($id);

        return view('remisiones.show', compact('solicitud'));
    }

     public function gestorFinalizadas()
    {
        $solicitudes = Solicitud::with([
            'cliente.persona',
            'muestras'
        ])->orderByDesc('created_at')->get();

        // Calcular estado global
        foreach ($solicitudes as $solicitud) {
            $total = $solicitud->muestras->count();
            $pendientes = $solicitud->muestras->where('estado', 'pendiente')->count();
            $finalizadas = $solicitud->muestras->where('estado', 'finalizada')->count();

            if ($pendientes === $total) {
                $solicitud->estado_global = 'pendiente';
            } elseif ($finalizadas === $total) {
                $solicitud->estado_global = 'finalizada';
            } else {
                $solicitud->estado_global = 'en_proceso';
            }
        }

        // Filtrar solo las finalizadas
        $solicitudes = $solicitudes->where('estado_global', 'finalizada');

        return view('dashboard.gestor', compact('solicitudes'));
    }
}
