<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaProducto extends Model
{
    protected $table = 'cita_productos';

    protected $fillable = [
        'cita_id',
        'producto_catalogo_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descuento_porcentaje',
    ];

    protected $casts = [
        'cantidad'              => 'integer',
        'precio_unitario'       => 'decimal:2',
        'descuento_porcentaje'  => 'decimal:2',
    ];

    /** Precio unitario después de aplicar el descuento. */
    public function getPrecioNetoAttribute(): ?float
    {
        if ($this->precio_unitario === null) return null;
        $desc = (float) ($this->descuento_porcentaje ?? 0);
        return round((float) $this->precio_unitario * (1 - $desc / 100), 2);
    }

    /** Precio neto multiplicado por la cantidad. */
    public function getPrecioTotalAttribute(): ?float
    {
        if ($this->precio_neto === null) return null;
        return round($this->precio_neto * $this->cantidad, 2);
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
