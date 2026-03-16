<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Campana extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'descripcion', 'activo', 'fecha_inicio', 'fecha_fin'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('campanas');
    }

    protected $table = 'campanas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'activo'       => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * Solo campañas activas dentro del rango de fechas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', today());
            });
    }
}
