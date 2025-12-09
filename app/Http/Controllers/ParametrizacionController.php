<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Models\ParametrosCorrecion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParametrizacionController extends Controller
{
    public function index()
    {
        // Muestra todos los parámetros globales
        $parametros = ParametrosCorrecion::all();

        return view('parametros.index', compact('parametros'));
    }

    public function create()
    {
        // Verificar si ya existen parámetros
        $parametros = ParametrosCorrecion::all();

        return view('parametros.create', [
            'tieneTemperatura' => $parametros->where('tipo', 'temperatura')->count() > 0,
            'tieneHumedad'     => $parametros->where('tipo', 'humedad')->count() > 0,
            'parametro'        => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => [
                'required',
                Rule::in(['temperatura', 'humedad']),
                Rule::unique('parametros_correcion', 'tipo'), // ahora global
            ],
            'valor_1' => 'required|numeric',
            'valor_2' => 'required|numeric',
            'valor_3' => 'required|numeric',
        ]);

        ParametrosCorrecion::create($validated);

        return redirect()
            ->route('parametros.index')
            ->with('success', 'Parámetro creado correctamente.');
    }

    public function edit($id)
    {
        $parametro = ParametrosCorrecion::findOrFail($id);

        return view('parametros.edit', compact('parametro'));
    }

    public function update(Request $request, ParametrosCorrecion $parametro)
    {
        $request->validate([
            'tipo' => 'required|string',
            'valor_1' => 'numeric|nullable',
            'valor_2' => 'numeric|nullable',
            'valor_3' => 'numeric|nullable',
        ]);

        $parametro->update($request->all());

        return redirect()->route('parametros.index')
            ->with('success', 'Parámetro actualizado correctamente.');
    }

    public function destroy($id)
    {
        $parametro = ParametrosCorrecion::findOrFail($id);
        $parametro->delete();

        return redirect()->route('parametros.index')
            ->with('success', 'Parámetro eliminado correctamente.');
    }
}
