<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Producto extends Model
{
    protected $fillable = [
        'sucursal_id',
        'codigo_barras',
        'nombre',
        'marca',
        'linea',
        'uso',
        'costo',
        'precio_venta',
        'stock_minimo',
        'es_reventa',
        'tipo',
        'peso_gramos',
    ];

    public const TIPO_VITRINA     = 'vitrina';
    public const TIPO_SEGUIMIENTO = 'seguimiento';
    public const TIPO_GENERICO    = 'generico';

    public const TIPOS = [
        self::TIPO_VITRINA     => 'Vitrina',
        self::TIPO_SEGUIMIENTO => 'Seguimiento',
        self::TIPO_GENERICO    => 'Genérico',
    ];

    protected $casts = [
        'costo'        => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'integer',
        'es_reventa'   => 'boolean',
        'peso_gramos'  => 'decimal:2',
    ];

    /** El tipo solo existe en productos de reventa; los técnicos no tienen. */
    public function getTipoLabelAttribute(): string
    {
        return $this->es_reventa ? (self::TIPOS[$this->tipo] ?? 'Genérico') : 'Técnico';
    }

    protected static function booted(): void
    {
        // codigo_barras está copiado en los movimientos (referencia legible/legacy).
        static::updated(function (Producto $producto) {
            if ($producto->wasChanged('codigo_barras')) {
                Entrada::where('producto_id', $producto->id)->update(['codigo_barras' => $producto->codigo_barras]);
                Salida::where('producto_id', $producto->id)->update(['codigo_barras' => $producto->codigo_barras]);
            }
        });
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function entradas(): HasMany
    {
        return $this->hasMany(Entrada::class);
    }

    public function salidas(): HasMany
    {
        return $this->hasMany(Salida::class);
    }

    /** Productos de una sucursal; sin sucursal (modo global) no filtra. */
    public function scopeDeSucursal(Builder $query, ?int $sucursalId): Builder
    {
        return $query->when($sucursalId, fn ($q) => $q->where($q->qualifyColumn('sucursal_id'), $sucursalId));
    }

    /**
     * Expresión SQL de stock (entradas - salidas) de un pool para el producto de la
     * fila `$alias`. Cada producto pertenece a una sola sucursal, así que no hace
     * falta filtrar movimientos por sucursal.
     */
    public static function stockSql(string $tipoStock, string $alias = 'productos', string $extraEntradas = '', string $extraSalidas = ''): string
    {
        $tipoStock = $tipoStock === 'reventa' ? 'reventa' : 'tecnico';

        return "(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE entradas.producto_id = {$alias}.id AND tipo_stock = '{$tipoStock}'{$extraEntradas})"
             . " - (SELECT COALESCE(SUM(unidades),0) FROM salidas WHERE salidas.producto_id = {$alias}.id AND tipo_stock = '{$tipoStock}'{$extraSalidas})";
    }

    /** Stock actual del producto en un pool (técnico por defecto). */
    public function stock(string $tipoStock = 'tecnico'): int
    {
        return (int) $this->entradas()->where('tipo_stock', $tipoStock)->sum('unidades')
             - (int) $this->salidas()->where('tipo_stock', $tipoStock)->sum('unidades');
    }

    /**
     * El mismo producto (mismo código de barras) en otra sucursal. Los servicios
     * guardan materiales/productos asociados apuntando a la fila de una sucursal;
     * al consumir en una cita se usa la fila de la sucursal de la cita.
     */
    public function enSucursal(?int $sucursalId): ?self
    {
        if (! $sucursalId || $this->sucursal_id === $sucursalId) {
            return $this;
        }

        return static::where('sucursal_id', $sucursalId)
            ->where('codigo_barras', $this->codigo_barras)
            ->first();
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
     * Productos de reventa tipo vitrina (los que se venden al cliente) de la sucursal
     * indicada (todas si es null) con su stock actual de reventa.
     */
    public static function reventaConStock(?int $sucursalId = null): Collection
    {
        return static::query()
            ->select('productos.*')
            ->selectRaw(static::stockSql('reventa') . ' AS stock_actual')
            ->where('es_reventa', true)
            ->where('tipo', self::TIPO_VITRINA)
            ->deSucursal($sucursalId)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Productos de reventa tipo seguimiento (se aplican en servicio y se descuentan
     * por gramos) de la sucursal indicada, con su stock de reventa.
     */
    public static function seguimientoConStock(?int $sucursalId = null): Collection
    {
        return static::query()
            ->select('productos.*')
            ->selectRaw(static::stockSql('reventa') . ' AS stock_actual')
            ->where('es_reventa', true)
            ->where('tipo', self::TIPO_SEGUIMIENTO)
            ->deSucursal($sucursalId)
            ->orderBy('nombre')
            ->get();
    }
}
