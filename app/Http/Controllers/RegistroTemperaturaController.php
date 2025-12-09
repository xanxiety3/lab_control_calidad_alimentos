<?php

namespace App\Http\Controllers;

use App\Models\ParametrosCorrecion;
use App\Models\RegistroTemperatura;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RegistroTemperaturaController extends Controller
{
     public function index()
    {
        $user = Auth::user();

        // -------------------------------
        // 1. PARÁMETROS (Ya no depende de laboratorio)
        // -------------------------------
        $parametrosTemp = ParametrosCorrecion::where('tipo', 'temperatura')->first();
        $parametrosHum  = ParametrosCorrecion::where('tipo', 'humedad')->first();

        // -------------------------------
        // 2. BLOQUES HORARIOS
        // -------------------------------
        $bloques = [
            1 => '09:00',
            2 => '11:00',
            3 => '15:00',
        ];

        $hoy = Carbon::today();

        // YA NO SE FILTRA POR laboratorio_id
        $registrosHoy = RegistroTemperatura::whereDate('created_at', $hoy)
            ->orderBy('created_at')
            ->get();

        $estadoBloques = [
            1 => ['temperatura' => false, 'humedad' => false],
            2 => ['temperatura' => false, 'humedad' => false],
            3 => ['temperatura' => false, 'humedad' => false],
        ];

        // -------------------------------
        // Asignación secuencial automática a bloques
        // -------------------------------
        $bloqueActualAsignado = 1;

        foreach ($registrosHoy as $reg) {
            if ($bloqueActualAsignado <= 3) {
                $estadoBloques[$bloqueActualAsignado][$reg->tipo] = true;

                if (
                    $estadoBloques[$bloqueActualAsignado]['temperatura'] &&
                    $estadoBloques[$bloqueActualAsignado]['humedad']
                ) {
                    $bloqueActualAsignado++;
                }
            }
        }

        // -------------------------------
        // Determinar bloque actual y último completado
        // -------------------------------
        $bloqueActual = null;
        $ultimoCompleto = null;

        foreach ($estadoBloques as $num => $datos) {
            if ($datos['temperatura'] && $datos['humedad']) {
                $ultimoCompleto = $num;
            } else {
                $bloqueActual = $num;
                break;
            }
        }

        if (!$bloqueActual && $ultimoCompleto == 3) {
            $bloqueActual = null;
        }
      // Contar bloques completos (un bloque = 2 registros)
$registrosHoy = RegistroTemperatura::where('user_id', Auth::id())
    ->whereDate('created_at', now()->format('Y-m-d'))
    ->count();

$bloquesCompletosHoy = floor($registrosHoy / 2); // número de bloques completos
$limiteAlcanzado = $bloquesCompletosHoy >= 3; // 3 bloques = 6 registros


        return view('dashboardMon', [
                'limiteAlcanzado' => $limiteAlcanzado,
            'user'           => $user,
            'parametrosTemp' => $parametrosTemp,
            'parametrosHum'  => $parametrosHum,
            'estadoBloques'  => $estadoBloques,
            'bloqueActual'   => $bloqueActual,
            'ultimoCompleto' => $ultimoCompleto,
            'bloques'        => $bloques,
        ]);
    }



public function storeAmbos(Request $request)
{
    $request->validate([
        'temperatura_valor' => 'required|numeric',
        'humedad_valor'     => 'required|numeric',
        'hora'              => 'required'
    ]);

    try {
        $user = Auth::user();
         // Contar registros del día actual
    $hoy = now()->format('Y-m-d');
    $registrosHoy = RegistroTemperatura::where('user_id', $user->id)
        ->whereDate('created_at', $hoy)
        ->count();

    if ($registrosHoy >= 6) {
        return back()->with('error', 'Ya alcanzaste el límite de 6 registros para hoy.');
    }

        $paramTemp = ParametrosCorrecion::where('tipo', 'temperatura')->first();
        $paramHum  = ParametrosCorrecion::where('tipo', 'humedad')->first();

        if (!$paramTemp || !$paramHum) {
            return back()->with('error', 'No existen parámetros de corrección.');
        }

        $tempCorregida = $this->corregirValor('temperatura', $request->temperatura_valor, $paramTemp);
        $humCorregida  = $this->corregirValor('humedad', $request->humedad_valor, $paramHum);

        $fechaHora = now()->format('Y-m-d') . ' ' . $request->hora . ':00';

        RegistroTemperatura::create([
            'user_id'        => $user->id,
            'tipo'           => 'temperatura',
            'valor_original' => $request->temperatura_valor,
            'valor_corregido' => $tempCorregida,
            'created_at'     => $fechaHora,
            'updated_at'     => now(),
        ]);

        RegistroTemperatura::create([
            'user_id'        => $user->id,
            'tipo'           => 'humedad',
            'valor_original' => $request->humedad_valor,
            'valor_corregido' => $humCorregida,
            'created_at'     => $fechaHora,
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Registros guardados correctamente.');

    } catch (\Exception $e) {
        dd($e->getMessage(), $e->getTraceAsString());
    }
}



    private function corregirValor($tipo, $valor, $params)
    {
        if (!$params) return $valor;

        // === TEMPERATURA ===
        if ($tipo === 'temperatura') {
            $v1 = $params->valor_1;
            $v2 = $params->valor_2;
            $v3 = $params->valor_3;

            if ($valor <= 21.9) {
                return $valor + ($v1 + ($v2 - $v1) * ($valor - 17.6) / (21.9 - 17.6));
            }

            return $valor + ($v2 + ($v3 - $v2) * ($valor - 21.9) / (25.9 - 21.9));
        }

        // === HUMEDAD ===
        if ($tipo === 'humedad') {
            $v1 = $params->valor_1;
            $v2 = $params->valor_2;
            $v3 = $params->valor_3;

            if ($valor <= 44) {
                return $valor + ($v1 + ($v2 - $v1) * ($valor - 30) / (44 - 30));
            }

            return $valor + ($v2 + ($v3 - $v2) * ($valor - 44) / (60 - 44));
        }

        return $valor;
    }

}
