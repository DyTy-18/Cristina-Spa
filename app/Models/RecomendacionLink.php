<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecomendacionLink extends Model
{
    protected $table = 'recomendacion_links';

    protected $fillable = [
        'cliente_id',
        'token',
        'clicks',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'recomendacion_link_id');
    }

    /** Leads que ya se convirtieron en cliente */
    public function leadsConvertidos()
    {
        return $this->leads()->where('estado', 'convertido');
    }

    public function getUrlAttribute(): string
    {
        return route('recomendacion.form', $this->token);
    }

    public function getWhatsappUrlAttribute(): string
    {
        $telefono = preg_replace('/\D/', '', $this->cliente->telefono ?? '');
        // Bolivia: si no tiene código de país, agregamos 591
        if (strlen($telefono) <= 8) {
            $telefono = '591' . $telefono;
        }

        $nombre  = $this->cliente->nombre;
        $mensaje = urlencode(
            "¡Hola! 😊 *{$nombre}* te recomienda *Cristina Spa* — el spa y salón de belleza de referencia en La Paz desde 2006.\n\n"
            . "Usa su enlace especial y obtén *10% de descuento* en tu primera visita:\n"
            . $this->url
        );

        return "https://wa.me/{$telefono}?text={$mensaje}";
    }
}
