<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CreaContratoPaquete;
use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\DescuentoProgramado;
use App\Models\Empleado;
use App\Models\Campana;
use App\Models\Paquete;
use App\Models\Servicio;
use App\Services\WppService;
use App\Services\MaterialConsumptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    use CreaContratoPaquete;
    /** Devuelve un mapa [servicio_id => porcentaje] con los descuentos vigentes. */
    private function descuentosVigentesMapa(): array
    {
        $vigentes = DescuentoProgramado::vigentes()->get(['servicio_id', 'porcentaje']);
        $global   = (float) ($vigentes->whereNull('servicio_id')->max('porcentaje') ?? 0);

        return $vigentes->whereNotNull('servicio_id')
            ->groupBy('servicio_id')
            ->map(fn($g) => (float) $g->max('porcentaje'))
            ->union($global > 0 ? ['__global' => $global] : [])
            ->toArray();
    }

    public function create()
    {
        $clientesRaw = Cliente::orderBy('apellido')->orderBy('nombre')->get(['id', 'nombre', 'apellido', 'telefono']);
        $servicios   = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empleados   = Empleado::where('activo', true)->orderBy('apellido')->get(['id', 'nombre', 'apellido']);

        $clientesJson = $clientesRaw->map(fn($c) => [
            'id'     => $c->id,
            'nombre' => trim($c->nombre . ' ' . ($c->apellido ?? '')),
            'tel'    => $c->telefono ?? '',
        ])->values();

        $campanas       = Campana::activas()->orderBy('nombre')->get();
        $descuentosMapa = $this->descuentosVigentesMapa();

        $serviciosJson = $servicios->map(fn($s) => [
            'id'        => $s->id,
            'nombre'    => $s->nombre,
            'precio'    => $s->precio,
            'duracion'  => $s->duracion_minutos,
            'categoria' => $s->categoria,
            'descuento' => $descuentosMapa[$s->id] ?? $descuentosMapa['__global'] ?? 0,
        ])->values();

        $empleadosJson = $empleados->map(fn($e) => [
            'id'     => $e->id,
            'nombre' => trim($e->nombre . ' ' . $e->apellido),
        ])->values();

        $paquetes    = Paquete::where('activo', true)->with(['nivel', 'servicios'])->orderBy('categoria')->orderBy('nombre')->get();
        $paquetesJson = $paquetes->map(fn($p) => [
            'id'       => $p->id,
            'nombre'   => $p->nombre,
            'categoria'=> $p->categoria,
            'nivel'    => $p->nivel ? ['nombre' => $p->nivel->nombre, 'color' => $p->nivel->color] : null,
            'descuento_general' => $p->descuento_general,
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

        return view('admin.citas.create', compact(
            'clientesRaw', 'clientesJson', 'servicios', 'empleados', 'campanas',
            'serviciosJson', 'empleadosJson', 'paquetesJson'
        ));
    }

    public function store(Request $request)
    {
        $clienteTipo = $request->input('cliente_tipo', 'existente');

        $rules = array_merge([
            'cliente_tipo'                  => 'required|in:existente,nuevo',
            'fecha'                         => 'required|date',
            'hora'                          => 'required',
            'estado'                        => 'required|in:pendiente,confirmada,completada,cancelada',
            'notas'                         => 'nullable|string',
            'campana_id'                    => 'nullable|exists:campanas,id',
            'servicios'                     => 'required|array|min:1',
            'servicios.*.servicio_id'       => 'required|exists:servicios,id',
            'servicios.*.precio'            => 'nullable|numeric|min:0',
            'servicios.*.descuento'         => 'nullable|numeric|min:0|max:100',
            'profesionales'                 => 'nullable|array',
            'profesionales.*.empleado_id'   => 'nullable|exists:empleados,id',
            'profesionales.*.servicio_id'   => 'nullable|exists:servicios,id',
        ], $this->contratoValidationRules());

        if ($clienteTipo === 'existente') {
            $rules['cliente_id'] = 'required|exists:clientes,id';
        } else {
            $rules['nuevo_nombre']   = 'required|string|max:100';
            $rules['nuevo_apellido'] = 'nullable|string|max:100';
            $rules['nuevo_telefono'] = 'required|string|max:20';
            $rules['nuevo_email']    = 'nullable|email|unique:clientes,email';
        }

        $data = $request->validate($rules);

        if ($clienteTipo === 'nuevo') {
            $cliente = Cliente::create([
                'nombre'   => $data['nuevo_nombre'],
                'apellido' => $data['nuevo_apellido'] ?? null,
                'telefono' => $data['nuevo_telefono'],
                'email'    => $data['nuevo_email'] ?? null,
            ]);
            $clienteId = $cliente->id;
        } else {
            $clienteId = $data['cliente_id'];
        }

        $serviciosBase = Servicio::whereIn('id', collect($data['servicios'])->pluck('servicio_id'))->pluck('precio', 'id');

        // Mapa servicio_id → empleado_id desde la sección de profesionales
        $empleadoPorServicio = [];
        foreach ($data['profesionales'] ?? [] as $prof) {
            if (!empty($prof['empleado_id']) && !empty($prof['servicio_id'])) {
                $empleadoPorServicio[$prof['servicio_id']] = $prof['empleado_id'];
            }
        }

        $precioTotal = collect($data['servicios'])->sum(function ($s) use ($serviciosBase) {
            $precio = (float) ($s['precio'] ?? ($serviciosBase[$s['servicio_id']] ?? 0));
            $desc   = (float) ($s['descuento'] ?? 0);
            return round($precio * (1 - $desc / 100), 2);
        });

        $cita = Cita::create([
            'cliente_id'   => $clienteId,
            'campana_id'   => $data['campana_id'] ?? null,
            'fecha'        => $data['fecha'],
            'hora'         => $data['hora'],
            'estado'       => $data['estado'],
            'precio_final' => $precioTotal ?: null,
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

        // Crear contrato de paquete si se solicitó
        $this->crearContratoSiCorresponde($request, $clienteId);

        app(WppService::class)->notificarSegunEstado($cita);

        return redirect()->route('admin.citas.calendario')
            ->with('success', 'Cita registrada exitosamente.');
    }

    public function show(Cita $cita)
    {
        $cita->load(['cliente', 'citaServicios.servicio', 'citaServicios.empleado', 'campana']);
        return view('admin.citas.show', compact('cita'));
    }

    public function edit(Cita $cita)
    {
        $cita->load(['citaServicios.servicio', 'citaServicios.empleado']);
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empleados = Empleado::where('activo', true)->orderBy('apellido')->get(['id', 'nombre', 'apellido']);
        $campanas  = Campana::activas()->orWhere('id', $cita->campana_id)->orderBy('nombre')->get();

        $descuentosMapa = $this->descuentosVigentesMapa();

        $serviciosJson = $servicios->map(fn($s) => [
            'id'        => $s->id,
            'nombre'    => $s->nombre,
            'precio'    => $s->precio,
            'duracion'  => $s->duracion_minutos,
            'categoria' => $s->categoria,
            'descuento' => $descuentosMapa[$s->id] ?? $descuentosMapa['__global'] ?? 0,
        ])->values();

        $empleadosJson = $empleados->map(fn($e) => [
            'id'     => $e->id,
            'nombre' => trim($e->nombre . ' ' . $e->apellido),
        ])->values();

        return view('admin.citas.edit', compact('cita', 'servicios', 'empleados', 'serviciosJson', 'empleadosJson', 'campanas'));
    }

    public function update(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'fecha'                         => 'required|date',
            'hora'                          => 'required',
            'estado'                        => 'required|in:pendiente,confirmada,completada,cancelada',
            'notas'                         => 'nullable|string',
            'campana_id'                    => 'nullable|exists:campanas,id',
            'servicios'                     => 'required|array|min:1',
            'servicios.*.servicio_id'       => 'required|exists:servicios,id',
            'servicios.*.precio'            => 'nullable|numeric|min:0',
            'servicios.*.descuento'         => 'nullable|numeric|min:0|max:100',
            'profesionales'                 => 'nullable|array',
            'profesionales.*.empleado_id'   => 'nullable|exists:empleados,id',
            'profesionales.*.servicio_id'   => 'nullable|exists:servicios,id',
        ]);

        $serviciosBase = Servicio::whereIn('id', collect($data['servicios'])->pluck('servicio_id'))->pluck('precio', 'id');

        $empleadoPorServicio = [];
        foreach ($data['profesionales'] ?? [] as $prof) {
            if (!empty($prof['empleado_id']) && !empty($prof['servicio_id'])) {
                $empleadoPorServicio[$prof['servicio_id']] = $prof['empleado_id'];
            }
        }

        $precioTotal = collect($data['servicios'])->sum(function ($s) use ($serviciosBase) {
            $precio = (float) ($s['precio'] ?? ($serviciosBase[$s['servicio_id']] ?? 0));
            $desc   = (float) ($s['descuento'] ?? 0);
            return round($precio * (1 - $desc / 100), 2);
        });

        $estadoAnterior = $cita->estado;

        $cita->update([
            'campana_id'   => $data['campana_id'] ?? null,
            'fecha'        => $data['fecha'],
            'hora'         => $data['hora'],
            'estado'       => $data['estado'],
            'precio_final' => $precioTotal ?: null,
            'notas'        => $data['notas'] ?? null,
        ]);

        $cita->citaServicios()->delete();

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

        if ($estadoAnterior !== $cita->estado) {
            app(WppService::class)->notificarSegunEstado($cita);

            if ($cita->estado === 'completada') {
                app(MaterialConsumptionService::class)->procesarCita($cita);
            }
        }

        return redirect()->route('admin.citas.show', $cita)
            ->with('success', 'Cita actualizada exitosamente.');
    }

    public function calendario()
    {
        return view('admin.citas.calendario');
    }

    public function calendarDatos(Request $request)
    {
        $start = $request->get('start');
        $end   = $request->get('end');

        $citas = Cita::with(['cliente', 'citaServicios.servicio', 'citaServicios.empleado'])
            ->when($start, fn($q) => $q->whereDate('fecha', '>=', Carbon::parse($start)->toDateString()))
            ->when($end,   fn($q) => $q->whereDate('fecha', '<=', Carbon::parse($end)->toDateString()))
            ->get();

        $colores = [
            'pendiente'  => ['bg' => '#ffc107', 'border' => '#d39e00', 'text' => '#1a1a1a'],
            'confirmada' => ['bg' => '#c9a96e', 'border' => '#8b7355', 'text' => '#fff'],
            'completada' => ['bg' => '#28a745', 'border' => '#1e7e34', 'text' => '#fff'],
            'cancelada'  => ['bg' => '#e8726b', 'border' => '#c82333', 'text' => '#fff'],
        ];

        $events = $citas->map(function ($cita) use ($colores) {
            $hora    = $cita->getRawOriginal('hora') ?? '09:00';
            $startDt = Carbon::parse($cita->fecha->format('Y-m-d') . ' ' . $hora);

            $duracionTotal = $cita->citaServicios->sum(fn($cs) => $cs->servicio?->duracion_minutos ?? 0) ?: 60;
            $endDt = $startDt->copy()->addMinutes($duracionTotal);

            $color = $colores[$cita->estado] ?? $colores['pendiente'];

            $nombresServicios = $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' + ');
            $empleadosNombres = $cita->citaServicios
                ->filter(fn($cs) => $cs->empleado)
                ->map(fn($cs) => $cs->empleado->nombre . ' ' . $cs->empleado->apellido)
                ->unique()->implode(', ');

            return [
                'id'              => $cita->id,
                'title'           => ($nombresServicios ?: 'Servicio') . ' · ' . ($cita->cliente?->nombre ?? ''),
                'start'           => $startDt->format('Y-m-d\TH:i:s'),
                'end'             => $endDt->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color['bg'],
                'borderColor'     => $color['border'],
                'textColor'       => $color['text'],
                'extendedProps'   => [
                    'cliente_id' => $cita->cliente_id,
                    'cliente'    => $cita->cliente?->nombre_completo,
                    'servicio'   => $nombresServicios,
                    'duracion'   => $duracionTotal . ' min',
                    'empleado'   => $empleadosNombres ?: null,
                    'estado'     => $cita->estado,
                    'precio'     => $cita->precio_final,
                    'notas'      => $cita->notas,
                    'hora'       => $startDt->format('H:i'),
                    'fecha_fmt'  => $cita->fecha->translatedFormat('l d \d\e F \d\e Y'),
                ],
            ];
        });

        return response()->json($events);
    }
}
