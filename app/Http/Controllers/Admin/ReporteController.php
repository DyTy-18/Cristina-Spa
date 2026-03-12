<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ComisionesExport;
use App\Models\CitaServicio;
use App\Models\Empleado;
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
        $query = CitaServicio::with(['empleado', 'servicio', 'servicio.comision', 'cita.cliente'])
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

                    $com       = $cs->servicio?->comision;
                    $comActiva = $com && $com->activo;
                    $precio    = (float) ($cs->precio_unitario ?? 0);
                    $pct       = $comActiva ? (float) $com->porcentaje : 0;

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
}
