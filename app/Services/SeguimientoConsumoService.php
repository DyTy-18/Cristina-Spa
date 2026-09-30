<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Salida;
use App\Models\SeguimientoProducto;
use Illuminate\Support\Facades\DB;

/**
 * Descuenta del inventario técnico los productos de seguimiento según los gramos usados.
 *
 * Los gramos se acumulan por producto y sucursal: cada vez que el total usado alcanza
 * otro múltiplo del peso del producto se descuenta 1 unidad. Ej: peso 100 g, se usan
 * 60 g + 60 g → 120 g acumulados → 1 unidad descontada (quedan 20 g "abiertos").
 *
 * El cálculo se rehace desde cero en cada cambio, así agregar o quitar registros
 * (o cambiar el peso del producto) siempre deja el inventario consistente.
 */
class SeguimientoConsumoService
{
    public const DESTINO = 'Consumo seguimiento';

    public function recalcular(Producto $producto, ?int $sucursalId): void
    {
        if (! $producto->peso_gramos || $producto->peso_gramos <= 0) {
            return;
        }

        DB::transaction(function () use ($producto, $sucursalId) {
            $gramosTotales = $this->gramosUsados($producto, $sucursalId);

            // Pequeña tolerancia para que 3 × 33.33 g sobre 99.99 g cuente como unidad completa.
            $unidadesEsperadas = (int) floor(($gramosTotales + 0.0001) / (float) $producto->peso_gramos);

            $salidas = Salida::where('codigo_barras', $producto->codigo_barras)
                ->where('tipo_stock', 'tecnico')
                ->where('destino', self::DESTINO)
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId), fn ($q) => $q->whereNull('sucursal_id'))
                ->lockForUpdate()
                ->orderByDesc('id')
                ->get();

            $diferencia = $unidadesEsperadas - (int) $salidas->sum('unidades');

            if ($diferencia > 0) {
                Salida::create([
                    'codigo_barras' => $producto->codigo_barras,
                    'sucursal_id'   => $sucursalId,
                    'tipo_stock'    => 'tecnico',
                    'unidades'      => $diferencia,
                    'fecha'         => today(),
                    'destino'       => self::DESTINO,
                ]);
            } elseif ($diferencia < 0) {
                // Se quitaron gramos: devolver unidades anulando las salidas más recientes.
                $porDevolver = -$diferencia;
                foreach ($salidas as $salida) {
                    if ($porDevolver <= 0) {
                        break;
                    }
                    if ($salida->unidades <= $porDevolver) {
                        $porDevolver -= $salida->unidades;
                        $salida->delete();
                    } else {
                        $salida->update(['unidades' => $salida->unidades - $porDevolver]);
                        $porDevolver = 0;
                    }
                }
            }
        });
    }

    /** Gramos del producto usados en seguimientos de citas de la sucursal. */
    public function gramosUsados(Producto $producto, ?int $sucursalId): float
    {
        return (float) SeguimientoProducto::query()
            ->join('citas', 'citas.id', '=', 'seguimiento_productos.cita_id')
            ->where('seguimiento_productos.producto_id', $producto->id)
            ->when($sucursalId, fn ($q) => $q->where('citas.sucursal_id', $sucursalId), fn ($q) => $q->whereNull('citas.sucursal_id'))
            ->sum('seguimiento_productos.gramos');
    }
}
