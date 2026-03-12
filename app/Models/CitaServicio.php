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
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

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
