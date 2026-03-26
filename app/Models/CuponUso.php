<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuponUso extends Model
{
    protected $table = 'cupon_usos';

    protected $fillable = [
        'cupon_id',
        'cita_id',
        'cliente_id',
        'notas',
    ];

    public function cupon()
    {
        return $this->belongsTo(Cupon::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
