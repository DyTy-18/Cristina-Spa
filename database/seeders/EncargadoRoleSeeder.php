<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EncargadoRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $encargado = Role::firstOrCreate(['name' => 'encargado']);

        $encargado->syncPermissions([
            // Citas
            'ver citas', 'crear citas', 'editar citas', 'confirmar citas', 'cancelar citas',
            // Clientes
            'ver clientes', 'crear clientes', 'editar clientes', 'ver historial clientes',
            // Servicios
            'ver servicios',
            // Empleados
            'ver empleados', 'crear empleados', 'editar empleados',
            // Inventario
            'ver inventario', 'gestionar inventario',
            // Caja
            'ver caja', 'registrar pagos', 'ver ventas',
            // Campañas
            'ver campanas',
            // Comisiones
            'ver comisiones',
            // Reportes
            'ver reportes',
            // Usuarios (solo de su sucursal)
            'gestionar usuarios',
        ]);
    }
}
