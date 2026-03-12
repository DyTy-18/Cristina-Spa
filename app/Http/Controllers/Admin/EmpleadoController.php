<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $cargo = $request->get('cargo');

        $query = Empleado::withCount('citaServicios')
            ->orderBy('activo', 'desc')
            ->orderBy('apellido');

        if ($cargo) {
            $query->where('cargo', $cargo);
        }

        $empleados = $query->get();
        $cargos = Empleado::distinct()->orderBy('cargo')->pluck('cargo');

        return view('admin.empleados.index', compact('empleados', 'cargos', 'cargo'));
    }

    public function create()
    {
        return view('admin.empleados.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'             => 'required|string|max:100',
            'apellido'           => 'nullable|string|max:100',
            'telefono'           => 'nullable|string|max:20',
            'cargo'              => 'required|string|max:50',
            'especialidad'       => 'nullable|string|max:150',
            'fecha_contratacion' => 'nullable|date',
            'activo'             => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        $empleado = Empleado::create($data);

        return redirect()->route('admin.empleados.show', $empleado)
            ->with('success', 'Empleado registrado exitosamente.');
    }

    public function show(Empleado $empleado)
    {
        $citaIds = $empleado->citaServicios()->pluck('cita_id')->unique();

        $stats = [
            'total_citas'        => $citaIds->count(),
            'citas_completadas'  => Cita::whereIn('id', $citaIds)->where('estado', 'completada')->count(),
            'ingresos_generados' => Cita::whereIn('id', $citaIds)->where('estado', 'completada')->sum('precio_final'),
            'proximas_citas'     => Cita::whereIn('id', $citaIds)->where('estado', 'confirmada')
                ->where('fecha', '>=', now()->toDateString())->count(),
        ];

        $citas = Cita::whereIn('id', $citaIds)
            ->with(['cliente', 'citaServicios' => fn($q) => $q->where('empleado_id', $empleado->id)->with('servicio')])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->take(20)
            ->get();

        return view('admin.empleados.show', compact('empleado', 'stats', 'citas'));
    }

    public function edit(Empleado $empleado)
    {
        return view('admin.empleados.edit', compact('empleado'));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'nombre'             => 'required|string|max:100',
            'apellido'           => 'nullable|string|max:100',
            'telefono'           => 'nullable|string|max:20',
            'cargo'              => 'required|string|max:50',
            'especialidad'       => 'nullable|string|max:150',
            'fecha_contratacion' => 'nullable|date',
            'activo'             => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo');

        $empleado->update($data);

        return redirect()->route('admin.empleados.show', $empleado)
            ->with('success', 'Empleado actualizado exitosamente.');
    }
}
