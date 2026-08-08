<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductoCatalogo extends Model
{
    protected $table = 'productos_catalogo';

    protected $fillable = [
        'codigo_barras',
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

    /** Producto de inventario que respalda esta fila del catálogo (null en filas legacy). */
    public function productoInventario(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'codigo_barras', 'codigo_barras');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
