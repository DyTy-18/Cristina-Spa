<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Cita;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\Sucursal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Trait reutilizable para descontar stock de reventa cuando se venden
 * productos dentro de una cita, desde CitaController o ClienteController.
 */
trait VendeProductosReventa
{
    /**
     * Productos de reventa con stock, agrupados por sucursal (una lista por cada
     * sucursal activa), para que el selector de productos en el formulario se
     * actualice en el navegador cuando el usuario cambia la sucursal sin recargar
     * la página. La clave es el id de sucursal (string, por @json en el front).
     */
    protected function productosReventaPorSucursalJson(): Collection
    {
        return Sucursal::where('activo', true)->get()->mapWithKeys(function (Sucursal $s) {
            $lista = Producto::reventaConStock($s->id)->map(fn ($p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre,
                'precio' => $p->precio_venta ?? 0,
                'stock'  => (int) $p->stock_actual,
            ])->values();

            return [$s->id => $lista];
        });
    }
    /**
     * Cambia el estado de la cita y ajusta el stock de reventa si entra o sale de
     * "cancelada" (al cancelar se devuelven los productos; al reactivarla se vuelven a
     * descontar, validando stock). Si no hay stock, el estado no cambia.
     */
    protected function cambiarEstadoCita(Cita $cita, string $estado): void
    {
        DB::transaction(function () use ($cita, $estado) {
            $anterior = $cita->estado;
            $cita->update(['estado' => $estado]);

            if ($anterior !== $estado && in_array('cancelada', [$anterior, $estado], true)) {
                $productosData = $cita->citaProductos()->whereNotNull('producto_id')->get()
                    ->map(fn ($cp) => ['producto_id' => $cp->producto_id, 'cantidad' => $cp->cantidad]);

                $this->sincronizarVentaReventa($cita, $productosData, $cita->sucursal_id);
            }
        });
    }

    /**
     * Descuenta stock de reventa por los productos vendidos en la cita.
     * Primero revierte las salidas que esta misma cita hubiera generado antes (soporta
     * crear y editar, donde citaProductos se borra y recrea en cada guardado), recalcula
     * el stock disponible y valida antes de crear las nuevas salidas.
     */
    private function sincronizarVentaReventa(Cita $cita, Collection $productosData, ?int $sucursalId): void
    {
        Salida::where('cita_id', $cita->id)->where('tipo_stock', 'reventa')->delete();

        // Una cita cancelada no descuenta: sus productos vuelven al stock.
        if ($productosData->isEmpty() || $cita->estado === 'cancelada') {
            return;
        }

        $stockPorId = Producto::reventaConStock($sucursalId)
            ->whereIn('id', $productosData->pluck('producto_id'))
            ->keyBy('id');

        foreach ($productosData as $p) {
            $cantidad = (int) ($p['cantidad'] ?? 1);
            $producto = $stockPorId->get($p['producto_id']);

            if (! $producto || $cantidad > (int) $producto->stock_actual) {
                $nombre     = $producto->nombre ?? "producto #{$p['producto_id']}";
                $disponible = $producto->stock_actual ?? 0;
                throw ValidationException::withMessages([
                    'productos' => "Stock insuficiente para \"{$nombre}\": disponible {$disponible}, solicitado {$cantidad}.",
                ]);
            }
        }

        foreach ($productosData as $p) {
            $producto = $stockPorId->get($p['producto_id']);
            Salida::create([
                'producto_id' => $producto->id,
                'tipo_stock'  => 'reventa',
                'unidades'    => (int) ($p['cantidad'] ?? 1),
                'fecha'       => $cita->fecha,
                'cita_id'     => $cita->id,
            ]);
        }
    }
}
