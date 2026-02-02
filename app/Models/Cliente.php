<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'fecha_nacimiento',
        'direccion',
        'notas',
        'user_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    /**
     * Usuario asociado al cliente (si tiene cuenta)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Citas del cliente
     */
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * Nombre completo del cliente
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }
}
