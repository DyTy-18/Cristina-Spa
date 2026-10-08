<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class RegistroEnvio extends Model
{
    use MassPrunable;

    /** Días que se conserva el respaldo antes de borrarlo con model:prune. */
    public const DIAS_RETENCION = 365;

    protected $table = 'registro_envios';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'metodo',
        'ruta',
        'url',
        'ip',
        'datos',
        'status',
        'error',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(self::DIAS_RETENCION));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
