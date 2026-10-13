<?php

namespace App\Models;

use App\Models\Concerns\MovimientoDeProducto;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    use MovimientoDeProducto;

    protected $fillable = [
        'codigo_barras',
        'sucursal_id',
        'producto_id',
        'tipo_stock',
        'unidades',
        'fecha',
        'destino',
        'cita_id',
    ];

    protected $casts = [
        'fecha'    => 'date',
        'unidades' => 'integer',
    ];
}
