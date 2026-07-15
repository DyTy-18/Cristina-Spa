<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoNota extends Model
{
    protected $table = 'seguimiento_notas';

    protected $fillable = [
        'cita_id',
        'user_id',
        'nota',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
