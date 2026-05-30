<?php

namespace App\Services;

use App\Models\AlertaStock;
use App\Models\Cita;
use App\Models\ConsumoMaterial;
use App\Models\Entrada;
use App\Models\Salida;
use App\Models\ServicioMaterial;

class MaterialConsumptionService
{
    /**
     * Called when a Cita is marked as 'completada'.
     * Creates ONE ConsumoMaterial per (cita, material) with usos_reales = 1
     * and adjusts inventory accordingly.
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

                ConsumoMaterial::create([
                    'servicio_material_id' => $material->id,
                    'cita_id'              => $cita->id,
                    'usos_reales'          => 1,
                ]);

                $this->aplicarDelta($material, 0, 1);
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
        $this->aplicarDelta($material, $usosViejos, $usosNuevos);
        $consumo->update(['usos_reales' => $usosNuevos]);
    }

    /**
     * Returns how many extra Salidas (positive) or reversed units (negative) would result
     * from changing usos_reales. Used for stock pre-validation before committing changes.
     */
    public function calcularSalidasExtra(ServicioMaterial $material, int $usosViejos, int $usosNuevos): int
    {
        $material->refresh();
        $usosPorUnidad = max(1, (int) ($material->usos_por_unidad ?? 1));
        $totalAntes    = (int) $material->total_usos_procesados;
        $totalDespues  = $totalAntes - $usosViejos + $usosNuevos;

        return intdiv($totalDespues, $usosPorUnidad) - intdiv($totalAntes, $usosPorUnidad);
    }

    /**
     * Returns how many uses remain in the currently open unit.
     * Example: usos_por_unidad=50, total_usos_procesados=47 → 3 uses left in current bottle.
     */
    public function calcularUsosRestantesEnUnidad(ServicioMaterial $material): int
    {
        $usosPorUnidad = max(1, (int) ($material->usos_por_unidad ?? 1));
        $consumidosEnUnidadActual = (int) $material->total_usos_procesados % $usosPorUnidad;

        return $usosPorUnidad - $consumidosEnUnidadActual;
    }

    /**
     * Returns the current stock for a product by its barcode.
     */
    public function calcularStock(string $codigoBarras): int
    {
        $entradas = Entrada::where('codigo_barras', $codigoBarras)->sum('unidades');
        $salidas  = Salida::where('codigo_barras', $codigoBarras)->sum('unidades');

        return (int) ($entradas - $salidas);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Core delta logic: adjusts total_usos_procesados and creates Salidas as needed.
     * Uses the material's running total so results are always consistent across re-entries.
     */
    private function aplicarDelta(ServicioMaterial $material, int $usosViejos, int $usosNuevos): void
    {
        $material->refresh();
        $usosPorUnidad  = max(1, (int) ($material->usos_por_unidad ?? 1));
        $totalAntes     = (int) $material->total_usos_procesados;
        $totalDespues   = $totalAntes - $usosViejos + $usosNuevos;

        $salidasAntes   = intdiv($totalAntes, $usosPorUnidad);
        $salidasDespues = intdiv($totalDespues, $usosPorUnidad);
        $nuevasSalidas  = $salidasDespues - $salidasAntes;


        if ($nuevasSalidas > 0 && $material->producto) {
            // Incremento: descontar del inventario
            for ($i = 0; $i < $nuevasSalidas; $i++) {
                Salida::create([
                    'codigo_barras' => $material->producto->codigo_barras,
                    'unidades'      => 1,
                    'fecha'         => today(),
                    'destino'       => 'consumo_servicio',
                ]);
            }

            $stockActual = $this->calcularStock($material->producto->codigo_barras);

            if ($stockActual <= $material->producto->stock_minimo) {
                $yaExiste = AlertaStock::where('producto_id', $material->producto_id)
                                      ->where('leida', false)
                                      ->exists();
                if (!$yaExiste) {
                    AlertaStock::create([
                        'producto_id'  => $material->producto_id,
                        'stock_actual' => $stockActual,
                    ]);
                }
            }
        } elseif ($nuevasSalidas < 0 && $material->producto) {
            // Reversa: devolver unidades al inventario como entrada de corrección
            for ($i = 0; $i < abs($nuevasSalidas); $i++) {
                Entrada::create([
                    'codigo_barras' => $material->producto->codigo_barras,
                    'unidades'      => 1,
                    'fecha'         => today(),
                ]);
            }
        }

        $material->update(['total_usos_procesados' => max(0, $totalDespues)]);
    }
}
