<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::withCount('citas')
            ->withCount(['citas as citas_completadas_count' => fn($q) => $q->where('estado', 'completada')])
            ->withSum(['citas as total_gastado' => fn($q) => $q->where('estado', 'completada')], 'precio_final')
            ->withMax(['citas as ultima_visita' => fn($q) => $q->where('estado', 'completada')], 'fecha')
            ->withMin(['citas as primera_visita' => fn($q) => $q->where('estado', 'completada')], 'fecha')
            ->orderBy('apellido')
            ->get();

        return view('admin.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('admin.clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'nullable|string|max:100',
            'email'            => 'nullable|email|unique:clientes,email',
            'telefono'         => 'required|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'direccion'        => 'nullable|string|max:255',
            'notas'            => 'nullable|string',
        ]);

        $cliente = Cliente::create($data);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente registrado exitosamente.');
    }

    public function show(Request $request, Cliente $cliente)
    {
        $periodo = $request->get('periodo', 'todo');

        $query = $cliente->citas()
            ->with(['citaServicios.servicio', 'citaServicios.empleado'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc');

        switch ($periodo) {
            case 'mes':
                $query->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year);
                break;
            case 'trimestre':
                $query->where('fecha', '>=', now()->subMonths(3));
                break;
            case 'anio':
                $query->whereYear('fecha', now()->year);
                break;
        }

        $citas = $query->get();

        // KPIs siempre de todo el historial
        $stats = [
            'total_citas'       => $cliente->citas()->count(),
            'citas_completadas' => $cliente->citas()->where('estado', 'completada')->count(),
            'total_gastado'     => $cliente->citas()->where('estado', 'completada')->sum('precio_final'),
            'primera_visita'    => $cliente->citas()->where('estado', 'completada')->min('fecha'),
            'ultima_visita'     => $cliente->citas()->where('estado', 'completada')->max('fecha'),
        ];

        $servicioFavorito = CitaServicio::whereHas('cita', fn($q) => $q->where('cliente_id', $cliente->id)->where('estado', 'completada'))
            ->select('servicio_id', DB::raw('count(*) as total'))
            ->groupBy('servicio_id')
            ->orderByDesc('total')
            ->with('servicio')
            ->first();

        $citasPorMes = $citas->groupBy(fn($c) => \Carbon\Carbon::parse($c->fecha)->format('Y-m'));

        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empleados = Empleado::where('activo', true)->orderBy('apellido')->get();
        $campanas  = Campana::activas()->orderBy('nombre')->get();

        $modalServiciosJson = $servicios->map(fn($s) => [
            'id'     => $s->id,
            'nombre' => $s->nombre,
            'precio' => $s->precio,
        ])->values();

        $modalEmpleadosJson = $empleados->map(fn($e) => [
            'id'     => $e->id,
            'nombre' => trim($e->nombre . ' ' . $e->apellido),
        ])->values();

        return view('admin.clientes.show', compact(
            'cliente', 'citas', 'citasPorMes', 'stats', 'servicioFavorito', 'periodo',
            'servicios', 'empleados', 'campanas', 'modalServiciosJson', 'modalEmpleadosJson'
        ));
    }

    public function edit(Cliente $cliente)
    {
        return view('admin.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'nullable|string|max:100',
            'email'            => 'nullable|email|unique:clientes,email,' . $cliente->id,
            'telefono'         => 'required|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'direccion'        => 'nullable|string|max:255',
            'notas'            => 'nullable|string',
        ]);

        $cliente->update($data);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function storeCita(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'fecha'                         => 'required|date',
            'hora'                          => 'required',
            'estado'                        => 'required|in:pendiente,confirmada,completada,cancelada',
            'notas'                         => 'nullable|string',
            'servicios'                     => 'required|array|min:1',
            'servicios.*.servicio_id'       => 'required|exists:servicios,id',
            'servicios.*.precio'            => 'nullable|numeric|min:0',
            'profesionales'                 => 'nullable|array',
            'profesionales.*.empleado_id'   => 'nullable|exists:empleados,id',
            'profesionales.*.servicio_id'   => 'nullable|exists:servicios,id',
            'campana_id'                    => 'nullable|exists:campanas,id',
        ]);

        $serviciosBase = Servicio::whereIn('id', collect($data['servicios'])->pluck('servicio_id'))->pluck('precio', 'id');

        $empleadoPorServicio = [];
        foreach ($data['profesionales'] ?? [] as $prof) {
            if (!empty($prof['empleado_id']) && !empty($prof['servicio_id'])) {
                $empleadoPorServicio[$prof['servicio_id']] = $prof['empleado_id'];
            }
        }

        $precioTotal = collect($data['servicios'])->sum(
            fn($s) => $s['precio'] ?? ($serviciosBase[$s['servicio_id']] ?? 0)
        );

        $cita = Cita::create([
            'cliente_id'   => $cliente->id,
            'campana_id'   => $data['campana_id'] ?? null,
            'fecha'        => $data['fecha'],
            'hora'         => $data['hora'],
            'estado'       => $data['estado'],
            'precio_final' => $precioTotal ?: null,
            'notas'        => $data['notas'] ?? null,
        ]);

        foreach ($data['servicios'] as $s) {
            CitaServicio::create([
                'cita_id'         => $cita->id,
                'servicio_id'     => $s['servicio_id'],
                'empleado_id'     => $empleadoPorServicio[$s['servicio_id']] ?? null,
                'precio_unitario' => $s['precio'] ?? ($serviciosBase[$s['servicio_id']] ?? null),
            ]);
        }

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Visita registrada exitosamente.');
    }
}
