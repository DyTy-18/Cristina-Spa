<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EncargadosSucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $encargados = [
            [
                'sucursal_nombre' => 'Sucursal de Calacoto',
                'name'            => 'Encargado Calacoto',
                'email'           => 'calacoto@cristinaspa.com',
                'password'        => 'Calacoto2024',
            ],
            [
                'sucursal_nombre' => 'Sucursal de Hotel Gloria',
                'name'            => 'Encargado Hotel Gloria',
                'email'           => 'hotelgloria@cristinaspa.com',
                'password'        => 'HotelGloria2024',
            ],
            [
                'sucursal_nombre' => 'Sucursal de San Miguel',
                'name'            => 'Encargado San Miguel',
                'email'           => 'sanmiguel@cristinaspa.com',
                'password'        => 'SanMiguel2024',
            ],
            [
                'sucursal_nombre' => 'Sucursal de Achumani',
                'name'            => 'Admin Achumani',
                'email'           => 'achumani@cristinaspa.com',
                'password'        => 'Achumani2024',
            ],
        ];

        foreach ($encargados as $data) {
            $sucursal = Sucursal::where('nombre', $data['sucursal_nombre'])->first();

            if (!$sucursal) {
                $this->command->warn("Sucursal no encontrada: {$data['sucursal_nombre']}");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'        => $data['name'],
                    'password'    => Hash::make($data['password']),
                    'sucursal_id' => $sucursal->id,
                ]
            );

            // Si ya existía, asegurarse de que tenga la sucursal correcta
            if (!$user->wasRecentlyCreated) {
                $user->update(['sucursal_id' => $sucursal->id]);
            }

            // Asignar rol encargado si no lo tiene
            if (!$user->hasRole('encargado')) {
                $user->syncRoles(['encargado']);
            }

            $estado = $user->wasRecentlyCreated ? 'CREADO' : 'YA EXISTÍA';
            $this->command->info("{$estado}: {$data['email']} → {$sucursal->nombre}");
        }
    }
}
