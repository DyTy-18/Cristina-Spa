<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Inventario
        $totalProductos = Producto::count();
        $productosStockBajo = DB::table('productos as p')
            ->selectRaw('COUNT(*) as total')
            ->leftJoin('entradas as e', 'e.codigo_barras', '=', 'p.codigo_barras')
            ->leftJoin('salidas as s', 's.codigo_barras', '=', 'p.codigo_barras')
            ->groupBy('p.id', 'p.stock_minimo')
            ->havingRaw('(COALESCE(SUM(e.unidades), 0) - COALESCE(SUM(s.unidades), 0)) < p.stock_minimo')
            ->get()
            ->count();

        return view('admin.dashboard', compact(
            'citasHoy',
            'totalClientes',
            'totalServicios',
            'ingresosMes',
            'proximasCitas',
            'totalProductos',
            'productosStockBajo'
        ));
    }
}
