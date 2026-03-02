<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salida extends Model
{
    protected $fillable = [
        'codigo_barras',
        'unidades',
        'fecha',
        'destino',
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
