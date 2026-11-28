<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioBackup extends Model
{
    protected $fillable = [
        'user_id',
        'productos_count',
        'entradas_count',
        'salidas_count',
        'datos',
        'restaurado_at',
        'restaurado_por',
    ];

    protected $casts = [
        'datos'         => 'array',
        'restaurado_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restauradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restaurado_por');
    }
}
