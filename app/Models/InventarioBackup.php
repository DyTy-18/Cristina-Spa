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
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
