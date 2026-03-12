<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comision;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ComisionController extends Controller
{
    public function index()
    {
        $servicios = Servicio::orderBy('nombre')
            ->with('comision')
            ->get();

        return view('admin.comisiones.index', compact('servicios'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $data = $request->validate([
            'porcentaje' => 'required|numeric|min:0|max:100',
            'activo'     => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo');

        $servicio->comision()->updateOrCreate(
            ['servicio_id' => $servicio->id],
            $data
        );

        return back()->with('success', 'Comisión de "' . $servicio->nombre . '" actualizada.');
    }
}
