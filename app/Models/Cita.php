<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cita extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'fecha', 'hora', 'precio_final', 'notas'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('citas');
    }

    protected $fillable = [
        'cliente_id',
        'sucursal_id',
        'campana_id',
        'fecha',
        'hora',
        'estado',
        'precio_final',
        'tipo_pago',
        'tipo_pago_2',
        'monto_2',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'precio_final' => 'decimal:2',
        'monto_2' => 'decimal:2',
    ];

    /** Monto correspondiente a tipo_pago cuando el pago está dividido (el resto del total). */
    public function getMontoTipoPago1Attribute(): float
    {
        return round((float) $this->precio_final - (float) ($this->monto_2 ?? 0), 2);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function campana()
    {
        return $this->belongsTo(Campana::class);
    }

    public function citaServicios()
    {
        return $this->hasMany(CitaServicio::class);
    }

    public function citaProductos()
    {
        return $this->hasMany(CitaProducto::class);
    }

    public function seguimientoNotas()
    {
        return $this->hasMany(SeguimientoNota::class)->latest();
    }

    public function seguimientoProductos()
    {
        return $this->hasMany(SeguimientoProducto::class);
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
