<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CreaContratoPaquete;
use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\ContratoPaquete;
use App\Models\DescuentoProgramado;
use App\Models\Empleado;
use App\Models\Paquete;
use App\Models\Servicio;
use App\Services\WppService;
use App\Services\MaterialConsumptionService;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    use CreaContratoPaquete;
    public function index(Request $request)
    {
        $sid     = session('sucursal_activa_id');
        $ordenar = $request->get('ordenar', 'recientes');

        $query = Cliente::withCount(['citas' => fn($q) => $q->when($sid, fn($q2) => $q2->where('sucursal_id', $sid))])
            ->withCount(['citas as citas_completadas_count' => fn($q) => $q->where('estado', 'completada')->when($sid, fn($q2) => $q2->where('sucursal_id', $sid))])
            ->withCount(['citas as citas_ultimos_3meses' => fn($q) => $q->where('estado', 'completada')->where('fecha', '>=', now()->subMonths(3))->when($sid, fn($q2) => $q2->where('sucursal_id', $sid))])
            ->withSum(['citas as total_gastado' => fn($q) => $q->where('estado', 'completada')->when($sid, fn($q2) => $q2->where('sucursal_id', $sid))], 'precio_final')
            ->withMax(['citas as ultima_visita' => fn($q) => $q->where('estado', 'completada')->when($sid, fn($q2) => $q2->where('sucursal_id', $sid))], 'fecha')
            ->withMin(['citas as primera_visita' => fn($q) => $q->where('estado', 'completada')->when($sid, fn($q2) => $q2->where('sucursal_id', $sid))], 'fecha')
            ->when($sid, fn($q) => $q->where(function ($q2) use ($sid) {
                $q2->where('sucursal_id', $sid)
                   ->orWhereHas('citas', fn($q3) => $q3->where('sucursal_id', $sid));
            }))
            ->where('oculto', false);

        match ($ordenar) {
            'frecuentes'  => $query->orderByDesc('citas_ultimos_3meses')->orderByDesc('citas_completadas_count'),
            'gasto'       => $query->orderByDesc('total_gastado'),
            'alfabetico'  => $query->orderBy('apellido')->orderBy('nombre'),
            default       => $query->orderByDesc('created_at'),   // recientes
        };

        $clientes = $query->get();

        $esAdmin = auth()->user()->hasRole('admin');
        $totalOcultos = $esAdmin ? Cliente::where('oculto', true)->count() : 0;

        return view('admin.clientes.index', compact('clientes', 'esAdmin', 'totalOcultos', 'ordenar'));
    }

    public function ocultos()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $clientes = Cliente::withCount('citas')
            ->withCount(['citas as citas_completadas_count' => fn($q) => $q->where('estado', 'completada')])
            ->withSum(['citas as total_gastado' => fn($q) => $q->where('estado', 'completada')], 'precio_final')
            ->withMax(['citas as ultima_visita' => fn($q) => $q->where('estado', 'completada')], 'fecha')
            ->withMin(['citas as primera_visita' => fn($q) => $q->where('estado', 'completada')], 'fecha')
            ->where('oculto', true)
            ->orderBy('apellido')
            ->get();

        return view('admin.clientes.ocultos', compact('clientes'));
    }

    public function toggleOculto(Cliente $cliente)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $cliente->update(['oculto' => !$cliente->oculto]);

        $mensaje = $cliente->oculto ? 'Cliente ocultado.' : 'Cliente restaurado.';

        return redirect()->back()->with('success', $mensaje);
    }

    public function create()
    {
        $empleados = Empleado::where('activo', true)->orderBy('nombre')->get();
        return view('admin.clientes.create', compact('empleados'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'                => 'required|string|max:100',
            'apellido'              => 'nullable|string|max:100',
            'email'                 => 'nullable|email|unique:clientes,email',
            'telefono'              => 'nullable|string|max:20',
            'fecha_nacimiento'      => 'nullable|date',
            'direccion'             => 'nullable|string|max:255',
            'notas'                 => 'nullable|string',
            'empleado_exclusivo_id' => 'nullable|exists:empleados,id',
        ]);

        $cliente = Cliente::create(array_merge($data, [
            'sucursal_id' => session('sucursal_activa_id') ?? auth()->user()->sucursal_id,
        ]));

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente registrado exitosamente.');
    }

    public function show(Request $request, Cliente $cliente)
    {
        $sid     = session('sucursal_activa_id');
        $periodo = $request->get('periodo', 'recientes');

        $query = $cliente->citas()
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
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
            case 'recientes':
                $query->limit(4);
                break;
        }

        $citas = $query->get();

        // KPIs acotados a la sucursal activa
        $citasCompletadas = $cliente->citas()
            ->where('estado', 'completada')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid));

        $stats = [
            'total_citas'       => $cliente->citas()->when($sid, fn($q) => $q->where('sucursal_id', $sid))->count(),
            'citas_completadas' => $citasCompletadas->count(),
            'total_gastado'     => $citasCompletadas->sum('precio_final'),
            'ultima_visita'     => $citasCompletadas->max('fecha'),
            'visitas_mes'       => (clone $citasCompletadas)->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->count(),
            'total_mes'         => (clone $citasCompletadas)->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('precio_final'),
        ];

        $ultimaCitaCompletada = $cliente->citas()
            ->where('estado', 'completada')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->with('citaServicios.servicio')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->first();

        $ultimoServicio = $ultimaCitaCompletada?->citaServicios
            ->map(fn($cs) => $cs->servicio?->nombre)
            ->filter()
            ->implode(' + ');

        $servicioFavorito = null;

        $citasPorMes = $citas->groupBy(fn($c) => \Carbon\Carbon::parse($c->fecha)->format('Y-m'));

        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empleados = Empleado::where('activo', true)->orderBy('apellido')->get();
        $campanas  = Campana::activas()->orderBy('nombre')->get();

        $descVigentes   = DescuentoProgramado::vigentes()->get(['servicio_id', 'porcentaje']);
        $descGlobal     = (float) ($descVigentes->whereNull('servicio_id')->max('porcentaje') ?? 0);

        // Frecuencia real de cada servicio en citas completadas
        $frecuencias = \DB::table('cita_servicios')
            ->join('citas', 'cita_servicios.cita_id', '=', 'citas.id')
            ->where('citas.estado', 'completada')
            ->selectRaw('cita_servicios.servicio_id, COUNT(*) as total')
            ->groupBy('cita_servicios.servicio_id')
            ->pluck('total', 'servicio_id');

        // Orden de popularidad predefinido (para cuando no hay historial aún)
        $popularidadBase = [
            'Manicura', 'Pedicura', 'Corte de Mujer', 'Lavado', 'Planchado o Bucles',
            'Esmaltado Normal', 'Perfilado de Cejas', 'Depilación Axilas', 'Pintado en Gel',
            'Corte de Varón', 'Depilación Facial', 'Retoque de Raíz', 'Semi-recogido',
            'Depilación Bozo', 'Corte flequillo', 'Recogido', 'Laminado de Cejas',
        ];
        $popularidadIdx = array_flip($popularidadBase);

        $serviciosOrdenados = $servicios->sortByDesc(function ($s) use ($frecuencias, $popularidadIdx) {
            $real = (int) ($frecuencias[$s->id] ?? 0);
            // Si hay datos reales los usa; si no, usa el índice base invertido como desempate
            $base = isset($popularidadIdx[$s->nombre])
                ? (count($popularidadIdx) - $popularidadIdx[$s->nombre])
                : 0;
            return $real * 1000 + $base;
        })->values();

        $modalServiciosJson = $serviciosOrdenados->map(fn($s) => [
            'id'        => $s->id,
            'nombre'    => $s->nombre,
            'precio'    => $s->precio,
            'categoria' => $s->categoria,
            'descuento' => (float) ($descVigentes->where('servicio_id', $s->id)->max('porcentaje') ?? $descGlobal ?: 0),
        ])->values();

        $modalEmpleadosJson = $empleados->map(fn($e) => [
            'id'     => $e->id,
            'nombre' => trim($e->nombre . ' ' . $e->apellido),
        ])->values();

        $paquetes     = Paquete::where('activo', true)->with(['nivel', 'servicios'])->orderBy('categoria')->orderBy('nombre')->get();
        $paquetesJson = $paquetes->map(fn($p) => [
            'id'       => $p->id,
            'nombre'   => $p->nombre,
            'categoria'=> $p->categoria,
            'nivel'    => $p->nivel ? ['nombre' => $p->nivel->nombre, 'color' => $p->nivel->color] : null,
            'precio_total' => $p->precio_total,
            'servicios'=> $p->servicios->map(fn($s) => [
                'servicio_id' => $s->id,
                'nombre'      => $s->nombre,
                'precio'      => (float) $s->precio,
                'descuento'   => $s->pivot->descuento_porcentaje !== null
                                    ? (float) $s->pivot->descuento_porcentaje
                                    : (float) ($p->descuento_general ?? 0),
            ])->values(),
        ])->values();

        return view('admin.clientes.show', compact(
            'cliente', 'citas', 'citasPorMes', 'stats', 'ultimoServicio', 'periodo',
            'servicios', 'empleados', 'campanas', 'modalServiciosJson', 'modalEmpleadosJson', 'paquetesJson'
        ));
    }

    public function edit(Cliente $cliente)
    {
        $empleados = Empleado::where('activo', true)->orderBy('nombre')->get();
        return view('admin.clientes.edit', compact('cliente', 'empleados'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre'                => 'required|string|max:100',
            'apellido'              => 'nullable|string|max:100',
            'email'                 => 'nullable|email|unique:clientes,email,' . $cliente->id,
            'telefono'              => 'nullable|string|max:20',
            'fecha_nacimiento'      => 'nullable|date',
            'direccion'             => 'nullable|string|max:255',
            'notas'                 => 'nullable|string',
            'empleado_exclusivo_id' => 'nullable|exists:empleados,id',
        ]);

        $cliente->update($data);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function storeCita(Request $request, Cliente $cliente)
    {
        $data = $request->validate(array_merge([
            'fecha'                         => 'required|date',
            'hora'                          => 'required',
            'estado'                        => 'required|in:pendiente,confirmada,completada,cancelada',
            'tipo_pago'                     => 'nullable|in:efectivo,tarjeta,qr',
            'notas'                         => 'nullable|string',
            'servicios'                     => 'required|array|min:1',
            'servicios.*.servicio_id'       => 'required|exists:servicios,id',
            'servicios.*.precio'            => 'nullable|numeric|min:0',
            'servicios.*.descuento'         => 'nullable|numeric|min:0|max:100',
            'profesionales'                 => 'nullable|array',
            'profesionales.*.empleado_id'   => 'nullable|exists:empleados,id',
            'profesionales.*.servicio_id'   => 'nullable|exists:servicios,id',
            'campana_id'                    => 'nullable|exists:campanas,id',
        ], $this->contratoValidationRules()));

        $serviciosBase = Servicio::whereIn('id', collect($data['servicios'])->pluck('servicio_id'))->pluck('precio', 'id');

        $empleadoPorServicio = [];
        foreach ($data['profesionales'] ?? [] as $prof) {
            if (!empty($prof['empleado_id']) && !empty($prof['servicio_id'])) {
                $empleadoPorServicio[$prof['servicio_id']] = $prof['empleado_id'];
            }
        }

        // Total = suma de precios netos (precio_unitario aplicando descuento)
        $precioTotal = collect($data['servicios'])->sum(function ($s) use ($serviciosBase) {
            $precio = (float) ($s['precio'] ?? ($serviciosBase[$s['servicio_id']] ?? 0));
            $desc   = (float) ($s['descuento'] ?? 0);
            return round($precio * (1 - $desc / 100), 2);
        });

        $cita = Cita::create([
            'cliente_id'   => $cliente->id,
            'campana_id'   => $data['campana_id'] ?? null,
            'fecha'        => $data['fecha'],
            'hora'         => $data['hora'],
            'estado'       => $data['estado'],
            'precio_final' => $precioTotal ?: null,
            'tipo_pago'    => $data['tipo_pago'] ?? null,
            'notas'        => $data['notas'] ?? null,
        ]);

        foreach ($data['servicios'] as $s) {
            $desc = isset($s['descuento']) && $s['descuento'] > 0 ? $s['descuento'] : null;
            CitaServicio::create([
                'cita_id'              => $cita->id,
                'servicio_id'          => $s['servicio_id'],
                'empleado_id'          => $empleadoPorServicio[$s['servicio_id']] ?? null,
                'precio_unitario'      => $s['precio'] ?? ($serviciosBase[$s['servicio_id']] ?? null),
                'descuento_porcentaje' => $desc,
            ]);
        }

        // Auto-aplicar cupón de recomendación si el cliente tiene uno pendiente
        $cuponReco = $cliente->cuponRecomendacionActivo();
        if ($cuponReco) {
            $nuevoPrecio = 0;
            foreach ($cita->citaServicios as $cs) {
                $descFinal = max((float) ($cs->descuento_porcentaje ?? 0), (float) $cuponReco->valor);
                $cs->update(['descuento_porcentaje' => $descFinal]);
                $nuevoPrecio += round((float) $cs->precio_unitario * (1 - $descFinal / 100), 2);
            }
            $cita->update(['precio_final' => $nuevoPrecio ?: null]);

            \App\Models\CuponUso::create([
                'cupon_id'   => $cuponReco->id,
                'cita_id'    => $cita->id,
                'cliente_id' => $cliente->id,
                'notas'      => 'Cupón de recomendación aplicado automáticamente (20%).',
            ]);
        }

        $this->crearContratoSiCorresponde($request, $cliente->id);

        app(WppService::class)->notificarSegunEstado($cita);

        return redirect()->route('admin.clientes.show', $cliente)
            ->with('success', $cuponReco
                ? 'Visita registrada con cupón de recomendación aplicado (20% off).'
                : 'Visita registrada exitosamente.');
    }

    public function updateCitaEstado(Request $request, Cliente $cliente, Cita $cita)
    {
        abort_if($cita->cliente_id !== $cliente->id, 404);

        $data = $request->validate([
            'estado' => 'required|in:pendiente,confirmada,completada,cancelada',
        ]);

        $estadoAnterior = $cita->estado;
        $cita->update(['estado' => $data['estado']]);

        if ($estadoAnterior !== $cita->estado) {
            app(WppService::class)->notificarSegunEstado($cita);

            if ($cita->estado === 'completada') {
                app(MaterialConsumptionService::class)->procesarCita($cita);
            }
        }

        return response()->json(['estado' => $cita->estado]);
    }
}
