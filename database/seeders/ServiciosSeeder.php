<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            [
                'nombre' => 'Corte de Cabello',
                'descripcion' => 'Corte personalizado adaptado a tu estilo y tipo de rostro',
                'precio' => 25.00,
                'duracion_minutos' => 45,
                'categoria' => 'cortes',
            ],
            [
                'nombre' => 'Corte + Lavado',
                'descripcion' => 'Corte de cabello con lavado y secado incluido',
                'precio' => 35.00,
                'duracion_minutos' => 60,
                'categoria' => 'cortes',
            ],
            [
                'nombre' => 'Tinte Completo',
                'descripcion' => 'Coloración completa con productos premium',
                'precio' => 80.00,
                'duracion_minutos' => 120,
                'categoria' => 'coloracion',
            ],
            [
                'nombre' => 'Mechas / Highlights',
                'descripcion' => 'Técnica de mechas para dar luz y dimensión',
                'precio' => 95.00,
                'duracion_minutos' => 150,
                'categoria' => 'coloracion',
            ],
            [
                'nombre' => 'Balayage',
                'descripcion' => 'Técnica de coloración natural y degradada',
                'precio' => 120.00,
                'duracion_minutos' => 180,
                'categoria' => 'coloracion',
            ],
            [
                'nombre' => 'Peinado Especial',
                'descripcion' => 'Peinado elegante para ocasiones especiales',
                'precio' => 45.00,
                'duracion_minutos' => 60,
                'categoria' => 'peinados',
            ],
            [
                'nombre' => 'Tratamiento Hidratante',
                'descripcion' => 'Tratamiento profundo de hidratación capilar',
                'precio' => 40.00,
                'duracion_minutos' => 45,
                'categoria' => 'tratamientos',
            ],
            [
                'nombre' => 'Tratamiento Keratina',
                'descripcion' => 'Alisado con keratina para cabello liso y brillante',
                'precio' => 150.00,
                'duracion_minutos' => 180,
                'categoria' => 'tratamientos',
            ],
            [
                'nombre' => 'Spa Capilar',
                'descripcion' => 'Experiencia relajante con masaje y aromaterapia',
                'precio' => 55.00,
                'duracion_minutos' => 60,
                'categoria' => 'spa',
            ],
            [
                'nombre' => 'Paquete Novia',
                'descripcion' => 'Peinado, maquillaje de prueba y día del evento',
                'precio' => 250.00,
                'duracion_minutos' => 240,
                'categoria' => 'eventos',
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::create($servicio);
        }
    }
}
