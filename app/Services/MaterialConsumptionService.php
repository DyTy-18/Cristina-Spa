<?php

namespace App\Services;

use App\Models\AlertaStock;
use App\Models\Cita;
use App\Models\ConsumoMaterial;
use App\Models\Entrada;
use App\Models\Salida;

class MaterialConsumptionService
{
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
                // Idempotente: skip si ya se procesó esta cita para este material
                if (ConsumoMaterial::where('servicio_material_id', $material->id)
                                   ->where('cita_id', $cita->id)
                                   ->exists()) {
                    continue;
                }

                ConsumoMaterial::create([
                    'servicio_material_id' => $material->id,
                    'cita_id'              => $cita->id,
                ]);

                $totalConsumos = ConsumoMaterial::where('servicio_material_id', $material->id)->count();
                $usosPorUnidad = max(1, (int) ($material->usos_por_unidad ?? 1));

                if ($totalConsumos % $usosPorUnidad === 0) {
                    Salida::create([
                        'codigo_barras' => $material->producto->codigo_barras,
                        'unidades'      => 1,
                        'fecha'         => today(),
                        'destino'       => 'consumo_servicio',
                    ]);

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
                }
            }
        }
    }

    private function calcularStock(string $codigoBarras): int
    {
        $entradas = Entrada::where('codigo_barras', $codigoBarras)->sum('unidades');
        $salidas  = Salida::where('codigo_barras', $codigoBarras)->sum('unidades');
        return (int) ($entradas - $salidas);
    }
}
