<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumoMaterial extends Model
{
    protected $table = 'consumos_material';

    protected $fillable = ['servicio_material_id', 'cita_id'];

    public function servicioMaterial(): BelongsTo
    {
        return $this->belongsTo(ServicioMaterial::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }
}
