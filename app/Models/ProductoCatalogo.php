<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductoCatalogo extends Model
{
    protected $table = 'productos_catalogo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'producto_catalogo_servicio');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
