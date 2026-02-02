<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'cargo',
        'especialidad',
        'fecha_contratacion',
        'activo',
        'user_id',
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

    /**
     * Citas asignadas al empleado
     */
    public function citas()
    {
        return $this->hasMany(Cita::class);
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
