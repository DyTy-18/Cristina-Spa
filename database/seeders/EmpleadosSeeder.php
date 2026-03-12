<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Seeder;

class EmpleadosSeeder extends Seeder
{
    public function run(): void
    {
        $empleados = [
            [
                'nombre'             => 'María Fernanda',
                'apellido'           => 'Quispe Mamani',
                'telefono'           => '71234001',
                'cargo'              => 'estilista',
                'especialidad'       => 'Coloración y Balayage',
                'fecha_contratacion' => '2008-03-15',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Claudia Beatriz',
                'apellido'           => 'Mamani Apaza',
                'telefono'           => '71234002',
                'cargo'              => 'estilista',
                'especialidad'       => 'Cortes y Peinados',
                'fecha_contratacion' => '2010-06-01',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Paola Andrea',
                'apellido'           => 'Condori Flores',
                'telefono'           => '71234003',
                'cargo'              => 'colorista',
                'especialidad'       => 'Tintes y Mechas',
                'fecha_contratacion' => '2012-09-20',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Valeria Cecilia',
                'apellido'           => 'Torrez Laime',
                'telefono'           => '71234004',
                'cargo'              => 'estilista',
                'especialidad'       => 'Alisados y Keratinas',
                'fecha_contratacion' => '2014-02-10',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Andrea Sofía',
                'apellido'           => 'Chávez Rojas',
                'telefono'           => '71234005',
                'cargo'              => 'estilista',
                'especialidad'       => 'Trenzas y Recogidos',
                'fecha_contratacion' => '2015-08-05',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Daniela Mishel',
                'apellido'           => 'Vargas Huanca',
                'telefono'           => '71234006',
                'cargo'              => 'estilista',
                'especialidad'       => 'Cortes Modernos',
                'fecha_contratacion' => '2016-11-15',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Luciana Paola',
                'apellido'           => 'Ramos Pinto',
                'telefono'           => '71234007',
                'cargo'              => 'estilista',
                'especialidad'       => 'Tratamientos Capilares',
                'fecha_contratacion' => '2017-04-22',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Carla Vanessa',
                'apellido'           => 'Mendoza García',
                'telefono'           => '71234008',
                'cargo'              => 'colorista',
                'especialidad'       => 'Balayage y Técnicas Avanzadas',
                'fecha_contratacion' => '2018-07-10',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Sofía Elizabeth',
                'apellido'           => 'López Salinas',
                'telefono'           => '71234009',
                'cargo'              => 'manicurista',
                'especialidad'       => 'Uñas Acrílicas y Gel',
                'fecha_contratacion' => '2019-01-08',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Patricia Alejandra',
                'apellido'           => 'Arce Bustamante',
                'telefono'           => '71234010',
                'cargo'              => 'manicurista',
                'especialidad'       => 'Manicure y Pedicure',
                'fecha_contratacion' => '2019-06-15',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Roxana Isabel',
                'apellido'           => 'Gutiérrez Zenteno',
                'telefono'           => '71234011',
                'cargo'              => 'esteticista',
                'especialidad'       => 'Tratamientos Faciales',
                'fecha_contratacion' => '2020-02-01',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Carolina Fernanda',
                'apellido'           => 'Morales Cruz',
                'telefono'           => '71234012',
                'cargo'              => 'esteticista',
                'especialidad'       => 'Depilación y Spa',
                'fecha_contratacion' => '2020-09-14',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Isabel Carmen',
                'apellido'           => 'Pinto Álvarez',
                'telefono'           => '71234013',
                'cargo'              => 'recepcionista',
                'especialidad'       => 'Atención al Cliente',
                'fecha_contratacion' => '2006-05-20',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Mónica Patricia',
                'apellido'           => 'Huanca Tito',
                'telefono'           => '71234014',
                'cargo'              => 'recepcionista',
                'especialidad'       => 'Agenda y Coordinación',
                'fecha_contratacion' => '2009-03-11',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Fernanda Luz',
                'apellido'           => 'Salinas Quiroga',
                'telefono'           => '71234015',
                'cargo'              => 'cajera',
                'especialidad'       => 'Caja y Facturación',
                'fecha_contratacion' => '2011-08-25',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Alejandra Marcela',
                'apellido'           => 'Rojas Camacho',
                'telefono'           => '71234016',
                'cargo'              => 'recepcionista',
                'especialidad'       => 'Bienvenida y Turnos',
                'fecha_contratacion' => '2013-12-03',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Camila Beatriz',
                'apellido'           => 'Bustamante Espinoza',
                'telefono'           => '71234017',
                'cargo'              => 'estilista',
                'especialidad'       => 'Novias y Eventos',
                'fecha_contratacion' => '2021-03-18',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Diana Carolina',
                'apellido'           => 'Zenteno Mamani',
                'telefono'           => '71234018',
                'cargo'              => 'colorista',
                'especialidad'       => 'Colorimetría Avanzada',
                'fecha_contratacion' => '2021-11-08',
                'activo'             => true,
            ],
            [
                'nombre'             => 'Natalia Fiorella',
                'apellido'           => 'Laime Quispe',
                'telefono'           => '71234019',
                'cargo'              => 'estilista',
                'especialidad'       => 'Cortes y Acabados',
                'fecha_contratacion' => '2017-05-12',
                'activo'             => false,
            ],
            [
                'nombre'             => 'Gloria Mercedes',
                'apellido'           => 'Apaza Villca',
                'telefono'           => '71234020',
                'cargo'              => 'masajista',
                'especialidad'       => 'Masajes y Spa Corporal',
                'fecha_contratacion' => '2022-06-30',
                'activo'             => true,
            ],
        ];

        foreach ($empleados as $data) {
            Empleado::firstOrCreate(
                ['telefono' => $data['telefono']],
                $data
            );
        }
    }
}
