<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'sucursal',
        'servicio',
        'mensaje',
        'estado',
        'ip_address',
        'pais',
        'pais_codigo',
    ];

    public function scopeNuevos($query)
    {
        return $query->where('estado', 'nuevo');
    }
}
