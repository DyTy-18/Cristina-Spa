<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

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

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $logs = $query->paginate(50)->withQueryString();

        $logNames = Activity::distinct()->orderBy('log_name')->pluck('log_name');
        $usuarios = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('admin.logs.index', compact('logs', 'logNames', 'usuarios'));
    }
}
