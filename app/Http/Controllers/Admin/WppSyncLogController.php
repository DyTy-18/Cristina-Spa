<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WppSyncLog;
use Illuminate\Http\Request;

class WppSyncLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WppSyncLog::with('cita.cliente')->latest();

        if ($request->filled('direccion')) {
            $query->where('direccion', $request->direccion);
        }

        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $logs = $query->paginate(50)->withQueryString();

        $tipos = WppSyncLog::distinct()->orderBy('tipo')->pluck('tipo');

        return view('admin.wpp_logs.index', compact('logs', 'tipos'));
    }
}
