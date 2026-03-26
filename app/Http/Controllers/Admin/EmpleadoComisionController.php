<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComisionEmpleado;
use App\Models\Empleado;
use App\Models\Servicio;
use Illuminate\Http\Request;

class EmpleadoComisionController extends Controller
{
    public function index(Empleado $empleado)
    {
        $servicios = Servicio::orderBy('nombre')
            ->with(['comision', 'comisionesEmpleado' => fn($q) => $q->where('empleado_id', $empleado->id)])
            ->get();

        return view('admin.empleados.comisiones', compact('empleado', 'servicios'));
    }

    public function update(Request $request, Empleado $empleado, Servicio $servicio)
    {
        $data = $request->validate([
            'porcentaje' => 'required|numeric|min:0|max:100',
            'activo'     => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo');

        ComisionEmpleado::updateOrCreate(
            ['empleado_id' => $empleado->id, 'servicio_id' => $servicio->id],
            $data
        );

        return back()->with('success', 'Comisión de "' . $servicio->nombre . '" actualizada para ' . $empleado->nombre_completo . '.');
    }
}
