<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // PERMISOS
        // ============================================

        // Permisos para gestión de citas
        Permission::create(['name' => 'ver citas']);
        Permission::create(['name' => 'crear citas']);
        Permission::create(['name' => 'editar citas']);
        Permission::create(['name' => 'eliminar citas']);
        Permission::create(['name' => 'confirmar citas']);
        Permission::create(['name' => 'cancelar citas']);

        // Permisos para gestión de servicios
        Permission::create(['name' => 'ver servicios']);
        Permission::create(['name' => 'crear servicios']);
        Permission::create(['name' => 'editar servicios']);
        Permission::create(['name' => 'eliminar servicios']);

        // Permisos para gestión de clientes
        Permission::create(['name' => 'ver clientes']);
        Permission::create(['name' => 'crear clientes']);
        Permission::create(['name' => 'editar clientes']);
        Permission::create(['name' => 'eliminar clientes']);
        Permission::create(['name' => 'ver historial clientes']);

        // Permisos para gestión de personal/empleados
        Permission::create(['name' => 'ver empleados']);
        Permission::create(['name' => 'crear empleados']);
        Permission::create(['name' => 'editar empleados']);
        Permission::create(['name' => 'eliminar empleados']);
        Permission::create(['name' => 'gestionar horarios']);

        // Permisos para inventario/productos
        Permission::create(['name' => 'ver inventario']);
        Permission::create(['name' => 'gestionar inventario']);

        // Permisos para caja y pagos
        Permission::create(['name' => 'ver caja']);
        Permission::create(['name' => 'registrar pagos']);
        Permission::create(['name' => 'ver ventas']);
        Permission::create(['name' => 'hacer cierres caja']);

        // Permisos para reportes y estadísticas
        Permission::create(['name' => 'ver reportes']);
        Permission::create(['name' => 'exportar reportes']);

        // Permisos de configuración del sistema
        Permission::create(['name' => 'gestionar configuracion']);
        Permission::create(['name' => 'gestionar usuarios']);
        Permission::create(['name' => 'gestionar roles']);

        // ============================================
        // ROLES
        // ============================================

        // ADMIN - Control total del sistema
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // SECRETARIO - Gestión de citas, clientes y pagos
        $secretario = Role::create(['name' => 'secretario']);
        $secretario->givePermissionTo([
            // Citas
            'ver citas', 'crear citas', 'editar citas', 'confirmar citas', 'cancelar citas',
            // Clientes
            'ver clientes', 'crear clientes', 'editar clientes', 'ver historial clientes',
            // Servicios (solo ver)
            'ver servicios',
            // Empleados (solo ver para agendar)
            'ver empleados',
            // Caja
            'ver caja', 'registrar pagos', 'ver ventas',
            // Inventario (solo ver)
            'ver inventario'
        ]);

        // ESTILISTA - Profesional que realiza los servicios
        $estilista = Role::create(['name' => 'estilista']);
        $estilista->givePermissionTo([
            // Citas (ver sus propias citas)
            'ver citas', 'confirmar citas',
            // Clientes
            'ver clientes', 'ver historial clientes',
            // Servicios
            'ver servicios',
            // Inventario (para usar productos)
            'ver inventario'
        ]);

        // CAJERO - Manejo de pagos y caja
        $cajero = Role::create(['name' => 'cajero']);
        $cajero->givePermissionTo([
            // Citas (solo ver)
            'ver citas',
            // Clientes
            'ver clientes',
            // Servicios (para facturar)
            'ver servicios',
            // Caja y ventas
            'ver caja', 'registrar pagos', 'ver ventas', 'hacer cierres caja',
            // Inventario
            'ver inventario', 'gestionar inventario'
        ]);

        // CLIENTE - Usuario registrado que puede reservar citas
        $cliente = Role::create(['name' => 'cliente']);
        $cliente->givePermissionTo([
            'ver servicios',
            'ver citas',
            'crear citas',
            'cancelar citas'
        ]);
    }
}
