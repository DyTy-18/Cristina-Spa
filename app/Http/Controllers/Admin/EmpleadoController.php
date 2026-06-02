<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $sucursales = Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.empleados.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'             => 'required|string|max:100',
            'apellido'           => 'nullable|string|max:100',
            'telefono'           => 'nullable|string|max:20|unique:users,email',
            'cargo'              => 'required|string|max:50',
            'especialidad'       => 'nullable|string|max:150',
            'fecha_contratacion' => 'nullable|date',
            'activo'             => 'boolean',
            'password'           => 'nullable|string|min:8',
        ], [
            'telefono.unique' => 'Ya existe un usuario con ese número de teléfono.',
        ]);

        $empleado = DB::transaction(function () use ($request) {
            $empleado = Empleado::create([
                'nombre'             => $request->nombre,
                'apellido'           => $request->apellido,
                'telefono'           => $request->telefono,
                'cargo'              => $request->cargo,
                'especialidad'       => $request->especialidad,
                'fecha_contratacion' => $request->fecha_contratacion,
                'activo'             => $request->boolean('activo', true),
            ]);

            if ($request->filled('password') && $request->filled('telefono')) {
                $user = User::create([
                    'name'     => $empleado->nombre_completo,
                    'email'    => $request->telefono, // teléfono como identificador de login
                    'password' => $request->password,
                ]);
                $user->assignRole($this->rolParaCargo($request->cargo));
                $empleado->update([
                    'user_id'      => $user->id,
                    'clave_acceso' => $request->password,
                ]);
            }

            return $empleado;
        });

        // Asignar sucursales seleccionadas
        $empleado->sucursales()->sync($request->input('sucursales', []));

        return redirect()->route('admin.empleados.show', $empleado)
            ->with('success', 'Empleado registrado exitosamente.');
    }

    public function show(Empleado $empleado)
    {
        $empleado->load('user', 'sucursales');

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
        $empleado->load('user', 'sucursales');
        $sucursales = Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.empleados.edit', compact('empleado', 'sucursales'));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $ignoreUserId = $empleado->user_id ?? 'NULL';

        $request->validate([
            'nombre'             => 'required|string|max:100',
            'apellido'           => 'nullable|string|max:100',
            'telefono'           => "nullable|string|max:20|unique:users,email,{$ignoreUserId}",
            'cargo'              => 'required|string|max:50',
            'especialidad'       => 'nullable|string|max:150',
            'fecha_contratacion' => 'nullable|date',
            'activo'             => 'boolean',
            'password'           => 'nullable|string|min:8',
        ], [
            'telefono.unique' => 'Ya existe otro usuario con ese número de teléfono.',
        ]);

        DB::transaction(function () use ($request, $empleado) {
            $empleado->update([
                'nombre'             => $request->nombre,
                'apellido'           => $request->apellido,
                'telefono'           => $request->telefono,
                'cargo'              => $request->cargo,
                'especialidad'       => $request->especialidad,
                'fecha_contratacion' => $request->fecha_contratacion,
                'activo'             => $request->boolean('activo'),
            ]);

            $nombreCompleto = trim($request->nombre . ' ' . $request->apellido);

            if ($empleado->user) {
                // Sincronizar teléfono → email de login, actualizar nombre
                $userUpdate = [
                    'name'  => $nombreCompleto,
                    'email' => $request->telefono ?? $empleado->user->email,
                ];
                if ($request->filled('password')) {
                    $userUpdate['password'] = $request->password;
                    $empleado->update(['clave_acceso' => $request->password]);
                }
                $empleado->user->update($userUpdate);
            } elseif ($request->filled('password') && $request->filled('telefono')) {
                // Crear usuario nuevo y vincularlo
                $user = User::create([
                    'name'     => $nombreCompleto,
                    'email'    => $request->telefono,
                    'password' => $request->password,
                ]);
                $user->assignRole($this->rolParaCargo($request->cargo));
                $empleado->update([
                    'user_id'      => $user->id,
                    'clave_acceso' => $request->password,
                ]);
            }
        });

        // Sincronizar sucursales
        $empleado->sucursales()->sync($request->input('sucursales', []));

        return redirect()->route('admin.empleados.show', $empleado)
            ->with('success', 'Empleado actualizado exitosamente.');
    }

    private function rolParaCargo(string $cargo): string
    {
        return match($cargo) {
            'recepcionista' => 'secretario',
            'cajera'        => 'cajero',
            default         => 'estilista',
        };
    }
}
