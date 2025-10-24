<?php

// app/Http/Controllers/ReporteController.php

namespace App\Http\Controllers;

use App\Models\Muestra;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{
    public function index()
    {
        // 1️⃣ Solicitudes procesadas por mes
        $solicitudesPorMes = DB::table('solicitudes')
            ->select(DB::raw('MONTH(created_at) as mes'), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(function ($item) {
                $meses = [
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre',
                ];
                $item->mes_nombre = $meses[$item->mes];
                return $item;
            });


        $muestrasPorTipo = Muestra::with('tipoMuestra')
            ->select('tipo_muestra_id', DB::raw('count(*) as total'))
            ->groupBy('tipo_muestra_id')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->tipoMuestra->nombre ?? 'Sin tipo',
                    'total' => $item->total,
                ];
            });


        // 3️⃣ Promedio diario de solicitudes
        $solicitudesDiarias = DB::table('solicitudes')
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(*) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // 4️⃣ Ensayos más frecuentes (reales)
        $ensayosFrecuentes = DB::table('muestra_ensayo')
            ->join('ensayos', 'muestra_ensayo.ensayo_id', '=', 'ensayos.id')
            ->select('ensayos.nombre', DB::raw('count(muestra_ensayo.id) as total'))
            ->groupBy('ensayos.nombre')
            ->orderBy('total', 'desc')
            ->get();

        return view('remisiones.reportes', compact(
            'solicitudesPorMes',
            'muestrasPorTipo',
            'solicitudesDiarias',
            'ensayosFrecuentes'
        ));
    }
}
