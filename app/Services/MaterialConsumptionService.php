<?php

namespace App\Services;

use App\Models\AlertaStock;
use App\Models\Cita;
use App\Models\ConsumoMaterial;
use App\Models\Entrada;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\ServicioMaterial;
use Illuminate\Support\Facades\DB;

class MaterialConsumptionService
{
    /**
     * Called when a Cita is marked as 'completada'.
     * Creates ONE ConsumoMaterial per (cita, material) with usos_reales = 1
     * and adjusts inventory accordingly.
     *
     * Los usos se acumulan por sucursal: cada sucursal abre y agota sus propias
     * unidades del material, así una sucursal nunca descuenta stock por usos de otra.
     */
    public function procesarCita(Cita $cita): void
    {
        if ($cita->estado !== 'completada') {
            return;
        }

        $cita->load(['citaServicios.servicio.materiales.producto']);

        foreach ($cita->citaServicios as $citaServicio) {
            if (!$citaServicio->servicio) {
                continue;
            }

            foreach ($citaServicio->servicio->materiales as $material) {
                // Idempotente: skip if already processed for this cita
                if (ConsumoMaterial::where('servicio_material_id', $material->id)
                                   ->where('cita_id', $cita->id)
                                   ->exists()) {
                    continue;
                }

                DB::transaction(function () use ($material, $cita) {
                    // El delta se calcula antes de registrar el consumo (usa los usos ya acumulados).
                    $this->aplicarDelta($material, 0, 1, $cita->sucursal_id);

                    ConsumoMaterial::create([
                        'servicio_material_id' => $material->id,
                        'cita_id'              => $cita->id,
                        'usos_reales'          => 1,
                    ]);
                });
            }
        }
    }

    /**
     * Updates usos_reales for an existing ConsumoMaterial and adjusts inventory delta.
     * Safe to call multiple times — always computes the correct delta from current state.
     */
    public function actualizarUsos(ConsumoMaterial $consumo, int $usosNuevos): void
    {
        $usosViejos = $consumo->usos_reales ?? 1;

        if ($usosNuevos === $usosViejos) {
            return;
        }

        $material = ServicioMaterial::with('producto')->findOrFail($consumo->servicio_material_id);

        DB::transaction(function () use ($material, $consumo, $usosViejos, $usosNuevos) {
            $this->aplicarDelta($material, $usosViejos, $usosNuevos, Cita::whereKey($consumo->cita_id)->value('sucursal_id'));
            $consumo->update(['usos_reales' => $usosNuevos]);
        });
    }

    /**
     * Returns how many extra Salidas (positive) or reversed units (negative) would result
     * from changing usos_reales. Used for stock pre-validation before committing changes.
     */
    public function calcularSalidasExtra(ServicioMaterial $material, int $usosViejos, int $usosNuevos, ?int $sucursalId): int
    {
        $material->refresh();
        $usosPorUnidad = max(1, (int) ($material->usos_por_unidad ?? 1));
        $totalAntes    = $this->usosEnSucursal($material, $sucursalId);
        $totalDespues  = $totalAntes - $usosViejos + $usosNuevos;

        return intdiv($totalDespues, $usosPorUnidad) - intdiv($totalAntes, $usosPorUnidad);
    }

    /**
     * Returns how many uses remain in the unit currently open in the sucursal.
     * Example: usos_por_unidad=50, 47 usos acumulados en la sucursal → 3 uses left in current bottle.
     */
    public function calcularUsosRestantesEnUnidad(ServicioMaterial $material, ?int $sucursalId): int
    {
        $usosPorUnidad = max(1, (int) ($material->usos_por_unidad ?? 1));
        $consumidosEnUnidadActual = $this->usosEnSucursal($material, $sucursalId) % $usosPorUnidad;

        return $usosPorUnidad - $consumidosEnUnidadActual;
    }

    /**
     * Usos del material registrados en citas de la sucursal (consumos_material).
     * Es la base del cálculo de unidades consumidas: cada sucursal lleva su cuenta.
     */
    public function usosEnSucursal(ServicioMaterial $material, ?int $sucursalId): int
    {
        return (int) ConsumoMaterial::query()
            ->join('citas', 'citas.id', '=', 'consumos_material.cita_id')
            ->where('consumos_material.servicio_material_id', $material->id)
            ->when($sucursalId, fn ($q) => $q->where('citas.sucursal_id', $sucursalId), fn ($q) => $q->whereNull('citas.sucursal_id'))
            ->sum(DB::raw('COALESCE(consumos_material.usos_reales, 1)'));
    }

    /**
     * Producto del material en la sucursal de la cita (mismo código de barras).
     * El material del servicio apunta a la fila de una sucursal; cada sucursal
     * descuenta de su propio inventario. Null si esa sucursal no tiene el producto.
     */
    public function productoEnSucursal(ServicioMaterial $material, ?int $sucursalId): ?Producto
    {
        return $material->producto?->enSucursal($sucursalId);
    }

    /**
     * Stock técnico actual del producto (en su propia sucursal).
     */
    public function calcularStock(?Producto $producto): int
    {
        return $producto ? $producto->stock('tecnico') : 0;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Core delta logic: creates Salidas (or correction Entradas) as needed.
     * Uses the sucursal's accumulated uses (before this change is saved) so results
     * are always consistent across re-entries. Must run inside a transaction.
     */
    private function aplicarDelta(ServicioMaterial $material, int $usosViejos, int $usosNuevos, ?int $sucursalId): void
    {
        $producto = $this->productoEnSucursal($material, $sucursalId);

        // Bloquea el material para que dos citas simultáneas no calculen sobre el mismo total.
        $material = ServicioMaterial::lockForUpdate()->findOrFail($material->id);
        $usosPorUnidad  = max(1, (int) ($material->usos_por_unidad ?? 1));
        $totalAntes     = $this->usosEnSucursal($material, $sucursalId);
        $totalDespues   = $totalAntes - $usosViejos + $usosNuevos;

        $salidasAntes   = intdiv($totalAntes, $usosPorUnidad);
        $salidasDespues = intdiv($totalDespues, $usosPorUnidad);
        $nuevasSalidas  = $salidasDespues - $salidasAntes;


        if ($nuevasSalidas > 0 && $producto) {
            // Incremento: descontar del inventario de la sucursal de la cita
            for ($i = 0; $i < $nuevasSalidas; $i++) {
                Salida::create([
                    'producto_id' => $producto->id,
                    'tipo_stock'  => 'tecnico',
                    'unidades'    => 1,
                    'fecha'       => today(),
                    'destino'     => 'consumo_servicio',
                ]);
            }

            $stockActual = $this->calcularStock($producto);

            if ($stockActual <= $producto->stock_minimo) {
                $yaExiste = AlertaStock::where('producto_id', $producto->id)
                                      ->where('leida', false)
                                      ->exists();
                if (!$yaExiste) {
                    AlertaStock::create([
                        'producto_id'  => $producto->id,
                        'stock_actual' => $stockActual,
                    ]);
                }
            }
        } elseif ($nuevasSalidas < 0 && $producto) {
            // Reversa: devolver unidades al inventario como entrada de corrección
            for ($i = 0; $i < abs($nuevasSalidas); $i++) {
                Entrada::create([
                    'producto_id' => $producto->id,
                    'tipo_stock'  => 'tecnico',
                    'unidades'    => 1,
                    'fecha'       => today(),
                ]);
            }
        }

        // Total global solo informativo; el cálculo usa los usos de cada sucursal.
        $material->update(['total_usos_procesados' => max(0, (int) $material->total_usos_procesados - $usosViejos + $usosNuevos)]);
    }
}
