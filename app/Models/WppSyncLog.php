<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WppSyncLog extends Model
{
    protected $fillable = [
        'direccion',
        'tipo',
        'cita_id',
        'payload',
        'resultado',
        'status_code',
        'mensaje',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }
}
