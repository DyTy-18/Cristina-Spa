<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Empleado extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'apellido', 'telefono', 'cargo', 'especialidad', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('empleados');
    }

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'cargo',
        'especialidad',
        'fecha_contratacion',
        'activo',
        'user_id',
        'clave_acceso',
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Usuario asociado al empleado
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function citaServicios()
    {
        return $this->hasMany(CitaServicio::class);
    }

    public function comisiones()
    {
        return $this->hasMany(ComisionEmpleado::class);
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'empleado_sucursal');
    }

    /**
     * Nombre completo del empleado
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    /**
     * Scope para empleados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para estilistas
     */
    public function scopeEstilistas($query)
    {
        return $query->where('cargo', 'estilista');
    }
}
