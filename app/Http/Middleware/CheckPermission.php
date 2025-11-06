<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Verifica si el usuario tiene el permiso requerido.
     */
    public function handle(Request $request, Closure $next, string $permisos): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Acceso denegado');
        }

        // Si el usuario es administrador, acceso total
        if ($user->role && $user->role->nombre === 'admin') {
            return $next($request);
        }   

        // ✅ Permitir múltiples permisos separados por coma
        $listaPermisos = array_map('trim', explode(',', $permisos));

        // ✅ Comprobar si el usuario o su rol tiene alguno de los permisos
        $tienePermiso = false;
        foreach ($listaPermisos as $permiso) {
            if (
                $user->permissions()->where('nombre', $permiso)->exists() ||
                $user->role->permissions()->where('nombre', $permiso)->exists()
            ) {
                $tienePermiso = true;
                break;
            }
        }Log::info('🔐 CheckPermission', [
    'usuario' => $user->email,
    'rol' => $user->role->nombre ?? 'Sin rol',
    'permisos_usuario' => $user->permissions->pluck('nombre'),
    'permisos_rol' => $user->role?->permissions->pluck('nombre'),
    'permisos_requeridos' => $listaPermisos,
]);


        if (!$tienePermiso) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
