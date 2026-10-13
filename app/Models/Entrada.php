<?php

namespace App\Models;

use App\Models\Concerns\MovimientoDeProducto;
use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    use MovimientoDeProducto;

    protected $fillable = [
        'codigo_barras',
        'sucursal_id',
        'producto_id',
        'tipo_stock',
        'unidades',
        'fecha',
    ];

    protected $casts = [
        'fecha'    => 'date',
        'unidades' => 'integer',
    ];
}
