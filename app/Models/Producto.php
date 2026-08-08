<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Producto extends Model
{
    protected $fillable = [
        'codigo_barras',
        'nombre',
        'marca',
        'linea',
        'uso',
        'costo',
        'precio_venta',
        'stock_minimo',
        'es_reventa',
    ];

    protected $casts = [
        'costo'        => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'integer',
        'es_reventa'   => 'boolean',
    ];

    public function entradas(): HasMany
    {
        return $this->hasMany(Entrada::class, 'codigo_barras', 'codigo_barras');
    }

    public function salidas(): HasMany
    {
        return $this->hasMany(Salida::class, 'codigo_barras', 'codigo_barras');
    }

    public function servicioMateriales(): HasMany
    {
        return $this->hasMany(ServicioMaterial::class);
    }

    /** Servicios que recomiendan/usan este producto (reventa). */
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'producto_catalogo_servicio', 'producto_id', 'servicio_id');
    }

    /**
     * Productos de reventa con su stock actual (SUM entradas - SUM salidas, tipo_stock=reventa),
     * respetando la sucursal activa cuando se indica. Mismo cálculo que
     * InventarioController::productosConStock('reventa').
     */
    public static function reventaConStock(?int $sucursalId = null): Collection
    {
        $sidSql = $sucursalId ? " AND sucursal_id = {$sucursalId}" : '';

        $query = static::query()
            ->select('productos.*')
            ->selectRaw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = productos.codigo_barras AND tipo_stock = 'reventa'{$sidSql})
                       - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = productos.codigo_barras AND tipo_stock = 'reventa'{$sidSql}) AS stock_actual")
            ->where('es_reventa', true);

        if ($sucursalId) {
            $query->where(function ($qb) use ($sucursalId) {
                $qb->whereExists(function ($sub) use ($sucursalId) {
                    $sub->selectRaw('1')->from('entradas')
                        ->whereColumn('entradas.codigo_barras', 'productos.codigo_barras')
                        ->where('entradas.sucursal_id', $sucursalId)
                        ->where('entradas.tipo_stock', 'reventa');
                })->orWhereExists(function ($sub) use ($sucursalId) {
                    $sub->selectRaw('1')->from('salidas')
                        ->whereColumn('salidas.codigo_barras', 'productos.codigo_barras')
                        ->where('salidas.sucursal_id', $sucursalId)
                        ->where('salidas.tipo_stock', 'reventa');
                });
            });
        }

        return $query->orderBy('nombre')->get();
    }
}
