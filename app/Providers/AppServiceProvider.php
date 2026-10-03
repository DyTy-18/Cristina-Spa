<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            if (auth()->check()) {
                $view->with('alertasStockCount', \App\Models\AlertaStock::where('leida', false)->count());
            }
        });

        // Notificaciones de solicitudes de cita (solo el layout, que incluye la campana y el polling)
        View::composer('admin.layouts.app', function ($view) {
            if (auth()->check()) {
                $puedeVerSolicitudes = auth()->user()->hasAnyRole(\App\Models\SolicitudCita::ROLES_GESTION);
                $view->with('puedeVerSolicitudesCita', $puedeVerSolicitudes);
                $view->with('solicitudesPendientesCount', $puedeVerSolicitudes ? \App\Models\SolicitudCita::pendientes()->count() : 0);
            }
        });
    }
}
