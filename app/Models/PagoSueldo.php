<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoSueldo extends Model
{
    protected $table = 'pagos_sueldos';

    protected $fillable = [
        'empleado_id',
        'tipo',
        'monto',
        'periodo_desde',
        'periodo_hasta',
        'fecha_pago',
        'metodo_pago',
        'notas',
        'sucursal_id',
        'user_id',
    ];

    protected $casts = [
        'fecha_pago'    => 'date',
        'periodo_desde' => 'date',
        'periodo_hasta' => 'date',
        'monto'         => 'float',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function tipos(): array
    {
        return [
            'sueldo'   => 'Sueldo',
            'comision' => 'Comisión',
            'adelanto' => 'Adelanto',
            'bono'     => 'Bono',
        ];
    }
}
