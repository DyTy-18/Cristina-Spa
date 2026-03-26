<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paquete extends Model
{
    protected $fillable = ['nombre', 'categoria', 'nivel_id', 'descripcion', 'descuento_general', 'activo'];

    protected $casts = [
        'activo'            => 'boolean',
        'descuento_general' => 'float',
    ];

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(PaqueteNivel::class, 'nivel_id');
    }

    public function paqueteServicios(): HasMany
    {
        return $this->hasMany(PaqueteServicio::class)->orderBy('orden');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'paquete_servicios')
                    ->withPivot(['descuento_porcentaje', 'orden'])
                    ->orderByPivot('orden');
    }

    /**
     * Precio total del paquete aplicando descuentos:
     * - Usa descuento_porcentaje del servicio si está definido.
     * - Si no, aplica descuento_general del paquete.
     */
    public function getPrecioTotalAttribute(): float
    {
        $descGeneral = (float) ($this->descuento_general ?? 0);

        return $this->servicios->sum(function ($s) use ($descGeneral) {
            $precio = (float) $s->precio;
            $descS  = $s->pivot->descuento_porcentaje !== null
                        ? (float) $s->pivot->descuento_porcentaje
                        : $descGeneral;
            return round($precio * (1 - $descS / 100), 2);
        });
    }

    /**
     * Precio original sin ningún descuento.
     */
    public function getPrecioBaseAttribute(): float
    {
        return $this->servicios->sum(fn($s) => (float) $s->precio);
    }

    /**
     * Ahorro total vs precio base.
     */
    public function getAhorroAttribute(): float
    {
        return round($this->precio_base - $this->precio_total, 2);
    }
}
