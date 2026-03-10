<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function create()
    {
        $clientesRaw = Cliente::orderBy('apellido')->orderBy('nombre')->get(['id', 'nombre', 'apellido', 'telefono']);
        $servicios   = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empleados   = Empleado::where('activo', true)->orderBy('apellido')->get();

        // Transformar para el buscador JS (evitar closures dentro de @json en Blade)
        $clientesJson = $clientesRaw->map(function ($c) {
            return [
                'id'     => $c->id,
                'nombre' => trim($c->nombre . ' ' . ($c->apellido ?? '')),
                'tel'    => $c->telefono ?? '',
            ];
        })->values();

        return view('admin.citas.create', compact('clientesRaw', 'clientesJson', 'servicios', 'empleados'));
    }

    public function store(Request $request)
    {
        $clienteTipo = $request->input('cliente_tipo', 'existente');

        // Validación base
        $rules = [
            'cliente_tipo'  => 'required|in:existente,nuevo',
            'servicio_id'   => 'required|exists:servicios,id',
            'empleado_id'   => 'nullable|exists:empleados,id',
            'fecha'         => 'required|date',
            'hora'          => 'required',
            'estado'        => 'required|in:pendiente,confirmada,completada,cancelada',
            'precio_final'  => 'nullable|numeric|min:0',
            'notas'         => 'nullable|string',
        ];

        if ($clienteTipo === 'existente') {
            $rules['cliente_id'] = 'required|exists:clientes,id';
        } else {
            $rules['nuevo_nombre']    = 'required|string|max:100';
            $rules['nuevo_apellido']  = 'nullable|string|max:100';
            $rules['nuevo_telefono']  = 'required|string|max:20';
            $rules['nuevo_email']     = 'nullable|email|unique:clientes,email';
        }

        $data = $request->validate($rules);

        // Resolver el cliente
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

        Cita::create([
            'cliente_id'   => $clienteId,
            'servicio_id'  => $data['servicio_id'],
            'empleado_id'  => $data['empleado_id'] ?? null,
            'fecha'        => $data['fecha'],
            'hora'         => $data['hora'],
            'estado'       => $data['estado'],
            'precio_final' => $data['precio_final'] ?? null,
            'notas'        => $data['notas'] ?? null,
        ]);

        return redirect()->route('admin.citas.calendario')
            ->with('success', 'Cita registrada exitosamente.');
    }

    public function calendario()
    {
        return view('admin.citas.calendario');
    }

    public function calendarDatos(Request $request)
    {
        $start = $request->get('start');
        $end   = $request->get('end');

        $citas = Cita::with(['cliente', 'servicio', 'empleado'])
            ->when($start, fn($q) => $q->whereDate('fecha', '>=', Carbon::parse($start)->toDateString()))
            ->when($end,   fn($q) => $q->whereDate('fecha', '<=', Carbon::parse($end)->toDateString()))
            ->get();

        $colores = [
            'pendiente' => ['bg' => '#ffc107', 'border' => '#d39e00', 'text' => '#1a1a1a'],
            'confirmada' => ['bg' => '#c9a96e', 'border' => '#8b7355', 'text' => '#fff'],
            'completada' => ['bg' => '#28a745', 'border' => '#1e7e34', 'text' => '#fff'],
            'cancelada'  => ['bg' => '#e8726b', 'border' => '#c82333', 'text' => '#fff'],
        ];

        $events = $citas->map(function ($cita) use ($colores) {
            $hora    = $cita->getRawOriginal('hora') ?? '09:00';
            $startDt = Carbon::parse($cita->fecha->format('Y-m-d') . ' ' . $hora);
            $endDt   = $startDt->copy()->addMinutes($cita->servicio?->duracion_minutos ?? 60);
            $color   = $colores[$cita->estado] ?? $colores['pendiente'];

            return [
                'id'              => $cita->id,
                'title'           => ($cita->servicio?->nombre ?? 'Servicio') . ' · ' . ($cita->cliente?->nombre ?? ''),
                'start'           => $startDt->format('Y-m-d\TH:i:s'),
                'end'             => $endDt->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color['bg'],
                'borderColor'     => $color['border'],
                'textColor'       => $color['text'],
                'extendedProps'   => [
                    'cliente_id'  => $cita->cliente_id,
                    'cliente'     => $cita->cliente?->nombre_completo,
                    'servicio'    => $cita->servicio?->nombre,
                    'duracion'    => $cita->servicio?->duracion_formateada,
                    'empleado'    => $cita->empleado
                        ? $cita->empleado->nombre . ' ' . $cita->empleado->apellido
                        : null,
                    'estado'      => $cita->estado,
                    'precio'      => $cita->precio_final,
                    'notas'       => $cita->notas,
                    'hora'        => $startDt->format('H:i'),
                    'fecha_fmt'   => $cita->fecha->translatedFormat('l d \d\e F \d\e Y'),
                ],
            ];
        });

        return response()->json($events);
    }
}
