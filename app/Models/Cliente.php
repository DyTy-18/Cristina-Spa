<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cliente extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'apellido', 'email', 'telefono', 'direccion', 'notas'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('clientes');
    }

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
