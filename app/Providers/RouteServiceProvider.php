<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

        });
    }

    // 👇 Esta es la función que agregamos
    public static function redirectToRole($role)
    {
        return match ($role) {
            'admin' => '/dashboard/admin',
            'recepcion' => '/dashboard/recepcion',
            'analista' => '/dashboard/analista',
            'gestor_tecnico' => '/dashboard/gestor',
            'consulta' => '/dashboard/consulta',
            default => '/dashboard',
        };
    }
}
