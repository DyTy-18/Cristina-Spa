<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\CitasExport;
use App\Exports\ClientesExport;
use App\Exports\ComisionesExport;
use App\Exports\FinanzasExport;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Gasto;
use App\Models\PagoSueldo;
use App\Models\Servicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    // ──────────────────────────────────────────
    //  Lógica compartida de cálculo
    // ──────────────────────────────────────────
    private function calcular(string $desde, string $hasta, ?int $empleadoId = null): array
    {
        $query = CitaServicio::with(['empleado', 'servicio', 'servicio.comision', 'servicio.comisionesEmpleado', 'cita.cliente'])
            ->whereHas('cita', fn($q) =>
                $q->whereBetween('fecha', [$desde, $hasta])
                  ->where('estado', 'completada')
            )
            ->whereNotNull('empleado_id');

        if ($empleadoId) {
            $query->where('empleado_id', $empleadoId);
        }

        $lineas = $query->get()->sortBy(fn($cs) => $cs->cita?->fecha);

        // Mapa cita_id+servicio_id → cuántos empleados comparten ese servicio
        // (necesario para dividir la comisión cuando hay co-participantes)
        $participantesPorLinea = $lineas
            ->groupBy(fn($cs) => $cs->cita_id . '-' . $cs->servicio_id)
            ->map(fn($g) => $g->count());

        $porEmpleado = $lineas
            ->groupBy('empleado_id')
            ->map(function ($items) use ($participantesPorLinea) {
                $empleado = $items->first()->empleado;

                $filas = $items->map(function ($cs) use ($participantesPorLinea) {
                    $key           = $cs->cita_id . '-' . $cs->servicio_id;
                    $participantes = $participantesPorLinea[$key] ?? 1;

                    // Prioridad: comisión personal del empleado → comisión base del servicio
                    $comPersonal = $cs->servicio?->comisionesEmpleado
                        ->firstWhere('empleado_id', $cs->empleado_id);
                    $com        = $comPersonal ?? $cs->servicio?->comision;
                    $comActiva  = $com && $com->activo;
                    $precio     = (float) ($cs->precio_unitario ?? 0);
                    $pct        = $comActiva ? (float) $com->porcentaje : 0;

                    // Dividir la comisión entre todos los co-participantes
                    $monto = $comActiva
                        ? round(($precio * $pct / 100) / $participantes, 2)
                        : 0;

                    return [
                        'fecha'          => $cs->cita?->fecha,
                        'cliente'        => $cs->cita?->cliente?->nombre_completo,
                        'servicio'       => $cs->servicio?->nombre ?? '—',
                        'precio'         => $precio,
                        'pct'            => $pct,
                        'monto_comision' => $monto,
                        'participantes'  => $participantes,
                        'com_estado'     => $com
                            ? ($com->activo ? 'activa' : 'inactiva')
                            : 'sin_config',
                        'com_fuente'     => $comPersonal ? 'personal' : 'servicio',
                    ];
                })->sortBy('fecha');

                return [
                    'empleado'       => $empleado,
                    'filas'          => $filas,
                    'total_precio'   => $filas->sum('precio'),
                    'total_comision' => $filas->sum('monto_comision'),
                ];
            })
            ->sortBy(fn($e) => $e['empleado']?->apellido);

        return [
            'porEmpleado'    => $porEmpleado,
            'totalGlobal'    => $porEmpleado->sum('total_precio'),
            'comisionGlobal' => $porEmpleado->sum('total_comision'),
        ];
    }

    // ──────────────────────────────────────────
    //  Vista HTML
    // ──────────────────────────────────────────
    public function comisiones(Request $request)
    {
        $desde      = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta      = $request->get('hasta', now()->format('Y-m-d'));
        $empleadoId = $request->get('empleado_id') ?: null;

        $empleados = Empleado::orderBy('apellido')->get(['id', 'nombre', 'apellido']);

        $data = $this->calcular($desde, $hasta, $empleadoId);

        return view('admin.reportes.comisiones', array_merge($data, compact('desde', 'hasta', 'empleados', 'empleadoId')));
    }

    // ──────────────────────────────────────────
    //  Descarga PDF
    // ──────────────────────────────────────────
    public function comisionesPdf(Request $request)
    {
        $desde      = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta      = $request->get('hasta', now()->format('Y-m-d'));
        $empleadoId = $request->get('empleado_id') ?: null;

        $data = $this->calcular($desde, $hasta, $empleadoId);

        $pdf = Pdf::loadView('admin.reportes.comisiones-pdf', array_merge($data, compact('desde', 'hasta')))
            ->setPaper('a4', 'portrait');

        return $pdf->download('comisiones-' . $desde . '-a-' . $hasta . '.pdf');
    }

    // ──────────────────────────────────────────
    //  Descarga Excel
    // ──────────────────────────────────────────
    public function comisionesExcel(Request $request)
    {
        $desde      = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta      = $request->get('hasta', now()->format('Y-m-d'));
        $empleadoId = $request->get('empleado_id') ?: null;

        $data = $this->calcular($desde, $hasta, $empleadoId);

        return Excel::download(
            new ComisionesExport($data['porEmpleado'], $desde, $hasta),
            'comisiones-' . $desde . '-a-' . $hasta . '.xlsx'
        );
    }

    // ══════════════════════════════════════════
    //  REPORTE DE CLIENTES
    // ══════════════════════════════════════════

    private function calcularClientesReporte(
        ?string $desde,
        ?string $hasta,
        array   $servicioIds,
        ?string $conTelefono,
        ?string $conEmail,
        ?string $conFechaNac
    ): array {
        $clientes = Cliente::where('oculto', false)
            ->when($conTelefono === 'si', fn($q) => $q->whereNotNull('telefono')->where('telefono', '!=', ''))
            ->when($conTelefono === 'no', fn($q) => $q->where(fn($q2) => $q2->whereNull('telefono')->orWhere('telefono', '')))
            ->when($conEmail === 'si', fn($q) => $q->whereNotNull('email')->where('email', '!=', ''))
            ->when($conEmail === 'no', fn($q) => $q->where(fn($q2) => $q2->whereNull('email')->orWhere('email', '')))
            ->when($conFechaNac === 'si', fn($q) => $q->whereNotNull('fecha_nacimiento'))
            ->when($conFechaNac === 'no', fn($q) => $q->whereNull('fecha_nacimiento'))
            ->when($desde || $hasta || $servicioIds, fn($q) =>
                $q->whereHas('citas', fn($q2) => $q2
                    ->where('estado', 'completada')
                    ->when($desde, fn($q3) => $q3->where('fecha', '>=', $desde))
                    ->when($hasta, fn($q3) => $q3->where('fecha', '<=', $hasta))
                    ->when($servicioIds, fn($q3) => $q3->whereHas('citaServicios', fn($q4) => $q4->whereIn('servicio_id', $servicioIds)))
                )
            )
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $clienteIds = $clientes->pluck('id');

        $citas = Cita::whereIn('cliente_id', $clienteIds)
            ->where('estado', 'completada')
            ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
            ->when($servicioIds, fn($q) => $q->whereHas('citaServicios', fn($q2) => $q2->whereIn('servicio_id', $servicioIds)))
            ->with(['citaServicios.servicio'])
            ->get();

        $citasPorCliente = $citas->groupBy('cliente_id');

        $filas = $clientes->map(function ($cliente) use ($citasPorCliente) {
            $citasCliente = $citasPorCliente->get($cliente->id, collect());
            $serviciosUsados = $citasCliente
                ->flatMap(fn($c) => $c->citaServicios->map(fn($cs) => $cs->servicio?->nombre))
                ->filter()->unique()->sort()->values();

            return [
                'cliente'        => $cliente,
                'visitas'        => $citasCliente->count(),
                'total_gastado'  => (float) $citasCliente->sum('precio_final'),
                'primera_visita' => $citasCliente->min('fecha'),
                'ultima_visita'  => $citasCliente->max('fecha'),
                'servicios'      => $serviciosUsados,
            ];
        });

        return [
            'filas'   => $filas,
            'totales' => [
                'clientes'      => $clientes->count(),
                'con_telefono'  => $clientes->filter(fn($c) => !empty($c->telefono))->count(),
                'con_email'     => $clientes->filter(fn($c) => !empty($c->email))->count(),
                'con_fecha_nac' => $clientes->filter(fn($c) => $c->fecha_nacimiento)->count(),
                'total_gastado' => $filas->sum('total_gastado'),
                'total_visitas' => $filas->sum('visitas'),
            ],
        ];
    }

    public function clientes(Request $request)
    {
        $desde       = $request->get('desde') ?: null;
        $hasta       = $request->get('hasta') ?: null;
        $servicioIds = array_values(array_filter(array_map('intval', (array) $request->get('servicios', []))));
        $conTelefono = $request->get('con_telefono') ?: null;
        $conEmail    = $request->get('con_email') ?: null;
        $conFechaNac = $request->get('con_fecha_nacimiento') ?: null;

        $servicios = Servicio::orderBy('nombre')->get(['id', 'nombre']);
        $data      = $this->calcularClientesReporte($desde, $hasta, $servicioIds, $conTelefono, $conEmail, $conFechaNac);

        return view('admin.reportes.clientes', array_merge($data, compact(
            'desde', 'hasta', 'servicioIds', 'servicios', 'conTelefono', 'conEmail', 'conFechaNac'
        )));
    }

    public function clientesPdf(Request $request)
    {
        $desde       = $request->get('desde') ?: null;
        $hasta       = $request->get('hasta') ?: null;
        $servicioIds = array_values(array_filter(array_map('intval', (array) $request->get('servicios', []))));
        $conTelefono = $request->get('con_telefono') ?: null;
        $conEmail    = $request->get('con_email') ?: null;
        $conFechaNac = $request->get('con_fecha_nacimiento') ?: null;

        $data = $this->calcularClientesReporte($desde, $hasta, $servicioIds, $conTelefono, $conEmail, $conFechaNac);

        $pdf = Pdf::loadView('admin.reportes.clientes-pdf', array_merge($data, compact('desde', 'hasta')))
            ->setPaper('a4', 'landscape');

        $nombre = 'reporte-clientes' . ($desde ? '-' . $desde . '-a-' . $hasta : '') . '.pdf';

        return $pdf->download($nombre);
    }

    public function clientesExcel(Request $request)
    {
        $desde       = $request->get('desde') ?: null;
        $hasta       = $request->get('hasta') ?: null;
        $servicioIds = array_values(array_filter(array_map('intval', (array) $request->get('servicios', []))));
        $conTelefono = $request->get('con_telefono') ?: null;
        $conEmail    = $request->get('con_email') ?: null;
        $conFechaNac = $request->get('con_fecha_nacimiento') ?: null;

        $data = $this->calcularClientesReporte($desde, $hasta, $servicioIds, $conTelefono, $conEmail, $conFechaNac);

        $nombre = 'reporte-clientes' . ($desde ? '-' . $desde . '-a-' . $hasta : '') . '.xlsx';

        return Excel::download(new ClientesExport($data['filas'], $desde, $hasta), $nombre);
    }

    // ══════════════════════════════════════════
    //  ÍNDICE DE REPORTES
    // ══════════════════════════════════════════

    public function index()
    {
        return view('admin.reportes.index');
    }

    // ══════════════════════════════════════════
    //  REPORTE DE CITAS
    // ══════════════════════════════════════════

    private function calcularCitas(?string $desde, ?string $hasta): array
    {
        $sid = session('sucursal_activa_id');

        $citas = Cita::with(['cliente', 'citaServicios.servicio', 'sucursal'])
            ->where('estado', 'completada')
            ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return [
            'citas'         => $citas,
            'totalIngresos' => (float) $citas->sum('precio_final'),
            'totalCitas'    => $citas->count(),
        ];
    }

    public function citas(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));
        $data  = $this->calcularCitas($desde, $hasta);

        return view('admin.reportes.citas', array_merge($data, compact('desde', 'hasta')));
    }

    public function citasPdf(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));
        $data  = $this->calcularCitas($desde, $hasta);

        $pdf = Pdf::loadView('admin.reportes.citas-pdf', array_merge($data, compact('desde', 'hasta')))
            ->setPaper('a4', 'landscape');

        return $pdf->download('reporte-citas-' . $desde . '-a-' . $hasta . '.pdf');
    }

    public function citasExcel(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));
        $data  = $this->calcularCitas($desde, $hasta);

        return Excel::download(
            new CitasExport($data['citas'], $desde, $hasta),
            'reporte-citas-' . $desde . '-a-' . $hasta . '.xlsx'
        );
    }

    // ══════════════════════════════════════════
    //  REPORTE DE FINANZAS (Ingresos vs Gastos)
    // ══════════════════════════════════════════

    private function calcularFinanzas(?string $desde, ?string $hasta): array
    {
        $sid = session('sucursal_activa_id');

        $ingresosCitas = (float) Cita::where('estado', 'completada')
            ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->sum('precio_final');

        $gastos = Gasto::when($desde, fn($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->orderBy('fecha')
            ->get();

        $pagos = PagoSueldo::with('empleado')
            ->when($desde, fn($q) => $q->where('fecha_pago', '>=', $desde))
            ->when($hasta, fn($q) => $q->where('fecha_pago', '<=', $hasta))
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->orderBy('fecha_pago')
            ->get();

        $totalGastos = (float) $gastos->sum('monto');
        $totalPagos  = (float) $pagos->sum('monto');
        $neto        = $ingresosCitas - $totalGastos - $totalPagos;

        return compact('ingresosCitas', 'gastos', 'pagos', 'totalGastos', 'totalPagos', 'neto');
    }

    public function finanzas(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));
        $data  = $this->calcularFinanzas($desde, $hasta);

        return view('admin.reportes.finanzas', array_merge($data, compact('desde', 'hasta')));
    }

    public function finanzasPdf(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));
        $data  = $this->calcularFinanzas($desde, $hasta);

        $pdf = Pdf::loadView('admin.reportes.finanzas-pdf', array_merge($data, compact('desde', 'hasta')))
            ->setPaper('a4', 'portrait');

        return $pdf->download('reporte-finanzas-' . $desde . '-a-' . $hasta . '.pdf');
    }

    public function finanzasExcel(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));
        $data  = $this->calcularFinanzas($desde, $hasta);

        return Excel::download(
            new FinanzasExport($data, $desde, $hasta),
            'reporte-finanzas-' . $desde . '-a-' . $hasta . '.xlsx'
        );
    }
}
