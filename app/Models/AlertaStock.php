<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaStock extends Model
{
    protected $table = 'alertas_stock';

    protected $fillable = ['producto_id', 'stock_actual', 'leida'];

    protected $casts = ['leida' => 'boolean'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function getMensajeAttribute(): string
    {
        $p = $this->producto;
        $marca = $p->marca ? " ({$p->marca})" : '';
        return "⚠️ Stock bajo: {$p->nombre}{$marca}\nStock actual: {$this->stock_actual} unidades\nMínimo recomendado: {$p->stock_minimo}";
    }
}
