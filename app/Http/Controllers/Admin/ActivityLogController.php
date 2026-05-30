<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $logs = $query->paginate(50)->withQueryString();

        $logNames = Activity::distinct()->orderBy('log_name')->pluck('log_name');
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.logs.index', compact('logs', 'logNames', 'usuarios'));
    }

    public function exportPdf(Request $request)
    {
        $logs = $this->buildQuery($request)->get();

        $logNames = Activity::distinct()->orderBy('log_name')->pluck('log_name');
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        $filtros = [
            'log_name'    => $request->log_name,
            'event'       => $request->event,
            'causer_id'   => $request->causer_id,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
        ];

        $usuarioNombre = null;
        if ($request->filled('causer_id')) {
            $usuarioNombre = $usuarios->firstWhere('id', $request->causer_id)?->name;
        }

        $pdf = Pdf::loadView('admin.logs.reporte-pdf', compact('logs', 'filtros', 'usuarioNombre'))
            ->setPaper('a4', 'landscape');

        $filename = 'reporte-actividad-' . now()->format('Y-m-d_H-i') . '.pdf';

        return $pdf->download($filename);
    }

    private function buildQuery(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id)
                  ->where('causer_type', \App\Models\User::class);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        return $query;
    }
}
