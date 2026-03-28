<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'sucursal',
        'servicio',
        'mensaje',
        'estado',
        'ip_address',
        'pais',
        'pais_codigo',
        'recomendacion_link_id',
        'cliente_id',
        'cita_id',
        'servicio_id',
        'cupon_referido_id',
        'cupon_referidor_id',
    ];

    public function recomendacionLink()
    {
        return $this->belongsTo(RecomendacionLink::class, 'recomendacion_link_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function servicioRel()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function cuponReferido()
    {
        return $this->belongsTo(Cupon::class, 'cupon_referido_id');
    }

    public function cuponReferidor()
    {
        return $this->belongsTo(Cupon::class, 'cupon_referidor_id');
    }

    public function scopeNuevos($query)
    {
        return $query->where('estado', 'nuevo');
    }

    public function scopeDeRecomendacion($query)
    {
        return $query->whereNotNull('recomendacion_link_id');
    }
}
