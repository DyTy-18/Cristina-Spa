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

        // ONE consumo per (cita, servicio_material) — keyed by servicio_material_id
        $consumosExistentes = ConsumoMaterial::where('cita_id', $cita->id)
            ->get()
            ->keyBy('servicio_material_id');

        $cita->load('cliente');

        // Stock actual y usos restantes por material_id para mostrar en vista
        $service = app(MaterialConsumptionService::class);
        $stockPorProducto  = [];
        $usosRestantesPorMaterial = [];

        foreach ($citaServicios as $cs) {
            foreach ($cs->servicio?->materiales ?? [] as $material) {
                if ($material->producto && !isset($stockPorProducto[$material->producto_id])) {
                    $stockPorProducto[$material->producto_id] = $service->calcularStock(
                        $material->producto->codigo_barras
                    );
                }
                $usosRestantesPorMaterial[$material->id] = $service->calcularUsosRestantesEnUnidad($material);
            }
        }

        return view('admin.mis-citas.consumo', compact(
            'cita', 'citaServicios', 'consumosExistentes', 'stockPorProducto', 'usosRestantesPorMaterial'
        ));
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
            'materiales.*.usos_reales'   => 'required|integer|min:0',
        ]);

        $service = app(MaterialConsumptionService::class);

        // ── Paso 1: validar stock solo cuando hay incremento ──────────────────
        $erroresStock = [];
        $items = [];

        foreach ($request->materiales as $item) {
            $consumo    = ConsumoMaterial::with('servicioMaterial.producto')->findOrFail($item['consumo_id']);
            $usosNuevos = (int) $item['usos_reales'];
            $usosViejos = $consumo->usos_reales ?? 1;

            if ($usosNuevos > $usosViejos) {
                $material     = $consumo->servicioMaterial;
                $salidasExtra = $service->calcularSalidasExtra($material, $usosViejos, $usosNuevos);

                if ($salidasExtra > 0) {
                    $stock = $service->calcularStock($material->producto->codigo_barras);

                    if ($stock < $salidasExtra) {
                        $nombre = $material->producto->nombre;
                        $erroresStock[] = "Stock insuficiente para <strong>{$nombre}</strong>: "
                            . "disponible {$stock} unid., se necesitan {$salidasExtra}.";
                        continue;
                    }
                }
            }
            // Bajar a 0 = no se usó el material (reversa automática si había salidas)

            $items[] = ['consumo' => $consumo, 'usosNuevos' => $usosNuevos];
        }

        if (!empty($erroresStock)) {
            return back()->with('stock_errors', $erroresStock);
        }

        // ── Paso 2: aplicar cambios ───────────────────────────────────────────
        foreach ($items as $item) {
            $service->actualizarUsos($item['consumo'], $item['usosNuevos']);
        }

        return redirect()->route('admin.mis-citas')
            ->with('success', 'Consumo registrado correctamente.');
    }
}
