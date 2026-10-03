<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SolicitudCita extends Model
{
    protected $table = 'solicitudes_cita';

    /** Roles que gestionan las solicitudes y reciben las notificaciones */
    public const ROLES_GESTION = ['admin', 'secretario', 'encargado', 'developer'];

    public const ESTADOS = ['pendiente', 'contactado', 'agendado', 'descartado'];

    /** Zona horaria del negocio (la app corre en UTC) */
    public const TZ = 'America/La_Paz';

    /** Horarios que se ofrecen: primera y última hora de inicio, cada N minutos */
    public const HORA_INICIO = '09:00';
    public const HORA_FIN = '19:00';
    public const INTERVALO_MINUTOS = 30;

    /** Anticipación mínima para reservar en el mismo día */
    public const ANTICIPACION_MINUTOS = 60;

    /** Días hacia adelante que se pueden reservar */
    public const DIAS_MAXIMOS = 60;

    protected $fillable = [
        'nombre',
        'telefono',
        'servicio_id',
        'consulta',
        'fecha_preferida',
        'hora_preferida',
        'estado',
        'atendido_por',
        'atendido_at',
        'ip_address',
    ];

    protected $casts = [
        'fecha_preferida' => 'date',
        'atendido_at'     => 'datetime',
    ];

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /** Horarios disponibles en formato H:i, ej. ['09:00', '09:30', ...] */
    public static function horarios(): array
    {
        $horarios = [];
        $hora = Carbon::createFromFormat('H:i', self::HORA_INICIO);
        $fin  = Carbon::createFromFormat('H:i', self::HORA_FIN);

        while ($hora <= $fin) {
            $horarios[] = $hora->format('H:i');
            $hora->addMinutes(self::INTERVALO_MINUTOS);
        }

        return $horarios;
    }

    /** Fecha y hora actual en La Paz */
    public static function ahora(): Carbon
    {
        return now(self::TZ);
    }

    /**
     * Cambia cuando llega una solicitud nueva o se modifica alguna.
     * La vista de solicitudes la compara en cada polling para saber si debe refrescarse.
     */
    public static function firma(): string
    {
        $fila = static::selectRaw('COUNT(*) as total, MAX(id) as ultimo, MAX(updated_at) as cambio')->first();

        return md5($fila->total . '|' . $fila->ultimo . '|' . $fila->cambio);
    }

    /** Ej. "15:30" */
    public function getHoraPreferidaCortaAttribute(): string
    {
        return substr((string) $this->hora_preferida, 0, 5);
    }

    /** Ej. "Sáb 04/10/2026 · 15:30" */
    public function getFechaPreferidaTextoAttribute(): string
    {
        $texto = ucfirst(str_replace('.', '', $this->fecha_preferida->locale('es')->isoFormat('ddd DD/MM/YYYY')));

        return $texto . ' · ' . $this->hora_preferida_corta;
    }

    /** Teléfono sin "+" para armar links de wa.me */
    public function getTelefonoWaAttribute(): string
    {
        return preg_replace('/\D/', '', $this->telefono);
    }
}
