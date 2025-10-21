<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('rutaDashboardPorRol')) {
    /**
     * Retorna la ruta de dashboard según el rol del usuario autenticado.
     */
    function rutaDashboardPorRol(): string
    {
        $rol = auth()->user()->role->nombre ?? 'admin';

        return match ($rol) {
            'admin' => route('dashboard.admin'),
            'recepcion' => route('dashboard.recepcion'),
            'analista' => route('dashboard.analista'),
            'gestor_tecnico' => route('dashboard.gestor'),
            'consulta' => route('dashboard.consulta'),
            default => route('dashboard.admin'),
        };
    }
}
