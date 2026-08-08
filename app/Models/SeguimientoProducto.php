<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoProducto extends Model
{
    protected $table = 'seguimiento_productos';

    protected $fillable = [
        'cita_id',
        'producto_catalogo_id',
        'producto_id',
        'nombre_personalizado',
    ];

    /** Nombre a mostrar: el del producto vinculado (inventario o catálogo legado), si no el personalizado del cliente. */
    public function getNombreAttribute(): string
    {
        return $this->producto?->nombre ?? $this->productoCatalogo?->nombre ?? $this->nombre_personalizado ?? '—';
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    /** Producto de inventario (fuente actual). */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /** Producto de catálogo legado (solo filas creadas antes de vender directo del inventario). */
    public function productoCatalogo()
    {
        return $this->belongsTo(ProductoCatalogo::class, 'producto_catalogo_id');
    }
}
