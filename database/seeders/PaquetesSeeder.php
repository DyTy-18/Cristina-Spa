<?php

namespace Database\Seeders;

use App\Models\Paquete;
use App\Models\PaqueteNivel;
use App\Models\PaqueteServicio;
use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaquetesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('paquete_servicios')->truncate();
        DB::table('paquetes')->truncate();
        DB::table('paquete_niveles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Niveles oficiales
        $express  = PaqueteNivel::create(['nombre' => 'Express',  'orden' => 1, 'color' => '#50C878', 'activo' => true]);
        $rubi     = PaqueteNivel::create(['nombre' => 'Rubí',     'orden' => 2, 'color' => '#9B111E', 'activo' => true]);
        $diamante = PaqueteNivel::create(['nombre' => 'Diamante', 'orden' => 3, 'color' => '#5B8DD9', 'activo' => true]);

        $crearPaquete = function (string $nombre, string $cat, ?PaqueteNivel $nivel, float $precioFijo, array $serviciosNombres) {
            $paquete = Paquete::create([
                'nombre'            => $nombre,
                'categoria'         => $cat,
                'nivel_id'          => $nivel?->id,
                'precio_fijo'       => $precioFijo,
                'descuento_general' => null,
                'activo'            => true,
            ]);

            foreach ($serviciosNombres as $i => $sNombre) {
                $sid = Servicio::where('nombre', $sNombre)->value('id');
                if ($sid) {
                    PaqueteServicio::create([
                        'paquete_id'           => $paquete->id,
                        'servicio_id'          => $sid,
                        'descuento_porcentaje' => null,
                        'orden'                => $i,
                    ]);
                }
            }
        };

        // ── NOVIAS ─────────────────────────────────────────────────────────────
        $crearPaquete('Novias Express', 'novia', $express, 500, [
            'Peinado de Novia',
            'Tratamiento Essencial (Shampoo + Acondicionador)',
            'Depilación Completa',
            'Pintado en Gel',
        ]);

        $crearPaquete('Novias Rubí', 'novia', $rubi, 1150, [
            'Peinado de Novia',
            'Maquillaje de Fiesta Sin Pestañas',
            'Pestañas 3D 5D 6D',
            'Depilación Completa',
            'Manicura',
            'Pedicura',
        ]);

        $crearPaquete('Novias Diamante', 'novia', $diamante, 1700, [
            'Peinado de Novia',
            'Maquillaje de Fiesta Sin Pestañas',
            'Pestañas 3D 5D 6D',
            'Depilación Completa',
            'Tratamiento Essencial (Shampoo + Acondicionador)',
            'Limpieza Facial',
            'Manicura',
            'Pedicura',
        ]);

        // ── QUINCEAÑERAS ───────────────────────────────────────────────────────
        $crearPaquete('Quinceañeras Express', 'quinceañera', $express, 693, [
            'Peinado de Novia',
            'Tratamiento de Lujo',
            'Maquillaje de Fiesta Sin Pestañas',
            'Pestañas 3D 5D 6D',
            'Depilación Axilas',
            'Depilación Facial',
            'Manicura',
            'Esmaltado Normal',
        ]);

        $crearPaquete('Quinceañeras Rubí', 'quinceañera', $rubi, 1035, [
            'Peinado de Novia',
            'Tratamiento de Lujo',
            'Maquillaje de Fiesta Sin Pestañas',
            'Pestañas 3D 5D 6D',
            'Depilación Axilas',
            'Depilación Facial',
            'Limpieza Facial',
            'Manicura',
            'Pedicura',
            'Esmaltado Normal',
        ]);

        $crearPaquete('Quinceañeras Diamante', 'quinceañera', $diamante, 1530, [
            'Peinado de Novia',
            'Tratamiento de Lujo',
            'Maquillaje de Fiesta Sin Pestañas',
            'Pestañas 3D 5D 6D',
            'Depilación Axilas',
            'Depilación Facial',
            'Depilación Pierna Completa',
            'Depilación Brazo',
            'Limpieza Facial',
            'Manicura',
            'Forrado Acrílico con Tips',
            'Pedicura',
            'Esmaltado Normal',
        ]);

        // ── OTROS ──────────────────────────────────────────────────────────────
        $crearPaquete('Relax Love', 'otro', null, 280, [
            'Lavado',
            'Tratamiento Essencial (Shampoo + Acondicionador)',
            'Planchado o Bucles',
        ]);
    }
}
