<?php

namespace App\Services;

use App\Models\Cita;
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
        $body = ['cita' => $this->buildPayload($cita)];

        Log::debug('WppService → process-cita', $body);

        try {
            Http::withToken($this->token)
                ->timeout(5)
                ->post("{$this->baseUrl}/api/citas/citas/process-cita", $body);
        } catch (\Throwable $e) {
            Log::warning("WppService cita#{$cita->id}: {$e->getMessage()}");
        }
    }

    private function buildPayload(Cita $cita): array
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
