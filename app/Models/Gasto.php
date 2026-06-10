<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = [
        'descripcion',
        'monto',
        'categoria',
        'fecha',
        'metodo_pago',
        'notas',
        'sucursal_id',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'float',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function categorias(): array
    {
        return [
            'alimentacion'     => 'Alimentación',
            'limpieza'         => 'Limpieza',
            'papeleria'        => 'Papelería',
            'servicios_basicos'=> 'Servicios básicos',
            'mantenimiento'    => 'Mantenimiento',
            'compras'          => 'Compras / Insumos',
            'otros'            => 'Otros',
        ];
    }
}
