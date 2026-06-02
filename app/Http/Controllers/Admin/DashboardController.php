<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard de administración
     */
    public function index()
    {
        $sid    = session('sucursal_activa_id');
        $sidSql = $sid ? " AND sucursal_id = {$sid}" : '';

        $citasHoy = Cita::hoy()
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->count();

        // Clientes: registrados en esta sucursal O con citas aquí
        $totalClientes = Cliente::when($sid, fn($q) => $q->where(function ($q2) use ($sid) {
            $q2->where('sucursal_id', $sid)
               ->orWhereHas('citas', fn($q3) => $q3->where('sucursal_id', $sid));
        }))->count();

        $totalServicios = Servicio::activos()->count();

        $ingresosMes = Cita::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->where('estado', 'completada')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->sum('precio_final');

        $proximasCitas = Cita::proximas()
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->with(['cliente', 'citaServicios.servicio'])
            ->take(5)
            ->get();

        // Productos: solo los con movimientos en esta sucursal
        if ($sid) {
            $codigosConActividad = DB::table('entradas')->where('sucursal_id', $sid)->pluck('codigo_barras')
                ->merge(DB::table('salidas')->where('sucursal_id', $sid)->pluck('codigo_barras'))
                ->unique()->values()->toArray();

            $totalProductos     = Producto::whereIn('codigo_barras', $codigosConActividad)->count();
            $productosStockBajo = empty($codigosConActividad) ? 0 : DB::table('productos as p')
                ->selectRaw('COUNT(*) as total')
                ->whereIn('p.codigo_barras', $codigosConActividad)
                ->whereRaw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras{$sidSql})
                          - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras{$sidSql})
                          < p.stock_minimo")
                ->get()->count();
        } else {
            $totalProductos     = Producto::count();
            $productosStockBajo = DB::table('productos as p')
                ->selectRaw('COUNT(*) as total')
                ->whereRaw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras)
                          - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras)
                          < p.stock_minimo")
                ->get()->count();
        }

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
