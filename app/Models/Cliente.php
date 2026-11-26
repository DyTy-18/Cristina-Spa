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
        'oculto',
        'sucursal_id',
        'empleado_exclusivo_id',
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
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function empleadoExclusivo()
    {
        return $this->belongsTo(Empleado::class, 'empleado_exclusivo_id');
    }

    protected static function booted(): void
    {
        // El cascade de la BD borra las filas pero no los archivos: se borran foto a foto.
        static::deleting(fn (Cliente $cliente) => $cliente->fotos()->get()->each->delete());
    }

    /** Álbum de fotos de resultados (subidas desde el seguimiento de sus citas). */
    public function fotos()
    {
        return $this->hasMany(SeguimientoFoto::class)->latest();
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * Links de recomendación generados por este cliente
     */
    public function recomendacionLinks()
    {
        return $this->hasMany(RecomendacionLink::class);
    }

    /**
     * Primer cupón de recomendación activo que tiene pendiente como referidor.
     */
    public function cuponRecomendacionActivo(): ?\App\Models\Cupon
    {
        $linkIds = $this->recomendacionLinks()->pluck('id');
        if ($linkIds->isEmpty()) return null;

        return \App\Models\Lead::whereIn('recomendacion_link_id', $linkIds)
            ->whereNotNull('cupon_referidor_id')
            ->with('cuponReferidor')
            ->get()
            ->pluck('cuponReferidor')
            ->filter(fn($c) => $c && $c->isValido())
            ->first();
    }

    /**
     * Nombre completo del cliente
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }
}
