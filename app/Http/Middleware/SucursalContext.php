<?php

namespace App\Http\Middleware;

use App\Models\Sucursal;
use Closure;
use Illuminate\Http\Request;

class SucursalContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->hasRole(['admin', 'cristina'])) {
            // Admin/Cristina: por defecto ve TODAS las sucursales (null = global), puede cambiar
            if (!session()->has('sucursal_activa_id')) {
                session(['sucursal_activa_id' => null]);
            }
        } elseif ($user->hasRole('encargado')) {
            // Encargado: fijado a su sucursal
            session(['sucursal_activa_id' => $user->sucursal_id]);
        } else {
            // Otros roles: su sucursal fija, fallback a la principal
            $sucursalId = $user->sucursal_id
                ?? Sucursal::where('es_principal', true)->value('id')
                ?? Sucursal::where('activo', true)->value('id');
            session(['sucursal_activa_id' => $sucursalId]);
        }

        $sid            = session('sucursal_activa_id');
        $sucursalActiva = $sid ? Sucursal::find($sid) : null;
        $sucursales     = Sucursal::where('activo', true)
                            ->orderBy('es_principal', 'desc')
                            ->orderBy('nombre')
                            ->get();

        view()->share('sucursalActiva', $sucursalActiva);
        view()->share('sucursales', $sucursales);
        view()->share('modoGlobal', is_null($sid));

        return $next($request);
    }
}
