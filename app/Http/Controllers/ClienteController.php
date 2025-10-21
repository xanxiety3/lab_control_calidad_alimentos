<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Municipio;
use App\Models\Persona;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo_persona' => 'required|in:natural,juridica',
                'tipo_documento' => 'required|string|max:20',
                'numero_documento' => 'required|string|max:30|unique:personas,numero_documento',
                'nombre_completo' => 'nullable|required_if:tipo_persona,natural|string|max:255',
                'razon_social' => 'nullable|required_if:tipo_persona,juridica|string|max:255',
                'correo_electronico' => 'nullable|email|max:255',
                'telefono' => 'nullable|string|max:50',
                'departamento_id' => 'nullable|exists:departamentos,id',
                'municipio_id' => 'nullable|exists:municipios,id',
                'direccion' => 'nullable|string|max:255',
                'tipo_cliente' => 'required|in:interno,externo',
            ]);

            $persona = Persona::create([
                'tipo_persona' => $validated['tipo_persona'],
                'tipo_documento' => $validated['tipo_documento'],
                'numero_documento' => $validated['numero_documento'],
                'nombre_completo' => $validated['nombre_completo'] ?? null,
                'razon_social' => $validated['razon_social'] ?? null,
            ]);

            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'departamento_id' => $validated['departamento_id'] ?? null,
                'municipio_id' => $validated['municipio_id'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'correo_electronico' => $validated['correo_electronico'] ?? null,
                'tipo_cliente' => $validated['tipo_cliente'],
            ]);

            return response()->json([
                'id' => $cliente->id,
                'nombre' => $persona->tipo_persona === 'natural'
                    ? $persona->nombre_completo
                    : $persona->razon_social,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function porDepartamento($departamento_id)
    {
        // Traer municipios del departamento
        $municipios = Municipio::where('departamento_id', $departamento_id)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json($municipios);
    }
}
