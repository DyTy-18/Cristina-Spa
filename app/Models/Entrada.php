<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrada extends Model
{
    protected $fillable = [
        'codigo_barras',
        'sucursal_id',
        'tipo_stock',
        'unidades',
        'fecha',
    ];

    protected $casts = [
        'fecha'    => 'date',
        'unidades' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codigo_barras', 'codigo_barras');
    }
}
