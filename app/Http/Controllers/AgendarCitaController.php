<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\SolicitudCita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgendarCitaController extends Controller
{
    public function show()
    {
        $hoy = SolicitudCita::ahora()->startOfDay();
        $limiteHoy = $this->horaMinimaHoy();

        // Si ya no quedan horarios hoy, el calendario empieza mañana
        $primerDia = $limiteHoy > SolicitudCita::HORA_FIN ? $hoy->copy()->addDay() : $hoy;

        return view('agendar.form', [
            'hoy'        => $hoy->toDateString(),
            'primerDia'  => $primerDia->toDateString(),
            'fechaMax'   => $hoy->copy()->addDays(SolicitudCita::DIAS_MAXIMOS)->toDateString(),
            'horarios'   => SolicitudCita::horarios(),
            'limiteHoy'  => $limiteHoy,
            'servicios'  => $this->serviciosPorCategoria(),
        ]);
    }

    public function store(Request $request)
    {
        // Honeypot anti-bots: campo oculto que una persona nunca llena
        if ($request->filled('website')) {
            return redirect()->route('agendar.gracias');
        }

        // Normaliza el número: solo dígitos y sin el código de país si lo escribieron
        $digitos = preg_replace('/\D/', '', (string) $request->input('telefono'));
        if (str_starts_with($digitos, '591') && strlen($digitos) > 8) {
            $digitos = substr($digitos, 3);
        }
        $request->merge(['telefono' => $digitos]);

        $hoy = SolicitudCita::ahora()->toDateString();

        $data = $request->validate([
            'nombre'          => 'required|string|min:3|max:150',
            'telefono'        => ['required', 'regex:/^[2-7]\d{6,7}$/'],
            'servicio_id'     => ['required', Rule::exists('servicios', 'id')->where('activo', true)],
            'consulta'        => 'required|string|min:5|max:1000',
            'fecha_preferida' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . $hoy,
                'before_or_equal:' . SolicitudCita::ahora()->addDays(SolicitudCita::DIAS_MAXIMOS)->toDateString(),
                function ($attr, $value, $fail) {
                    if (Carbon::hasFormat($value, 'Y-m-d') && Carbon::parse($value)->isSunday()) {
                        $fail('Los domingos no atendemos, elige otro día.');
                    }
                },
            ],
            'hora_preferida'  => [
                'required',
                Rule::in(SolicitudCita::horarios()),
                function ($attr, $value, $fail) use ($request, $hoy) {
                    if ($request->input('fecha_preferida') === $hoy && $value < $this->horaMinimaHoy()) {
                        $fail('Ese horario ya no está disponible hoy, elige uno más tarde.');
                    }
                },
            ],
        ], [
            'nombre.required'                 => 'Ingresa tu nombre.',
            'nombre.min'                      => 'Ingresa tu nombre completo.',
            'telefono.required'               => 'Ingresa tu número de teléfono.',
            'telefono.regex'                  => 'Ingresa un número boliviano válido (ej. 71234567).',
            'servicio_id.required'            => 'Selecciona el servicio que deseas.',
            'servicio_id.exists'              => 'El servicio seleccionado no está disponible.',
            'consulta.required'               => 'Escribe tu consulta.',
            'consulta.min'                    => 'Cuéntanos un poco más sobre tu consulta.',
            'fecha_preferida.required'        => 'Selecciona una fecha en el calendario.',
            'fecha_preferida.date_format'     => 'Selecciona una fecha válida en el calendario.',
            'fecha_preferida.after_or_equal'  => 'La fecha no puede ser anterior a hoy.',
            'hora_preferida.required'         => 'Selecciona la hora de tu cita.',
            'hora_preferida.in'               => 'Selecciona una hora válida.',
            'fecha_preferida.before_or_equal' => 'Solo puedes reservar hasta ' . SolicitudCita::DIAS_MAXIMOS . ' días adelante.',
        ]);

        SolicitudCita::create([
            'nombre'          => trim($data['nombre']),
            'telefono'        => '+591' . $data['telefono'],
            'servicio_id'     => $data['servicio_id'],
            'consulta'        => trim($data['consulta']),
            'fecha_preferida' => $data['fecha_preferida'],
            'hora_preferida'  => $data['hora_preferida'],
            'ip_address'      => $request->ip(),
        ]);

        return redirect()->route('agendar.gracias');
    }

    /** Primera hora (H:i) que todavía se puede reservar hoy, según la anticipación mínima */
    private function horaMinimaHoy(): string
    {
        $ahora  = SolicitudCita::ahora();
        $limite = $ahora->copy()->addMinutes(SolicitudCita::ANTICIPACION_MINUTOS);

        // Si la anticipación cruza la medianoche, hoy ya no queda ningún horario
        return $limite->isSameDay($ahora) ? $limite->format('H:i') : '24:00';
    }

    /** Servicios activos agrupados por categoría, en el orden de Servicio::CATEGORIAS */
    private function serviciosPorCategoria()
    {
        $orden = array_keys(Servicio::CATEGORIAS);

        return Servicio::activos()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'duracion_minutos'])
            ->groupBy('categoria')
            ->sortBy(fn ($items, $cat) => ($pos = array_search($cat, $orden)) === false ? PHP_INT_MAX : $pos);
    }

    public function gracias()
    {
        return view('agendar.gracias');
    }
}
