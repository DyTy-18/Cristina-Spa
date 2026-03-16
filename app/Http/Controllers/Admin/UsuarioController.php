<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->orderBy('name')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El email es obligatorio.',
            'email.unique'       => 'Ya existe un usuario con ese email.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required'      => 'Debes asignar un rol.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$user->name} creado exitosamente.");
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('name')->get();
        $rolActual = $usuario->roles->first()?->name;
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'rolActual'));
    }

    public function update(Request $request, User $usuario)
    {
        $rules = [
            'name'  => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$usuario->id}",
            'role'  => 'required|exists:roles,name',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $data = $request->validate($rules, [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El email es obligatorio.',
            'email.unique'       => 'Ya existe un usuario con ese email.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required'      => 'Debes asignar un rol.',
        ]);

        $usuario->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($data['password'])]);
        }

        $usuario->syncRoles([$data['role']]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado exitosamente.");
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$nombre} eliminado.");
    }
}
