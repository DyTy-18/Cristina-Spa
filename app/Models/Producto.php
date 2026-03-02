<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $fillable = [
        'codigo_barras',
        'nombre',
        'marca',
        'linea',
        'costo',
        'stock_minimo',
    ];

    protected $casts = [
        'costo'        => 'decimal:2',
        'stock_minimo' => 'integer',
    ];

    public function entradas(): HasMany
    {
        return $this->hasMany(Entrada::class, 'codigo_barras', 'codigo_barras');
    }

    public function salidas(): HasMany
    {
        return $this->hasMany(Salida::class, 'codigo_barras', 'codigo_barras');
    }
}
