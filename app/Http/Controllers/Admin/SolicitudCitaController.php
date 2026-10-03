<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCita;
use Illuminate\Http\Request;

class SolicitudCitaController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->get('estado', 'pendiente');

        $query = SolicitudCita::with(['atendidoPor', 'servicio:id,nombre'])->orderByDesc('created_at');

        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        $solicitudes = $query->paginate(50)->withQueryString();
        $totales     = SolicitudCita::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        $firma = SolicitudCita::firma();

        return view('admin.solicitudes-cita.index', compact('solicitudes', 'estado', 'totales', 'firma'));
    }

    public function updateEstado(Request $request, SolicitudCita $solicitud)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', SolicitudCita::ESTADOS),
        ]);

        $solicitud->update([
            'estado'       => $request->estado,
            'atendido_por' => $request->estado === 'pendiente' ? null : auth()->id(),
            'atendido_at'  => $request->estado === 'pendiente' ? null : now(),
        ]);

        return back()->with('success', "Solicitud de {$solicitud->nombre} marcada como {$request->estado}.");
    }

    /**
     * Endpoint de polling: devuelve el total de pendientes y las solicitudes
     * con id mayor a `desde`. Sin `desde` solo devuelve el último id (línea base).
     */
    public function notificaciones(Request $request)
    {
        $desde = $request->integer('desde');

        $nuevas = $desde > 0
            ? SolicitudCita::pendientes()
                ->with('servicio:id,nombre')
                ->where('id', '>', $desde)
                ->orderBy('id')
                ->limit(10)
                ->get(['id', 'nombre', 'telefono', 'servicio_id', 'consulta', 'fecha_preferida', 'hora_preferida', 'created_at'])
                ->map(fn ($s) => [
                    'id'       => $s->id,
                    'nombre'   => $s->nombre,
                    'telefono' => $s->telefono,
                    'servicio' => $s->servicio?->nombre,
                    'consulta' => \Illuminate\Support\Str::limit($s->consulta, 90),
                    'fecha'    => $s->fecha_preferida_texto,
                    'hace'     => $s->created_at->locale('es')->diffForHumans(),
                ])
            : collect();

        return response()->json([
            'pendientes' => SolicitudCita::pendientes()->count(),
            'ultimo_id'  => (int) SolicitudCita::max('id'),
            'firma'      => SolicitudCita::firma(),
            'nuevas'     => $nuevas,
        ]);
    }
}
