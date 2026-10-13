<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\PagoSueldo;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\WppSyncLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard de administración
     */
    public function index()
    {
        if (auth()->user()->hasRole('cristina')) {
            return $this->resumenCristina();
        }

        $sid    = session('sucursal_activa_id');

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
        // Cada sucursal tiene su propio catálogo; en modo global se suman todos.
        $totalProductos     = Producto::deSucursal($sid)->count();
        $productosStockBajo = Producto::deSucursal($sid)
            ->whereRaw('(CASE WHEN productos.es_reventa THEN (' . Producto::stockSql('reventa') . ') ELSE (' . Producto::stockSql('tecnico') . ') END) < productos.stock_minimo')
            ->count();

        $wppLogsRecientes = WppSyncLog::with('cita.cliente')->latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'citasHoy',
            'totalClientes',
            'totalServicios',
            'ingresosMes',
            'proximasCitas',
            'totalProductos',
            'productosStockBajo',
            'wppLogsRecientes'
        ));
    }

    private function resumenCristina()
    {
        $sid    = session('sucursal_activa_id');

        // ── Citas ─────────────────────────────────────────────────────────────
        $citasHoy = Cita::hoy()
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->count();

        $citasMes = Cita::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->count();

        $citasPendientes = Cita::where('estado', 'pendiente')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->count();

        $citasCompletadasMes = Cita::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->where('estado', 'completada')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->count();

        // ── Finanzas ──────────────────────────────────────────────────────────
        $ingresosMes = (float) Cita::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->where('estado', 'completada')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->sum('precio_final');

        $gastosMes = (float) Gasto::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->sum('monto');

        $pagosMes = (float) PagoSueldo::whereMonth('fecha_pago', now()->month)
            ->whereYear('fecha_pago', now()->year)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->sum('monto');

        $netoMes = $ingresosMes - $gastosMes - $pagosMes;

        // ── Clientes ──────────────────────────────────────────────────────────
        $totalClientes = Cliente::when($sid, fn($q) => $q->where(function ($q2) use ($sid) {
            $q2->where('sucursal_id', $sid)
               ->orWhereHas('citas', fn($q3) => $q3->where('sucursal_id', $sid));
        }))->count();

        $nuevosMes = Cliente::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ── Inventario ────────────────────────────────────────────────────────
        // Cada sucursal tiene su propio catálogo; en modo global se suman todos.
        $totalProductos     = Producto::deSucursal($sid)->count();
        $productosStockBajo = Producto::deSucursal($sid)
            ->whereRaw('(CASE WHEN productos.es_reventa THEN (' . Producto::stockSql('reventa') . ') ELSE (' . Producto::stockSql('tecnico') . ') END) < productos.stock_minimo')
            ->count();

        $wppLogsRecientes = WppSyncLog::with('cita.cliente')->latest()->take(10)->get();

        return view('admin.resumen', compact(
            'citasHoy', 'citasMes', 'citasPendientes', 'citasCompletadasMes',
            'ingresosMes', 'gastosMes', 'pagosMes', 'netoMes',
            'totalClientes', 'nuevosMes',
            'totalProductos', 'productosStockBajo',
            'wppLogsRecientes'
        ));
    }
}
