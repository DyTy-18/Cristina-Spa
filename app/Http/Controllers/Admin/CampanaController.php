<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campana;
use Illuminate\Http\Request;

class CampanaController extends Controller
{
    public function index()
    {
        $campanas = Campana::withCount('citas')
            ->orderBy('activo', 'desc')
            ->orderBy('nombre')
            ->get();

        return view('admin.campanas.index', compact('campanas'));
    }

    public function create()
    {
        return view('admin.campanas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:150',
            'descripcion'  => 'nullable|string',
            'activo'       => 'boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        Campana::create($data);

        return redirect()->route('admin.campanas.index')
            ->with('success', 'Campaña creada exitosamente.');
    }

    public function edit(Campana $campana)
    {
        return view('admin.campanas.edit', compact('campana'));
    }

    public function update(Request $request, Campana $campana)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:150',
            'descripcion'  => 'nullable|string',
            'activo'       => 'boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $data['activo'] = $request->boolean('activo');

        $campana->update($data);

        return redirect()->route('admin.campanas.index')
            ->with('success', 'Campaña actualizada exitosamente.');
    }
}
