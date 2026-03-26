<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ContratoPago extends Model
{
    protected $table = 'contrato_pagos';

    protected $fillable = [
        'contrato_id', 'numero_cuota', 'monto',
        'fecha_vencimiento', 'fecha_pago', 'estado', 'metodo_pago', 'notas',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago'        => 'date',
        'monto'             => 'float',
    ];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(ContratoPaquete::class, 'contrato_id');
    }

    /**
     * Auto-marca como vencido si la fecha_vencimiento pasó y sigue pendiente.
     */
    public function getEstadoEfectivoAttribute(): string
    {
        if ($this->estado === 'pendiente' && $this->fecha_vencimiento && $this->fecha_vencimiento->isPast()) {
            return 'vencido';
        }
        return $this->estado;
    }
}
