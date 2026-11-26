<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SeguimientoFoto extends Model
{
    protected $table = 'seguimiento_fotos';

    /** Disco privado: las fotos solo se sirven a usuarios autenticados (ver SeguimientoFotoController). */
    public const DISCO = 'local';

    protected $fillable = [
        'cliente_id',
        'cita_id',
        'user_id',
        'ruta',
        'descripcion',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (SeguimientoFoto $foto) => Storage::disk(self::DISCO)->delete($foto->ruta));
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return route('admin.seguimiento-fotos.ver', $this);
    }
}
