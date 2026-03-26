<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\ContratoPago;
use App\Models\ContratoPaquete;
use App\Models\ContratoServicio;
use App\Models\Paquete;
use Illuminate\Http\Request;

/**
 * Trait reutilizable para crear un contrato de paquete
 * desde CitaController o ClienteController.
 */
trait CreaContratoPaquete
{
    /**
     * Crea el contrato (con sus servicios y pagos) si el request lo solicita.
     * Devuelve el ContratoPaquete creado o null.
     */
    protected function crearContratoSiCorresponde(Request $request, int $clienteId): ?ContratoPaquete
    {
        if (!$request->boolean('crear_contrato') || !$request->filled('paquete_id')) {
            return null;
        }

        $paquete = Paquete::with('servicios')->find($request->paquete_id);
        if (!$paquete) return null;

        $contrato = ContratoPaquete::create([
            'cliente_id'   => $clienteId,
            'paquete_id'   => $paquete->id,
            'fecha_inicio' => $request->input('contrato_fecha_inicio', today()->toDateString()),
            'precio_total' => (float) $request->input('contrato_precio_total', $paquete->precio_total),
            'tipo_pago'    => $request->input('contrato_tipo_pago', 'completo'),
            'notas'        => $request->input('contrato_notas'),
            'estado'       => 'activo',
        ]);

        // Registros de servicio (uno por cada servicio del paquete)
        foreach ($paquete->servicios as $i => $s) {
            ContratoServicio::create([
                'contrato_id' => $contrato->id,
                'servicio_id' => $s->id,
                'estado'      => 'pendiente',
                'orden'       => $i,
            ]);
        }

        // Registrar pagos iniciales (adelantos)
        $pagos = $request->input('contrato_pagos', []);
        foreach ($pagos as $i => $p) {
            if (empty($p['monto'])) continue;
            ContratoPago::create([
                'contrato_id'  => $contrato->id,
                'numero_cuota' => $i + 1,
                'monto'        => (float) $p['monto'],
                'fecha_pago'   => $p['fecha_pago'] ?? today()->toDateString(),
                'metodo_pago'  => $p['metodo_pago'] ?? null,
                'estado'       => 'pagado',
            ]);
        }

        return $contrato;
    }

    /**
     * Reglas de validación opcionales para el bloque de contrato.
     * Agregar a las $rules existentes del controller.
     */
    protected function contratoValidationRules(): array
    {
        return [
            'crear_contrato'                      => 'nullable|boolean',
            'paquete_id'                          => 'nullable|exists:paquetes,id',
            'contrato_precio_total'               => 'nullable|numeric|min:0',
            'contrato_fecha_inicio'               => 'nullable|date',
            'contrato_notas'                      => 'nullable|string',
            'contrato_pagos'                      => 'nullable|array',
            'contrato_pagos.*.monto'              => 'nullable|numeric|min:0',
            'contrato_pagos.*.fecha_pago'         => 'nullable|date',
            'contrato_pagos.*.metodo_pago'        => 'nullable|string|max:50',
        ];
    }
}
