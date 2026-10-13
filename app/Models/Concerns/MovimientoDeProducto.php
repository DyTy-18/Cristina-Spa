<?php

namespace App\Models\Concerns;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entradas y salidas pertenecen a un producto de una sucursal concreta.
 * codigo_barras y sucursal_id se copian siempre del producto, así un movimiento
 * nunca puede quedar registrado en una sucursal distinta a la de su producto.
 */
trait MovimientoDeProducto
{
    protected static function bootMovimientoDeProducto(): void
    {
        static::saving(function ($movimiento) {
            if (! $movimiento->producto_id) {
                throw new \LogicException('Un movimiento de inventario requiere producto_id.');
            }

            if ($movimiento->isDirty('producto_id') || ! $movimiento->codigo_barras || ! $movimiento->sucursal_id) {
                $producto = Producto::findOrFail($movimiento->producto_id);
                $movimiento->codigo_barras = $producto->codigo_barras;
                $movimiento->sucursal_id   = $producto->sucursal_id;
            }
        });
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
