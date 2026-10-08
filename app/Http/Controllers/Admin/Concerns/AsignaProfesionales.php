<?php

namespace App\Http\Controllers\Admin\Concerns;

/**
 * Trait reutilizable para la sección "Profesionales" de los formularios de cita,
 * desde CitaController o ClienteController.
 */
trait AsignaProfesionales
{
    /**
     * Convierte las filas profesionales[*][empleado_id|servicio_id] en un mapa
     * [servicio_id => [empleado_id, ...]]. Un mismo servicio puede tener varios
     * profesionales; los pares repetidos se ignoran.
     */
    protected function empleadosPorServicio(array $profesionales): array
    {
        $mapa = [];

        foreach ($profesionales as $prof) {
            if (empty($prof['empleado_id']) || empty($prof['servicio_id'])) {
                continue;
            }

            $mapa[$prof['servicio_id']][(int) $prof['empleado_id']] = (int) $prof['empleado_id'];
        }

        return array_map('array_values', $mapa);
    }
}
