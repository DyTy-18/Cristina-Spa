<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionEmpleado extends Model
{
    protected $table = 'comisiones_empleado';

    protected $fillable = ['empleado_id', 'servicio_id', 'porcentaje', 'activo'];

    protected $casts = [
        'activo'     => 'boolean',
        'porcentaje' => 'decimal:2',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
