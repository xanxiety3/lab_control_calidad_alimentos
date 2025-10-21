<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Verifica si el usuario tiene el permiso requerido.
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Acceso denegado');
        }

        // Si el usuario es administrador, acceso total
        if ($user->role && $user->role->nombre === 'admin') {
            return $next($request);
        }

        // Verificar permisos asignados al usuario o al rol
        $tienePermiso = $user->permissions()->where('nombre', $permiso)->exists() ||
            $user->role->permissions()->where('nombre', $permiso)->exists();

        if (!$tienePermiso) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
