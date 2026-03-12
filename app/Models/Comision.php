<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    protected $table = 'comisiones';

    protected $fillable = ['servicio_id', 'porcentaje', 'activo'];

    protected $casts = [
        'activo'     => 'boolean',
        'porcentaje' => 'decimal:2',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
