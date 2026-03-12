<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'campana_id',
        'fecha',
        'hora',
        'estado',
        'precio_final',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'precio_final' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function campana()
    {
        return $this->belongsTo(Campana::class);
    }

    public function citaServicios()
    {
        return $this->hasMany(CitaServicio::class);
    }

    /**
     * Scope para citas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para citas confirmadas
     */
    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmada');
    }

    /**
     * Scope para citas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha', today());
    }

    /**
     * Scope para próximas citas
     */
    public function scopeProximas($query)
    {
        return $query->where('fecha', '>=', today())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha')
            ->orderBy('hora');
    }
}
