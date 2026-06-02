<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            [
                'nombre'       => 'Sucursal de Calacoto',
                'direccion'    => 'Calacoto, La Paz',
                'es_principal' => true,
                'activo'       => true,
            ],
            [
                'nombre'       => 'Sucursal de Hotel Gloria',
                'direccion'    => 'Hotel Gloria, La Paz',
                'es_principal' => false,
                'activo'       => true,
            ],
            [
                'nombre'       => 'Sucursal de San Miguel',
                'direccion'    => 'San Miguel, La Paz',
                'es_principal' => false,
                'activo'       => true,
            ],
            [
                'nombre'       => 'Sucursal de Achumani',
                'direccion'    => 'Achumani, La Paz',
                'es_principal' => false,
                'activo'       => true,
            ],
        ];

        foreach ($sucursales as $data) {
            Sucursal::firstOrCreate(['nombre' => $data['nombre']], $data);
        }
    }
}
