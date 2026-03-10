<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClienteEjemploSeeder extends Seeder
{
    /**
     * Valentina Salinas — cliente fiel con un año completo de recorrido.
     * Demuestra el historial, el timeline y los KPIs del panel.
     */
    public function run(): void
    {
        // Crear la cliente (o recuperar si ya existe)
        $cliente = Cliente::firstOrCreate(
            ['email' => 'valentina.salinas@gmail.com'],
            [
                'nombre'           => 'Valentina',
                'apellido'         => 'Salinas',
                'telefono'         => '71234567',
                'fecha_nacimiento' => '1991-04-12',
                'direccion'        => 'Zona Sur, La Paz',
                'notas'            => 'Prefiere productos sin sulfatos. Alérgica al amoniaco. Siempre pide café antes de empezar.',
            ]
        );

        // Obtener IDs de servicios por nombre
        $servicios = Servicio::pluck('id', 'nombre');

        // Viaje completo — Enero 2025 a Febrero 2026
        $visitas = [
            [
                'fecha'        => '2025-01-15',
                'hora'         => '10:00',
                'servicio'     => 'Corte de Cabello',
                'precio_final' => 25.00,
                'estado'       => 'completada',
                'notas'        => 'Primera visita del año. Quiere renovar su look con un corte bob.',
            ],
            [
                'fecha'        => '2025-02-08',
                'hora'         => '11:30',
                'servicio'     => 'Tratamiento Hidratante',
                'precio_final' => 40.00,
                'estado'       => 'completada',
                'notas'        => 'Cabello muy reseco por el sol del verano. Excelente resultado.',
            ],
            [
                'fecha'        => '2025-03-20',
                'hora'         => '09:00',
                'servicio'     => 'Tinte Completo',
                'precio_final' => 80.00,
                'estado'       => 'completada',
                'notas'        => 'Cambio de color a marrón caramelo. Le encantó el tono.',
            ],
            [
                'fecha'        => '2025-04-03',
                'hora'         => '15:00',
                'servicio'     => 'Corte de Cabello',
                'precio_final' => 25.00,
                'estado'       => 'completada',
                'notas'        => 'Recorte de puntas para mantener el bob.',
            ],
            [
                'fecha'        => '2025-04-28',
                'hora'         => '10:00',
                'servicio'     => 'Spa Capilar',
                'precio_final' => 55.00,
                'estado'       => 'cancelada',
                'notas'        => 'Canceló por viaje de último momento. Reagendó para mayo.',
            ],
            [
                'fecha'        => '2025-05-10',
                'hora'         => '10:00',
                'servicio'     => 'Spa Capilar',
                'precio_final' => 55.00,
                'estado'       => 'completada',
                'notas'        => 'Relajación antes del viaje. Quedó muy contenta con el masaje.',
            ],
            [
                'fecha'        => '2025-06-18',
                'hora'         => '14:00',
                'servicio'     => 'Mechas / Highlights',
                'precio_final' => 95.00,
                'estado'       => 'completada',
                'notas'        => 'Primeras mechas. Resultado espectacular, tomó fotos para sus redes.',
            ],
            [
                'fecha'        => '2025-07-25',
                'hora'         => '11:00',
                'servicio'     => 'Corte + Lavado',
                'precio_final' => 35.00,
                'estado'       => 'completada',
                'notas'        => 'Mantener el largo pero definir la forma. Recorte leve.',
            ],
            [
                'fecha'        => '2025-08-14',
                'hora'         => '09:30',
                'servicio'     => 'Tratamiento Keratina',
                'precio_final' => 150.00,
                'estado'       => 'completada',
                'notas'        => 'Quería alisado duradero. Proceso de 3 horas. Resultado impecable.',
            ],
            [
                'fecha'        => '2025-09-05',
                'hora'         => '10:00',
                'servicio'     => 'Corte de Cabello',
                'precio_final' => 25.00,
                'estado'       => 'completada',
                'notas'        => 'Recorte post-keratina para definir el largo.',
            ],
            [
                'fecha'        => '2025-10-22',
                'hora'         => '13:00',
                'servicio'     => 'Balayage',
                'precio_final' => 120.00,
                'estado'       => 'completada',
                'notas'        => 'Quería probar algo nuevo. Balayage rubio miel. Quedó enamorada del resultado.',
            ],
            [
                'fecha'        => '2025-11-08',
                'hora'         => '16:00',
                'servicio'     => 'Peinado Especial',
                'precio_final' => 45.00,
                'estado'       => 'completada',
                'notas'        => 'Fiesta de fin de año de su empresa. Recogido elegante.',
            ],
            [
                'fecha'        => '2025-12-15',
                'hora'         => '10:30',
                'servicio'     => 'Tinte Completo',
                'precio_final' => 80.00,
                'estado'       => 'completada',
                'notas'        => 'Retoque de color para las fiestas de fin de año. Tono más oscuro.',
            ],
            [
                'fecha'        => '2026-01-20',
                'hora'         => '11:00',
                'servicio'     => 'Tratamiento Hidratante',
                'precio_final' => 40.00,
                'estado'       => 'completada',
                'notas'        => 'Recuperación post-verano. Muy necesario después del calor.',
            ],
            [
                'fecha'        => '2026-02-14',
                'hora'         => '15:00',
                'servicio'     => 'Corte + Lavado',
                'precio_final' => 35.00,
                'estado'       => 'completada',
                'notas'        => 'San Valentín. Vino con su hermana, las dos pidieron el mismo servicio.',
            ],
            [
                'fecha'        => '2026-03-20',
                'hora'         => '10:00',
                'servicio'     => 'Balayage',
                'precio_final' => 120.00,
                'estado'       => 'confirmada',
                'notas'        => 'Cita próxima. Quiere retocar el balayage de octubre.',
            ],
        ];

        // Solo insertar citas si la cliente fue recién creada (evitar duplicados)
        if ($cliente->wasRecentlyCreated) {
            foreach ($visitas as $v) {
                $servicioId = $servicios[$v['servicio']] ?? null;
                if (!$servicioId) continue;

                Cita::create([
                    'cliente_id'   => $cliente->id,
                    'servicio_id'  => $servicioId,
                    'empleado_id'  => null,
                    'fecha'        => $v['fecha'],
                    'hora'         => $v['hora'],
                    'estado'       => $v['estado'],
                    'precio_final' => $v['precio_final'],
                    'notas'        => $v['notas'],
                ]);
            }
        }
    }
}
