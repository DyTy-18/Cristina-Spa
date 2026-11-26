<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Calendario interno de cumpleaños de clientas (a partir de fecha_nacimiento de su ficha).
 */
class CumpleanosController extends Controller
{
    public function index()
    {
        $hoy = today();

        $proximos = $this->clientesConCumple()
            ->map(fn (Cliente $c) => [
                'cliente' => $c,
                'fecha'   => $fecha = $this->proximoCumple($c->fecha_nacimiento, $hoy),
                'edad'    => $this->edad($c->fecha_nacimiento, $fecha->year),
            ])
            ->filter(fn ($x) => $x['fecha']->lte($hoy->copy()->addDays(30)))
            ->sortBy(fn ($x) => $x['fecha']->timestamp)
            ->values();

        return view('admin.clientes.cumpleanos', compact('proximos', 'hoy'));
    }

    /** Eventos para FullCalendar: un cumpleaños por clienta y por año dentro del rango [start, end). */
    public function datos(Request $request)
    {
        $inicio = Carbon::parse($request->get('start', now()->startOfMonth()))->startOfDay();
        $fin    = Carbon::parse($request->get('end', now()->endOfMonth()->addDay()))->startOfDay();

        $eventos = [];
        foreach ($this->clientesConCumple() as $c) {
            for ($anio = $inicio->year; $anio <= $fin->year; $anio++) {
                $fecha = $this->cumpleEn($c->fecha_nacimiento, $anio);
                if ($fecha->lt($inicio) || $fecha->gte($fin)) {
                    continue;
                }

                $edad = $this->edad($c->fecha_nacimiento, $anio);

                $eventos[] = [
                    'id'              => "cumple-{$c->id}-{$anio}",
                    'title'           => '🎂 ' . trim("{$c->nombre} {$c->apellido}") . ($edad ? " ({$edad})" : ''),
                    'start'           => $fecha->toDateString(),
                    'allDay'          => true,
                    'url'             => route('admin.clientes.show', $c),
                    'backgroundColor' => '#c9a96e',
                    'borderColor'     => '#8b7355',
                    'textColor'       => '#fff',
                ];
            }
        }

        return response()->json($eventos);
    }

    private function clientesConCumple(): Collection
    {
        return Cliente::whereNotNull('fecha_nacimiento')
            ->where('oculto', false)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'telefono', 'fecha_nacimiento']);
    }

    /** Fecha del cumpleaños en un año dado (29/02 cae el 28/02 en años no bisiestos). */
    private function cumpleEn(Carbon $nacimiento, int $anio): Carbon
    {
        $dia = min($nacimiento->day, Carbon::create($anio, $nacimiento->month, 1)->daysInMonth);

        return Carbon::create($anio, $nacimiento->month, $dia)->startOfDay();
    }

    private function proximoCumple(Carbon $nacimiento, Carbon $desde): Carbon
    {
        $fecha = $this->cumpleEn($nacimiento, $desde->year);

        return $fecha->lt($desde) ? $this->cumpleEn($nacimiento, $desde->year + 1) : $fecha;
    }

    /** Edad que cumple ese año; null si la fecha de nacimiento no es creíble. */
    private function edad(Carbon $nacimiento, int $anio): ?int
    {
        $edad = $anio - $nacimiento->year;

        return $edad > 0 && $edad < 120 ? $edad : null;
    }
}
