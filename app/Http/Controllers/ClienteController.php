<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Municipio;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{

    public function index()
    {
        // Traer todos los clientes con su información principal
        $clientes = Cliente::with('persona')->get();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $departamentos = DB::table('departamentos')->orderBy('nombre')->get();
        return view('clientes.create', compact('departamentos'));
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'tipo_persona' => 'required|in:natural,juridica',
        'tipo_documento' => 'required|string|max:20',
        'numero_documento' => 'required|string|max:30|unique:personas,numero_documento',
        'nombre_completo' => 'nullable|required_if:tipo_persona,natural|string|max:255',
        'razon_social' => 'nullable|required_if:tipo_persona,juridica|string|max:255',
        'correo_electronico' => 'required|email|unique:clientes,correo_electronico|max:255',
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

    // Redirige a donde quieras, por ejemplo al listado de clientes
    return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
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

    public function edit($id)
    {
        $cliente = Cliente::with('persona')->findOrFail($id);
        $departamentos = DB::table('departamentos')->orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente', 'departamentos'));
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::with('persona')->findOrFail($id);

        // 🔍 Validación dinámica según tipo de persona
        $rules = [
            'tipo_persona' => 'sometimes|in:natural,juridica',
            'tipo_cliente' => 'sometimes|in:interno,externo',
            'tipo_documento' => 'sometimes|string|max:20',
            'numero_documento' => 'sometimes|string|max:20|unique:personas,numero_documento,' . $cliente->persona->id,
            'telefono' => 'sometimes|string|max:20',
            'correo_electronico' => 'sometimes|email|max:100',
            'departamento_id' => 'sometimes|integer',
            'municipio_id' => 'sometimes|integer',
            'direccion' => 'sometimes|string|max:20',
        ];

        if ($request->tipo_persona === 'natural') {
            $rules['nombre_completo'] = 'sometimes|string|max:100';
        } else {
            $rules['razon_social'] = 'sometimes|string|max:150';
        }

        $validated = $request->validate($rules);

        // 🧠 Actualizar la persona asociada
        $cliente->persona->update([
            'tipo_persona' => $validated['tipo_persona'],
            'tipo_documento' => $validated['tipo_documento'],
            'numero_documento' => $validated['numero_documento'],
            'nombre_completo' => $validated['nombre_completo'] ?? null,
            'razon_social' => $validated['razon_social'] ?? null,
        ]);

        // 🧾 Si hay campos propios del cliente (no de persona), actualízalos aquí
        $cliente->update([
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'correo_electronico' => $validated['correo_electronico'] ?? null,
            'tipo_cliente' => $validated['tipo_cliente'],
            'departamento_id' => $validated['departamento_id'] ?? null,
            'municipio_id' => $validated['municipio_id'] ?? null,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }
}
