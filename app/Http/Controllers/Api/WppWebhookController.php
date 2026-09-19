<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\WppSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Webhook entrante: cambios que se originan en WPP (confirmación/cancelación del
 * cliente por WhatsApp, o una cita agendada/reprogramada por el bot) y que deben
 * reflejarse en Laravel.
 *
 * Importante: a diferencia de los controladores del panel admin, estas acciones
 * NO vuelven a llamar a WppService — el cambio ya se originó en WPP, así que
 * reenviarlo generaría un eco entre ambos sistemas.
 */
class WppWebhookController extends Controller
{
    /** PATCH /api/wpp/citas/{cita}/estado — el cliente confirma o cancela por WhatsApp. */
    public function updateEstado(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'estado' => 'required|in:confirmada,cancelada',
        ]);

        try {
            if ($cita->estado === 'completada') {
                throw ValidationException::withMessages([
                    'estado' => 'La cita ya fue completada, no se puede modificar desde WhatsApp.',
                ]);
            }

            $cita->update(['estado' => $data['estado']]);

            Log::info("WppWebhook: cita#{$cita->id} → estado {$data['estado']} (origen WPP)");

            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'webhook_estado',
                'cita_id'   => $cita->id,
                'payload'   => $data,
                'resultado' => 'ok',
            ]);
        } catch (\Throwable $e) {
            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'webhook_estado',
                'cita_id'   => $cita->id,
                'payload'   => $data,
                'resultado' => 'error',
                'mensaje'   => $e->getMessage(),
            ]);

            throw $e;
        }

        return response()->json(['id' => $cita->id, 'estado' => $cita->estado]);
    }

    /** POST /api/wpp/citas — el bot agenda una cita nueva. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'telefono'              => 'required|string|max:20',
            'nombre'                => 'nullable|string|max:100',
            'apellido'              => 'nullable|string|max:100',
            'fecha'                 => 'required|date',
            'hora'                  => 'required',
            'sucursal_id'           => 'nullable|exists:sucursales,id',
            'notas'                 => 'nullable|string',
            'servicios'             => 'required|array|min:1',
            'servicios.*'           => 'exists:servicios,id',
        ]);

        try {
            $cliente = $this->resolverCliente($data);
            $sucursalId = $data['sucursal_id'] ?? Sucursal::where('es_principal', true)->value('id');

            $servicios = Servicio::whereIn('id', $data['servicios'])->get();
            $precioTotal = $servicios->sum('precio');

            $cita = DB::transaction(function () use ($cliente, $sucursalId, $data, $servicios, $precioTotal) {
                $cita = Cita::create([
                    'cliente_id'   => $cliente->id,
                    'sucursal_id'  => $sucursalId,
                    'fecha'        => $data['fecha'],
                    'hora'         => $data['hora'],
                    'estado'       => 'pendiente',
                    'precio_final' => $precioTotal ?: null,
                    'notas'        => $data['notas'] ?? null,
                ]);

                foreach ($servicios as $servicio) {
                    CitaServicio::create([
                        'cita_id'         => $cita->id,
                        'servicio_id'     => $servicio->id,
                        'precio_unitario' => $servicio->precio,
                    ]);
                }

                return $cita;
            });

            Log::info("WppWebhook: cita#{$cita->id} creada por el bot para cliente#{$cliente->id}");

            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'webhook_crear',
                'cita_id'   => $cita->id,
                'payload'   => $data,
                'resultado' => 'ok',
            ]);
        } catch (\Throwable $e) {
            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'webhook_crear',
                'payload'   => $data,
                'resultado' => 'error',
                'mensaje'   => $e->getMessage(),
            ]);

            throw $e;
        }

        return response()->json(['id' => $cita->id, 'estado' => $cita->estado], 201);
    }

    /** PATCH /api/wpp/citas/{cita} — el bot reprograma fecha/hora de una cita existente. */
    public function reprogramar(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'hora'  => 'required',
        ]);

        try {
            if (in_array($cita->estado, ['completada', 'cancelada'], true)) {
                throw ValidationException::withMessages([
                    'fecha' => 'No se puede reprogramar una cita completada o cancelada.',
                ]);
            }

            $cita->update(['fecha' => $data['fecha'], 'hora' => $data['hora']]);

            Log::info("WppWebhook: cita#{$cita->id} reprogramada a {$data['fecha']} {$data['hora']} (origen WPP)");

            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'webhook_reprogramar',
                'cita_id'   => $cita->id,
                'payload'   => $data,
                'resultado' => 'ok',
            ]);
        } catch (\Throwable $e) {
            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'webhook_reprogramar',
                'cita_id'   => $cita->id,
                'payload'   => $data,
                'resultado' => 'error',
                'mensaje'   => $e->getMessage(),
            ]);

            throw $e;
        }

        return response()->json(['id' => $cita->id, 'fecha' => $cita->fecha->format('Y-m-d'), 'hora' => $data['hora']]);
    }

    /** Busca un cliente existente por teléfono (comparando solo dígitos) o crea uno nuevo. */
    private function resolverCliente(array $data): Cliente
    {
        $digitos = preg_replace('/[^0-9]/', '', $data['telefono']);

        $cliente = Cliente::where('telefono', 'like', "%{$digitos}%")->first();
        if ($cliente) {
            return $cliente;
        }

        return Cliente::create([
            'nombre'      => $data['nombre'] ?? 'Cliente WhatsApp',
            'apellido'    => $data['apellido'] ?? null,
            'telefono'    => $data['telefono'],
            'sucursal_id' => $data['sucursal_id'] ?? Sucursal::where('es_principal', true)->value('id'),
        ]);
    }
}
