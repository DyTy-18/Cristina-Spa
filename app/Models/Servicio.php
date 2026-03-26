<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Servicio extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'precio', 'duracion_minutos', 'categoria', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('servicios');
    }

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion_minutos',
        'categoria',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Citas asociadas a este servicio
     */
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function comision()
    {
        return $this->hasOne(Comision::class);
    }

    public function comisionesEmpleado()
    {
        return $this->hasMany(ComisionEmpleado::class);
    }

    /**
     * Scope para servicios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Formato de duración legible
     */
    public function getDuracionFormateadaAttribute()
    {
        $horas = floor($this->duracion_minutos / 60);
        $minutos = $this->duracion_minutos % 60;
        
        if ($horas > 0 && $minutos > 0) {
            return "{$horas}h {$minutos}min";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$minutos}min";
        }
    }
}
