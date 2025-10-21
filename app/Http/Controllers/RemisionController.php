<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Ensayo;
use App\Models\Muestra;
use App\Models\Solicitud;
use App\Models\TipoMuestra;
use Illuminate\Http\Request;

class RemisionController extends Controller
{
    public function create()
    {
        $clientes = Cliente::with('persona')->get();
        $ensayos = Ensayo::where('activo', true)->get();
        $departamentos = Departamento::all();
        $tipoMuestras = TipoMuestra::all();
        $codigoSolicitud = '25-048'; // ejemplo dinámico
        return view('remisiones.create', compact('clientes', 'ensayos', 'departamentos', 'tipoMuestras', 'codigoSolicitud'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'entrega_resultados' => 'required|in:correo,personal,ambos',
            'muestras' => 'required|array|min:1',
            'muestras.*.tipo_muestra_id' => 'required|exists:tipo_muestras,id',
            'muestras.*.cantidad' => 'required',
            'muestras.*.ensayos' => 'required|array|min:1',
        ]);

        // --- Crear solicitud automáticamente ---
        $anio = date('y');
        $ultimo = Solicitud::where('numero_solicitud', 'like', "$anio-%")
            ->orderByDesc('id')
            ->value('numero_solicitud');

        $nuevoNumero = $ultimo
            ? str_pad((int) substr($ultimo, 3) + 1, 3, '0', STR_PAD_LEFT)
            : '001';

        $numeroSolicitud = "{$anio}-{$nuevoNumero}";

        $solicitud = Solicitud::create([
            'numero_solicitud' => $numeroSolicitud,
            'cliente_id' => $request->cliente_id,
            'fecha_solicitud' => now(),
            'entrega_resultados' => $request->entrega_resultados,
            'declaracion_conformidad' => false,
            'aprobada' => true,
            'observaciones' => $request->observaciones,
        ]);

        // --- Crear muestras asociadas ---
        $letras = range('A', 'Z');
        foreach ($request->muestras as $i => $muestraData) {
            $codigoInterno = $letras[$i] . '-' . substr($numeroSolicitud, 3);

            $muestra = Muestra::create([
                'solicitud_id' => $solicitud->id,
                'codigo_cliente' => $muestraData['codigo_cliente'] ?? null,
                'codigo_interno' => $codigoInterno,
                'tipo_muestra_id' => $muestraData['tipo_muestra_id'],
                'cantidad' => $muestraData['cantidad'],
                'condiciones' => $muestraData['condiciones'] ?? null,
                'estado' => 'pendiente',
            ]);

            $muestra->ensayos()->sync($muestraData['ensayos']);
        }

        return redirect()->route('dashboard.recepcion')->with('success', 'Remisión creada correctamente.');
    }
}
