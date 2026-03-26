<?php

namespace Database\Seeders;

use App\Models\Paquete;
use App\Models\PaqueteNivel;
use App\Models\PaqueteServicio;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class PaquetesSeeder extends Seeder
{
    public function run(): void
    {
        // Niveles (ya creados por migración, solo los buscamos)
        $esmeralda = PaqueteNivel::where('nombre', 'Esmeralda')->first();
        $rubi      = PaqueteNivel::where('nombre', 'Rubí')->first();
        $diamante  = PaqueteNivel::where('nombre', 'Diamante')->first();

        // Servicios por nombre
        $s = Servicio::pluck('id', 'nombre');

        // Helper
        $crearPaquete = function (string $nombre, string $cat, ?PaqueteNivel $nivel, ?float $descGeneral, array $items) {
            if (Paquete::where('nombre', $nombre)->exists()) return;

            $paquete = Paquete::create([
                'nombre'            => $nombre,
                'categoria'         => $cat,
                'nivel_id'          => $nivel?->id,
                'descuento_general' => $descGeneral,
                'activo'            => true,
            ]);

            foreach ($items as $i => [$sNombre, $descS]) {
                $sid = Servicio::where('nombre', $sNombre)->value('id');
                if ($sid) {
                    PaqueteServicio::create([
                        'paquete_id'          => $paquete->id,
                        'servicio_id'         => $sid,
                        'descuento_porcentaje'=> $descS,
                        'orden'               => $i,
                    ]);
                }
            }
        };

        // ── NOVIA ──────────────────────────────────────────────────────────────
        // Esmeralda: básico (corte + peinado)
        $crearPaquete('Novia Esmeralda', 'novia', $esmeralda, null, [
            ['Corte de Cabello',  null],
            ['Peinado Especial',  null],
        ]);

        // Rubí: esmeralda + tinte + hidratante, 5% general
        $crearPaquete('Novia Rubí', 'novia', $rubi, 5, [
            ['Corte de Cabello',      null],
            ['Peinado Especial',      null],
            ['Tinte Completo',        null],
            ['Tratamiento Hidratante',null],
        ]);

        // Diamante: completo con balayage + keratina, 10% general
        $crearPaquete('Novia Diamante', 'novia', $diamante, 10, [
            ['Corte de Cabello',      null],
            ['Peinado Especial',      null],
            ['Balayage',              null],
            ['Tratamiento Keratina',  null],
            ['Spa Capilar',           null],
        ]);

        // ── QUINCEAÑERA ────────────────────────────────────────────────────────
        $crearPaquete('Quinceañera Esmeralda', 'quinceañera', $esmeralda, null, [
            ['Corte + Lavado',    null],
            ['Peinado Especial',  null],
        ]);

        $crearPaquete('Quinceañera Rubí', 'quinceañera', $rubi, 5, [
            ['Corte + Lavado',        null],
            ['Peinado Especial',      null],
            ['Mechas / Highlights',   null],
            ['Tratamiento Hidratante',null],
        ]);

        $crearPaquete('Quinceañera Diamante', 'quinceañera', $diamante, 12, [
            ['Corte + Lavado',        null],
            ['Peinado Especial',      null],
            ['Balayage',              null],
            ['Tratamiento Keratina',  null],
            ['Spa Capilar',           null],
        ]);

        // ── EVENTOS ────────────────────────────────────────────────────────────
        $crearPaquete('Eventos Esmeralda', 'eventos', $esmeralda, null, [
            ['Peinado Especial', null],
        ]);

        $crearPaquete('Eventos Rubí', 'eventos', $rubi, null, [
            ['Peinado Especial',       null],
            ['Tratamiento Hidratante', null],
        ]);

        $crearPaquete('Eventos Diamante', 'eventos', $diamante, 8, [
            ['Peinado Especial', null],
            ['Balayage',         null],
            ['Spa Capilar',      null],
        ]);
    }
}
