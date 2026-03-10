<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
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
            ->with(['servicio', 'empleado'])
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

        $servicioFavorito = $cliente->citas()
            ->where('estado', 'completada')
            ->select('servicio_id', DB::raw('count(*) as total'))
            ->groupBy('servicio_id')
            ->orderByDesc('total')
            ->with('servicio')
            ->first();

        $citasPorMes = $citas->groupBy(fn($c) => \Carbon\Carbon::parse($c->fecha)->format('Y-m'));

        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empleados = Empleado::where('activo', true)->orderBy('apellido')->get();

        return view('admin.clientes.show', compact(
            'cliente', 'citas', 'citasPorMes', 'stats', 'servicioFavorito', 'periodo',
            'servicios', 'empleados'
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
            'servicio_id'  => 'required|exists:servicios,id',
            'empleado_id'  => 'nullable|exists:empleados,id',
            'fecha'        => 'required|date',
            'hora'         => 'required',
            'estado'       => 'required|in:pendiente,confirmada,completada,cancelada',
            'precio_final' => 'nullable|numeric|min:0',
            'notas'        => 'nullable|string',
        ]);

        $data['cliente_id'] = $cliente->id;

        Cita::create($data);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Visita registrada exitosamente.');
    }
}
