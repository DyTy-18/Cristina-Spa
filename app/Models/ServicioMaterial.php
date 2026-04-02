<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioMaterial extends Model
{
    protected $table = 'servicio_materiales';

    protected $fillable = [
        'servicio_id',
        'producto_id',
        'cantidad',
        'unidad',
        'usos_por_unidad',
    ];

    protected $casts = [
        'cantidad'        => 'decimal:2',
        'usos_por_unidad' => 'integer',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function consumos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConsumoMaterial::class);
    }
}
