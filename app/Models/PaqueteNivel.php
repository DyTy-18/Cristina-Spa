<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaqueteNivel extends Model
{
    protected $table = 'paquete_niveles';

    protected $fillable = ['nombre', 'orden', 'color', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function paquetes(): HasMany
    {
        return $this->hasMany(Paquete::class, 'nivel_id');
    }
}
