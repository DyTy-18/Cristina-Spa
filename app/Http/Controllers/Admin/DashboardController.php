<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Servicio;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard de administración
     */
    public function index()
    {
        // Estadísticas
        $citasHoy = Cita::hoy()->count();
        $totalClientes = Cliente::count();
        $totalServicios = Servicio::activos()->count();
        
        // Ingresos del mes (citas completadas)
        $ingresosMes = Cita::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->where('estado', 'completada')
            ->sum('precio_final');
        
        // Próximas citas
        $proximasCitas = Cita::proximas()
            ->with(['cliente', 'servicio', 'empleado'])
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'citasHoy',
            'totalClientes',
            'totalServicios',
            'ingresosMes',
            'proximasCitas'
        ));
    }
}
