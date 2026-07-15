<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoProducto extends Model
{
    protected $table = 'seguimiento_productos';

    protected $fillable = [
        'cita_id',
        'producto_catalogo_id',
        'nombre_personalizado',
    ];

    /** Nombre a mostrar: el del catálogo si está vinculado, si no el personalizado del cliente. */
    public function getNombreAttribute(): string
    {
        return $this->producto?->nombre ?? $this->nombre_personalizado ?? '—';
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function producto()
    {
        return $this->belongsTo(ProductoCatalogo::class, 'producto_catalogo_id');
    }
}
