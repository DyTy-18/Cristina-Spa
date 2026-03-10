<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutar el seeder de roles y permisos primero
        $this->call([
            RolesAndPermissionsSeeder::class,
            ServiciosSeeder::class,
            ClienteEjemploSeeder::class,
        ]);

        // Crear usuario admin (o recuperar si ya existe)
        $user = User::firstOrCreate(
            ['email' => 'admin@cristinaspa.com'],
            [
                'name'     => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
