<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Servicio;

class ServiciosSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('servicio_materiales')->truncate();
        DB::table('comisiones')->truncate();
        DB::table('comisiones_empleado')->truncate();
        DB::table('cita_servicios')->truncate();
        DB::table('servicios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $servicios = [
            // ── Peluquería ───────────────────────────────────────
            ['nombre' => 'Corte de Mujer',            'precio' => 250, 'duracion_minutos' => 45,  'categoria' => 'peluqueria'],
            ['nombre' => 'Corte de Varón',             'precio' => 100, 'duracion_minutos' => 30,  'categoria' => 'peluqueria'],
            ['nombre' => 'Corte flequillo',            'precio' => 80,  'duracion_minutos' => 15,  'categoria' => 'peluqueria'],

            // ── Peinados ─────────────────────────────────────────
            ['nombre' => 'Lavado',                     'precio' => 80,  'duracion_minutos' => 15,  'categoria' => 'peinados'],
            ['nombre' => 'Planchado o Bucles',         'precio' => 70,  'duracion_minutos' => 30,  'categoria' => 'peinados'],
            ['nombre' => 'Semi-recogido',               'precio' => 100, 'duracion_minutos' => 45,  'categoria' => 'peinados'],
            ['nombre' => 'Recogido',                   'precio' => 150, 'duracion_minutos' => 60,  'categoria' => 'peinados'],
            ['nombre' => 'Peinado de Novia',           'precio' => 160, 'duracion_minutos' => 90,  'categoria' => 'peinados'],

            // ── Coloración ───────────────────────────────────────
            ['nombre' => 'Retoque de Raíz',            'precio' => 310, 'duracion_minutos' => 90,  'categoria' => 'coloracion'],
            ['nombre' => 'Matizado con Masque Kérastase', 'precio' => 180, 'duracion_minutos' => 30, 'categoria' => 'coloracion'],
            ['nombre' => 'Matizado Con Tono Sobre Tono', 'precio' => 250, 'duracion_minutos' => 45, 'categoria' => 'coloracion'],
            ['nombre' => 'Tiger Eye (Sin Fondo)',       'precio' => 450, 'duracion_minutos' => 180, 'categoria' => 'coloracion'],
            ['nombre' => 'Baby Lights (Sin Fondo)',     'precio' => 450, 'duracion_minutos' => 180, 'categoria' => 'coloracion'],
            ['nombre' => 'Balayage Platinado',          'precio' => 750, 'duracion_minutos' => 240, 'categoria' => 'coloracion'],
            ['nombre' => 'Color Global',                'precio' => 500, 'duracion_minutos' => 90,  'categoria' => 'coloracion'],
            ['nombre' => 'Morena Iluminada (sin decoloración)', 'precio' => 700, 'duracion_minutos' => 180, 'categoria' => 'coloracion'],
            ['nombre' => 'Mechas Creativas Rubias',     'precio' => 300, 'duracion_minutos' => 120, 'categoria' => 'coloracion'],
            ['nombre' => 'Color de Fondo Para Cualquier Técnica', 'precio' => 180, 'duracion_minutos' => 60, 'categoria' => 'coloracion'],
            ['nombre' => 'Metal-Detox',                 'precio' => 0,   'duracion_minutos' => 45,  'categoria' => 'coloracion', 'descripcion' => 'Precio según evaluación previa'],

            // ── Alisado u Ondulación ─────────────────────────────
            ['nombre' => 'Alisado Definitivo',          'precio' => 750, 'duracion_minutos' => 180, 'categoria' => 'alisado'],
            ['nombre' => 'Ondulación o Permanente',     'precio' => 500, 'duracion_minutos' => 120, 'categoria' => 'alisado'],

            // ── Depilado con Cera ────────────────────────────────
            ['nombre' => 'Depilación Facial',           'precio' => 90,  'duracion_minutos' => 20,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Pierna Completa',  'precio' => 150, 'duracion_minutos' => 40,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Cuello',           'precio' => 50,  'duracion_minutos' => 10,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Axilas',           'precio' => 80,  'duracion_minutos' => 15,  'categoria' => 'depilacion'],
            ['nombre' => 'Perfilado de Cejas',          'precio' => 60,  'duracion_minutos' => 20,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Bozo',             'precio' => 40,  'duracion_minutos' => 10,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Espalda',          'precio' => 100, 'duracion_minutos' => 30,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Abdomen',          'precio' => 80,  'duracion_minutos' => 20,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Brasilera',        'precio' => 150, 'duracion_minutos' => 30,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Brazo',            'precio' => 120, 'duracion_minutos' => 30,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Completa',         'precio' => 500, 'duracion_minutos' => 90,  'categoria' => 'depilacion'],
            ['nombre' => 'Depilación Biquini',          'precio' => 100, 'duracion_minutos' => 20,  'categoria' => 'depilacion'],

            // ── Maquillaje, Cejas y Pestañas ─────────────────────
            ['nombre' => 'Maquillaje de Fiesta Sin Pestañas', 'precio' => 150, 'duracion_minutos' => 45, 'categoria' => 'maquillaje'],
            ['nombre' => 'Pestañas 3D 5D 6D',           'precio' => 80,  'duracion_minutos' => 20,  'categoria' => 'maquillaje'],
            ['nombre' => 'Rizado de Pestañas',           'precio' => 120, 'duracion_minutos' => 45,  'categoria' => 'maquillaje'],
            ['nombre' => 'Laminado de Cejas',            'precio' => 100, 'duracion_minutos' => 45,  'categoria' => 'maquillaje'],
            ['nombre' => 'Rizado de Pestañas y Laminado de Cejas', 'precio' => 200, 'duracion_minutos' => 90, 'categoria' => 'maquillaje'],

            // ── Pies y Manos ─────────────────────────────────────
            ['nombre' => 'Manicura',                    'precio' => 60,  'duracion_minutos' => 30,  'categoria' => 'pies_manos'],
            ['nombre' => 'Pedicura',                    'precio' => 80,  'duracion_minutos' => 45,  'categoria' => 'pies_manos'],
            ['nombre' => 'Esmaltado Normal',             'precio' => 30,  'duracion_minutos' => 20,  'categoria' => 'pies_manos'],
            ['nombre' => 'Pintado en Gel',               'precio' => 120, 'duracion_minutos' => 45,  'categoria' => 'pies_manos'],

            // ── Extensiones de Uñas ──────────────────────────────
            ['nombre' => 'Forrado de Uñas Técnica Dipping', 'precio' => 80,  'duracion_minutos' => 45,  'categoria' => 'extensiones'],
            ['nombre' => 'Forrado Acrílico con Tips',    'precio' => 160, 'duracion_minutos' => 60,  'categoria' => 'extensiones'],
            ['nombre' => 'Uñas Esculpidas con Acrílico', 'precio' => 200, 'duracion_minutos' => 90,  'categoria' => 'extensiones'],
            ['nombre' => 'Técnica Soft Gel',             'precio' => 200, 'duracion_minutos' => 60,  'categoria' => 'extensiones'],

            // ── Spa ──────────────────────────────────────────────
            ['nombre' => 'Limpieza Facial',              'precio' => 150, 'duracion_minutos' => 60,  'categoria' => 'spa'],
            ['nombre' => 'Limpieza con Micro-Dermo Abrasión', 'precio' => 250, 'duracion_minutos' => 75, 'categoria' => 'spa'],
            ['nombre' => 'Masaje Relajante',             'precio' => 150, 'duracion_minutos' => 60,  'categoria' => 'spa'],
            ['nombre' => 'Exfoliación Corporal',         'precio' => 180, 'duracion_minutos' => 45,  'categoria' => 'spa'],
            ['nombre' => 'Envolvimiento Corporal',       'precio' => 180, 'duracion_minutos' => 60,  'categoria' => 'spa'],

            // ── Tratamientos Capilares ───────────────────────────
            ['nombre' => 'Tratamiento de Lujo',          'precio' => 650, 'duracion_minutos' => 60,  'categoria' => 'tratamientos'],
            ['nombre' => 'Thermo Blindaje Capilar',      'precio' => 500, 'duracion_minutos' => 60,  'categoria' => 'tratamientos'],
            ['nombre' => 'Tratamiento Nutrición Ampolla Kérastase', 'precio' => 350, 'duracion_minutos' => 30, 'categoria' => 'tratamientos'],
            ['nombre' => 'Tratamiento 24 Kilates Aurora Botania', 'precio' => 350, 'duracion_minutos' => 45, 'categoria' => 'tratamientos'],
            ['nombre' => 'Tratamiento Express Intenso (Shampoo + Mascarilla)', 'precio' => 200, 'duracion_minutos' => 20, 'categoria' => 'tratamientos'],
            ['nombre' => 'Tratamiento Essencial (Shampoo + Acondicionador)', 'precio' => 150, 'duracion_minutos' => 15, 'categoria' => 'tratamientos'],
        ];

        foreach ($servicios as $data) {
            Servicio::create(array_merge(['activo' => true, 'descripcion' => null], $data));
        }
    }
}
