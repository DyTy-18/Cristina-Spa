<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\WppSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WppService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.wpp.url', 'http://localhost:21465'), '/');
        $this->token   = config('services.wpp.token', '');
    }

    /**
     * Envía la cita al servidor WPP. El servidor decide qué hacer según el estado:
     *   confirmada  → mensaje inmediato de confirmación
     *   pendiente   → programa recordatorios 24h y 1h antes
     *   completada  → no_action
     *   cancelada   → no_action
     */
    public function notificarSegunEstado(Cita $cita): void
    {
        $this->enviar($cita, 'actualizada');
    }

    /** Avisa al servidor WPP que la cita fue eliminada, para que cancele recordatorios pendientes. */
    public function notificarEliminacion(Cita $cita): void
    {
        $this->enviar($cita, 'eliminada');
    }

    private function enviar(Cita $cita, string $accion): void
    {
        $body = ['cita' => $this->payloadCita($cita) + ['accion' => $accion]];

        Log::debug('WppService → process-cita', $body);

        try {
            $response = Http::withToken($this->token)
                ->timeout(5)
                ->post("{$this->baseUrl}/api/citas/citas/process-cita", $body);

            WppSyncLog::create([
                'direccion'   => 'saliente',
                'tipo'        => 'push',
                'cita_id'     => $cita->id,
                'payload'     => $body,
                'resultado'   => $response->successful() ? 'ok' : 'error',
                'status_code' => $response->status(),
                'mensaje'     => $response->successful() ? null : substr($response->body(), 0, 2000),
            ]);
        } catch (\Throwable $e) {
            Log::warning("WppService cita#{$cita->id}: {$e->getMessage()}");

            WppSyncLog::create([
                'direccion' => 'saliente',
                'tipo'      => 'push',
                'cita_id'   => $cita->id,
                'payload'   => $body,
                'resultado' => 'error',
                'mensaje'   => $e->getMessage(),
            ]);
        }
    }

    /** Payload de una cita en el formato compartido entre el push saliente y la consulta por rango de fechas. */
    public function payloadCita(Cita $cita): array
    {
        $cita->loadMissing(['cliente', 'citaServicios.servicio', 'citaServicios.empleado']);

        $servicios = $cita->citaServicios
            ->map(fn($cs) => $cs->servicio?->nombre)
            ->filter()
            ->values()
            ->toArray();

        $empleado = $cita->citaServicios
            ->map(fn($cs) => $cs->empleado ? trim("{$cs->empleado->nombre} {$cs->empleado->apellido}") : null)
            ->filter()
            ->unique()
            ->implode(', ');

        return [
            'id'      => $cita->id,
            'fecha'   => $cita->fecha->format('Y-m-d'),
            'hora'    => substr($cita->getRawOriginal('hora'), 0, 5),
            'estado'  => $cita->estado,
            'cliente' => [
                'nombre'   => $cita->cliente->nombre,
                'apellido' => $cita->cliente->apellido ?? '',
                'telefono' => preg_replace('/[^0-9]/', '', $cita->cliente->telefono ?? ''),
            ],
            'servicios' => $servicios,
            'empleado'  => $empleado ?: null,
            'notas'     => $cita->notas,
        ];
    }
}
