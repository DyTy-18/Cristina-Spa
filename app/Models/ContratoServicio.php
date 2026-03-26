<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratoServicio extends Model
{
    protected $table = 'contrato_servicios';

    protected $fillable = [
        'contrato_id', 'servicio_id', 'estado',
        'fecha_completado', 'cita_id', 'notas', 'orden',
    ];

    protected $casts = [
        'fecha_completado' => 'date',
    ];

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(ContratoPaquete::class, 'contrato_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }
}
