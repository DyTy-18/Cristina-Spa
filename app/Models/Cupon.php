<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'descripcion',
        'tipo',
        'valor',
        'usos_maximos',
        'fecha_vencimiento',
        'activo',
    ];

    protected $casts = [
        'valor'             => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'activo'            => 'boolean',
    ];

    public function usos()
    {
        return $this->hasMany(CuponUso::class);
    }

    public function getUsosActualesAttribute(): int
    {
        return $this->usos()->count();
    }

    public function getUsosRestantesAttribute(): ?int
    {
        if ($this->usos_maximos === null) return null;
        return max(0, $this->usos_maximos - $this->usos_actuales);
    }

    /** Estado: valido | agotado | vencido | inactivo */
    public function getEstadoAttribute(): string
    {
        if (! $this->activo) return 'inactivo';
        if ($this->fecha_vencimiento && Carbon::today()->gt($this->fecha_vencimiento)) return 'vencido';
        if ($this->usos_maximos !== null && $this->usos_actuales >= $this->usos_maximos) return 'agotado';
        return 'valido';
    }

    public function isValido(): bool
    {
        return $this->estado === 'valido';
    }

    /** Etiqueta del valor para mostrar */
    public function getValorLabelAttribute(): string
    {
        $v = (float) $this->valor;
        return $this->tipo === 'porcentaje'
            ? rtrim(rtrim(number_format($v, 2), '0'), '.') . '%'
            : 'Bs. ' . number_format($v, 2);
    }
}
