<?php

namespace App\Http\Controllers;

use App\Models\RegistroTemperatura;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GraficasController extends Controller
{
 public function graficas()
{
    // Paginación de registros
    $registros = RegistroTemperatura::orderBy('created_at')->paginate(6);

    if ($registros->isEmpty()) {
        return view('graficas.index', [
            'dias' => [],
            'temp_9' => [], 'temp_11' => [], 'temp_15' => [],
            'hum_9' => [], 'hum_11' => [], 'hum_15' => [],
            'registros' => $registros
        ]);
    }

    // Para gráficos, obtener todos (sin paginar)
    $todosRegistros = RegistroTemperatura::orderBy('created_at')->get();

    $diasAgrupados = $todosRegistros->groupBy(function ($item) {
        return $item->created_at->format('Y-m-d');
    });

    $dias = [];
    $temp_9 = []; $temp_11 = []; $temp_15 = [];
    $hum_9  = []; $hum_11  = []; $hum_15  = [];

    foreach ($diasAgrupados as $dia => $items) {

        // Si no tiene mínimo 6 registros, saltarlo
        if ($items->count() < 6) {
            continue;
        }

        $dias[] = date('d', strtotime($dia));

        $items = $items->values(); // Reindexar

        // Extraer valores más seguro
        $t9  = $items->slice(0,2)->firstWhere('tipo','temperatura')->valor_corregido ?? null;
        $h9  = $items->slice(0,2)->firstWhere('tipo','humedad')->valor_corregido ?? null;

        $t11 = $items->slice(2,2)->firstWhere('tipo','temperatura')->valor_corregido ?? null;
        $h11 = $items->slice(2,2)->firstWhere('tipo','humedad')->valor_corregido ?? null;

        $t15 = $items->slice(4,2)->firstWhere('tipo','temperatura')->valor_corregido ?? null;
        $h15 = $items->slice(4,2)->firstWhere('tipo','humedad')->valor_corregido ?? null;

        $temp_9[] = $t9;
        $hum_9[]  = $h9;

        $temp_11[] = $t11;
        $hum_11[]  = $h11;

        $temp_15[] = $t15;
        $hum_15[]  = $h15;
    }

    return view('graficas.index', compact(
        'dias',
        'temp_9', 'temp_11', 'temp_15',
        'hum_9', 'hum_11', 'hum_15',
        'registros'
    ));
}


    
}
