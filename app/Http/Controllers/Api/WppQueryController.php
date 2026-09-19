<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\WppSyncLog;
use App\Services\WppService;
use Illuminate\Http\Request;

/**
 * Consultas que el servidor WPP hace bajo demanda (o para reconciliación periódica)
 * contra Laravel, que es la fuente de verdad de citas y clientes.
 */
class WppQueryController extends Controller
{
    /**
     * GET /api/wpp/citas?desde=YYYY-MM-DD&hasta=YYYY-MM-DD&telefono=opcional
     *
     * Devuelve todas las citas del rango (con los datos del cliente embebidos),
     * en el mismo formato que usa el push saliente (WppService::payloadCita).
     */
    public function citas(Request $request, WppService $wpp)
    {
        $data = $request->validate([
            'desde'    => 'required|date',
            'hasta'    => 'required|date|after_or_equal:desde',
            'telefono' => 'nullable|string',
        ]);

        $telefono = $data['telefono'] ?? null;
        $digitos  = $telefono ? preg_replace('/[^0-9]/', '', $telefono) : null;

        try {
            $citas = Cita::with(['cliente', 'citaServicios.servicio', 'citaServicios.empleado'])
                ->whereDate('fecha', '>=', $data['desde'])
                ->whereDate('fecha', '<=', $data['hasta'])
                ->when($digitos, fn($q) => $q->whereHas(
                    'cliente',
                    fn($c) => $c->where('telefono', 'like', "%{$digitos}%")
                ))
                ->orderBy('fecha')
                ->orderBy('hora')
                ->get();

            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'consulta',
                'payload'   => $data + ['resultados' => $citas->count()],
                'resultado' => 'ok',
            ]);
        } catch (\Throwable $e) {
            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'consulta',
                'payload'   => $data,
                'resultado' => 'error',
                'mensaje'   => $e->getMessage(),
            ]);

            throw $e;
        }

        return response()->json([
            'citas' => $citas->map(fn(Cita $cita) => $wpp->payloadCita($cita))->values(),
        ]);
    }
}
