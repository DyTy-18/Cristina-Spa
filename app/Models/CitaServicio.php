<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaServicio extends Model
{
    protected $table = 'cita_servicios';

    protected $fillable = [
        'cita_id',
        'servicio_id',
        'empleado_id',
        'precio_unitario',
        'descuento_porcentaje',
    ];

    protected $casts = [
        'precio_unitario'      => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
    ];

    /** Precio después de aplicar el descuento. */
    public function getPrecioNetoAttribute(): ?float
    {
        if ($this->precio_unitario === null) return null;
        $desc = (float) ($this->descuento_porcentaje ?? 0);
        return round((float) $this->precio_unitario * (1 - $desc / 100), 2);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
