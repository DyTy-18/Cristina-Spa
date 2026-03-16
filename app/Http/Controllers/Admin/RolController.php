<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('name')->get();
        $totalPermisos = Permission::count();
        return view('admin.roles.index', compact('roles', 'totalPermisos'));
    }

    public function edit(Role $rol)
    {
        $permisos = Permission::orderBy('name')->get()->groupBy(function ($p) {
            // Agrupa por la primera palabra (ej. "ver", "crear", "gestionar")
            return explode(' ', $p->name, 2)[1] ?? $p->name;
        })->sortKeys();

        $permisosDelRol = $rol->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('rol', 'permisos', 'permisosDelRol'));
    }

    public function update(Request $request, Role $rol)
    {
        $request->validate([
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permissions,name',
        ]);

        $rol->syncPermissions($request->input('permisos', []));

        return redirect()->route('admin.roles.index')
            ->with('success', "Permisos del rol \"{$rol->name}\" actualizados.");
    }
}
