<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\ConsumoMaterial;
use App\Services\MaterialConsumptionService;
use Illuminate\Http\Request;

class MisCitasController extends Controller
{
    public function index()
    {
        $empleado = auth()->user()->empleado;

        if (!$empleado) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Tu usuario no tiene un empleado vinculado.');
        }

        // Citas de hoy
        $citasHoy = $this->agruparCitas(
            CitaServicio::where('empleado_id', $empleado->id)
                ->whereHas('cita', fn($q) => $q->whereDate('fecha', today()))
                ->with('cita.cliente', 'servicio')
                ->get()
        );

        // Próximas citas (mañana en adelante, 14 días, solo confirmadas/pendientes)
        $citasProximas = $this->agruparCitas(
            CitaServicio::where('empleado_id', $empleado->id)
                ->whereHas('cita', fn($q) => $q
                    ->whereDate('fecha', '>', today())
                    ->whereDate('fecha', '<=', today()->addDays(14))
                    ->whereIn('estado', ['confirmada', 'pendiente']))
                ->with('cita.cliente', 'servicio')
                ->get()
        )->sortBy(fn($item) => $item['cita']->fecha->format('Y-m-d') . $item['cita']->hora)->values();

        // Stats rápidos
        $stats = [
            'hoy'       => $citasHoy->count(),
            'proximas'  => $citasProximas->count(),
            'mes'       => CitaServicio::where('empleado_id', $empleado->id)
                ->whereHas('cita', fn($q) => $q
                    ->whereMonth('fecha', now()->month)
                    ->whereYear('fecha', now()->year)
                    ->where('estado', 'completada'))
                ->distinct('cita_id')->count('cita_id'),
        ];

        return view('admin.mis-citas.index', compact('citasHoy', 'citasProximas', 'stats', 'empleado'));
    }

    private function agruparCitas($citaServicios)
    {
        return $citaServicios->groupBy('cita_id')->map(function ($servicios) {
            return [
                'cita'      => $servicios->first()->cita,
                'servicios' => $servicios->pluck('servicio')->filter()->values(),
            ];
        })->values();
    }

    public function consumo(Cita $cita)
    {
        $empleado = auth()->user()->empleado;

        if (!$empleado) {
            return redirect()->route('admin.mis-citas')
                ->with('error', 'Tu usuario no tiene un empleado vinculado.');
        }

        $perteneceAlEmpleado = CitaServicio::where('cita_id', $cita->id)
            ->where('empleado_id', $empleado->id)
            ->exists();

        abort_if(!$perteneceAlEmpleado, 403);
        abort_if($cita->estado !== 'completada', 403, 'Solo se puede registrar consumo de citas completadas.');

        // Servicios del empleado en esta cita con sus materiales
        $citaServicios = CitaServicio::where('cita_id', $cita->id)
            ->where('empleado_id', $empleado->id)
            ->with('servicio.materiales.producto')
            ->get();

        // Consumos ya registrados indexados por servicio_material_id
        $consumosExistentes = ConsumoMaterial::where('cita_id', $cita->id)
            ->get()
            ->groupBy('servicio_material_id');

        $cita->load('cliente');

        return view('admin.mis-citas.consumo', compact('cita', 'citaServicios', 'consumosExistentes'));
    }

    public function guardarConsumo(Request $request, Cita $cita)
    {
        $empleado = auth()->user()->empleado;

        if (!$empleado) {
            return redirect()->route('admin.mis-citas')
                ->with('error', 'Tu usuario no tiene un empleado vinculado.');
        }

        $perteneceAlEmpleado = CitaServicio::where('cita_id', $cita->id)
            ->where('empleado_id', $empleado->id)
            ->exists();

        abort_if(!$perteneceAlEmpleado, 403);
        abort_if($cita->estado !== 'completada', 403);

        $request->validate([
            'materiales'                 => 'required|array',
            'materiales.*.consumo_id'    => 'required|integer|exists:consumos_material,id',
            'materiales.*.usos_reales'   => 'required|integer|min:1',
        ]);

        $service = app(MaterialConsumptionService::class);

        foreach ($request->materiales as $item) {
            $consumo = ConsumoMaterial::findOrFail($item['consumo_id']);
            $usosNuevos = (int) $item['usos_reales'];

            // Calcular cuántos usos extra se agregan respecto a lo ya reportado
            $usosYaReportados = $consumo->usos_reales ?? 1;
            $usosExtra = $usosNuevos - $usosYaReportados;

            $consumo->update(['usos_reales' => $usosNuevos]);

            if ($usosExtra > 0) {
                $service->procesarUsosExtra($consumo, $usosExtra);
            }
        }

        return redirect()->route('admin.mis-citas')
            ->with('success', 'Consumo registrado correctamente.');
    }
}
