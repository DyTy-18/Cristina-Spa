<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->get('estado');

        $query = Lead::orderBy('created_at', 'desc');

        if ($estado && $estado !== 'todos') {
            $query->where('estado', $estado);
        }

        $leads   = $query->paginate(50)->withQueryString();
        $totales = Lead::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('admin.leads.index', compact('leads', 'estado', 'totales'));
    }

    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'estado' => 'required|in:nuevo,contactado,convertido,perdido',
        ]);

        $lead->update(['estado' => $request->estado]);

        return response()->json(['ok' => true, 'estado' => $lead->estado]);
    }
}
