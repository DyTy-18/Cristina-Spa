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
        ]);

        // Crear usuario de prueba con rol admin
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@cristinaspa.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');
    }
}
