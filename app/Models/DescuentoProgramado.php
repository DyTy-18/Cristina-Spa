<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DescuentoProgramado extends Model
{
    protected $table = 'descuentos_programados';

    protected $fillable = [
        'servicio_id',
        'descripcion',
        'porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'porcentaje'   => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'activo'       => 'boolean',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    /** Estado calculado: vigente | próximo | vencido | inactivo */
    public function getEstadoAttribute(): string
    {
        if (! $this->activo) return 'inactivo';
        $hoy = Carbon::today();
        if ($hoy->lt($this->fecha_inicio)) return 'próximo';
        if ($this->fecha_fin && $hoy->gt($this->fecha_fin)) return 'vencido';
        return 'vigente';
    }

    public function scopeVigentes($q)
    {
        return $q->where('activo', true)
                 ->where('fecha_inicio', '<=', now())
                 ->where(fn($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()));
    }
}
