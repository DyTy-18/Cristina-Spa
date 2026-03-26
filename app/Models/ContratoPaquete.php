<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContratoPaquete extends Model
{
    protected $table = 'contratos_paquete';

    protected $fillable = [
        'cliente_id', 'paquete_id', 'fecha_inicio',
        'precio_total', 'tipo_pago', 'estado', 'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'precio_total' => 'float',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(Paquete::class);
    }

    public function contratoServicios(): HasMany
    {
        return $this->hasMany(ContratoServicio::class, 'contrato_id')->orderBy('orden');
    }

    public function contratoPagos(): HasMany
    {
        return $this->hasMany(ContratoPago::class, 'contrato_id')->orderBy('numero_cuota');
    }

    // ── Calculated accessors ──────────────────────────────────────────────────

    public function getMontoPagadoAttribute(): float
    {
        return (float) $this->contratoPagos->where('estado', 'pagado')->sum('monto');
    }

    public function getMontoPendienteAttribute(): float
    {
        return round($this->precio_total - $this->monto_pagado, 2);
    }

    public function getProgresoServiciosAttribute(): int
    {
        $total = $this->contratoServicios->count();
        if ($total === 0) return 0;
        $completados = $this->contratoServicios->where('estado', 'completado')->count();
        return (int) round($completados / $total * 100);
    }

    public function getProgresoPagosAttribute(): int
    {
        if ($this->precio_total == 0) return 100;
        return (int) min(100, round($this->monto_pagado / $this->precio_total * 100));
    }

    public function getServiciosCompletadosAttribute(): int
    {
        return $this->contratoServicios->where('estado', 'completado')->count();
    }

    public function getTotalServiciosAttribute(): int
    {
        return $this->contratoServicios->count();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConCliente($query, ?string $busqueda)
    {
        if (!$busqueda) return $query;
        return $query->whereHas('cliente', fn($q) => $q->where('nombre', 'like', "%$busqueda%")
            ->orWhere('apellido', 'like', "%$busqueda%"));
    }
}
