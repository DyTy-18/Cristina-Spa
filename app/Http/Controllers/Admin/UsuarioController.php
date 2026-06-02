<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    // Roles que un encargado puede asignar (no puede escalar privilegios)
    private const ROLES_ENCARGADO = ['secretario', 'cajero', 'estilista'];

    public function index()
    {
        $user        = auth()->user();
        $esEncargado = $user->hasRole('encargado');

        $query = User::with(['roles', 'sucursal'])->orderBy('name');

        if ($esEncargado) {
            // Solo ve usuarios de su propia sucursal (excluye admins y encargados)
            $query->where('sucursal_id', $user->sucursal_id)
                  ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin', 'encargado']));
        }

        $usuarios = $query->get();
        return view('admin.usuarios.index', compact('usuarios', 'esEncargado'));
    }

    public function create()
    {
        $user        = auth()->user();
        $esEncargado = $user->hasRole('encargado');

        if ($esEncargado) {
            $roles      = Role::whereIn('name', self::ROLES_ENCARGADO)->orderBy('name')->get();
            $sucursales = collect();
        } else {
            $roles      = Role::whereNotIn('name', ['cliente'])->orderBy('name')->get();
            $sucursales = Sucursal::where('activo', true)
                            ->orderBy('es_principal', 'desc')
                            ->orderBy('nombre')
                            ->get();
        }

        return view('admin.usuarios.create', compact('roles', 'sucursales', 'esEncargado'));
    }

    public function store(Request $request)
    {
        $user        = auth()->user();
        $esEncargado = $user->hasRole('encargado');

        $rolesPermitidos = $esEncargado
            ? self::ROLES_ENCARGADO
            : Role::pluck('name')->toArray();

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => ['required', Rule::in($rolesPermitidos)],
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.unique'       => 'Ya existe un usuario con ese email.',
            'password.min'       => 'Mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required'      => 'Debes asignar un rol.',
            'role.in'            => 'Rol no permitido.',
        ]);

        $sucursalId = $esEncargado
            ? $user->sucursal_id
            : ($data['sucursal_id'] ?? null);

        $nuevo = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'sucursal_id' => $sucursalId,
        ]);

        $nuevo->assignRole($data['role']);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$nuevo->name} creado exitosamente.");
    }

    public function edit(User $usuario)
    {
        $user        = auth()->user();
        $esEncargado = $user->hasRole('encargado');

        // Encargado solo puede editar usuarios de su sucursal
        if ($esEncargado && $usuario->sucursal_id !== $user->sucursal_id) {
            abort(403);
        }

        if ($esEncargado) {
            $roles      = Role::whereIn('name', self::ROLES_ENCARGADO)->orderBy('name')->get();
            $sucursales = collect();
        } else {
            $roles      = Role::whereNotIn('name', ['cliente'])->orderBy('name')->get();
            $sucursales = Sucursal::where('activo', true)
                            ->orderBy('es_principal', 'desc')
                            ->orderBy('nombre')
                            ->get();
        }

        $rolActual = $usuario->roles->first()?->name;
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'rolActual', 'sucursales', 'esEncargado'));
    }

    public function update(Request $request, User $usuario)
    {
        $user        = auth()->user();
        $esEncargado = $user->hasRole('encargado');

        if ($esEncargado && $usuario->sucursal_id !== $user->sucursal_id) {
            abort(403);
        }

        $rolesPermitidos = $esEncargado
            ? self::ROLES_ENCARGADO
            : Role::pluck('name')->toArray();

        $rules = [
            'name'        => 'required|string|max:100',
            'email'       => "required|email|unique:users,email,{$usuario->id}",
            'role'        => ['required', Rule::in($rolesPermitidos)],
            'sucursal_id' => 'nullable|exists:sucursales,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $data = $request->validate($rules, [
            'email.unique'       => 'Ya existe un usuario con ese email.',
            'password.min'       => 'Mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.in'            => 'Rol no permitido.',
        ]);

        $updateData = ['name' => $data['name'], 'email' => $data['email']];

        if (!$esEncargado) {
            $updateData['sucursal_id'] = $data['sucursal_id'] ?? null;
        }

        $usuario->update($updateData);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($data['password'])]);
        }

        $usuario->syncRoles([$data['role']]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado exitosamente.");
    }

    public function destroy(User $usuario)
    {
        $user        = auth()->user();
        $esEncargado = $user->hasRole('encargado');

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($esEncargado && $usuario->sucursal_id !== $user->sucursal_id) {
            abort(403);
        }

        // Encargado no puede eliminar admins ni otros encargados
        if ($esEncargado && $usuario->hasAnyRole(['admin', 'encargado'])) {
            abort(403);
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$nombre} eliminado.");
    }
}
