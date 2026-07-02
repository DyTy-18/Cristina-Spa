<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::with('encargado')
            ->orderBy('es_principal', 'desc')
            ->orderBy('nombre')
            ->get();

        return view('admin.sucursales.index', compact('sucursales'));
    }

    public function create()
    {
        return view('admin.sucursales.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100|unique:sucursales,nombre',
            'direccion'    => 'nullable|string|max:200',
            'telefono'     => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:100',
            'descripcion'  => 'nullable|string',
            'es_principal' => 'boolean',
            'activo'       => 'boolean',
        ], [
            'nombre.unique' => 'Ya existe una sucursal con ese nombre.',
        ]);

        if ($request->boolean('es_principal')) {
            Sucursal::where('es_principal', true)->update(['es_principal' => false]);
        }

        Sucursal::create([
            'nombre'       => $request->nombre,
            'direccion'    => $request->direccion,
            'telefono'     => $request->telefono,
            'email'        => $request->email,
            'descripcion'  => $request->descripcion,
            'es_principal' => $request->boolean('es_principal'),
            'activo'       => $request->boolean('activo', true),
        ]);

        return redirect()->route('admin.sucursales.index')
            ->with('success', 'Sucursal registrada exitosamente.');
    }

    public function edit(Sucursal $sucursal)
    {
        $sucursal->load('encargado');

        // Usuarios que pueden ser asignados como encargado:
        // - sin sucursal asignada, O ya encargado de esta misma sucursal
        // - excluye admin y developer
        $usuariosDisponibles = User::whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['admin', 'developer', 'encargado']))
            ->orderBy('name')
            ->get();

        return view('admin.sucursales.edit', compact('sucursal', 'usuariosDisponibles'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100|unique:sucursales,nombre,' . $sucursal->id,
            'direccion'    => 'nullable|string|max:200',
            'telefono'     => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:100',
            'descripcion'  => 'nullable|string',
            'es_principal' => 'boolean',
            'activo'       => 'boolean',
        ], [
            'nombre.unique' => 'Ya existe otra sucursal con ese nombre.',
        ]);

        if ($request->boolean('es_principal')) {
            Sucursal::where('es_principal', true)
                ->where('id', '!=', $sucursal->id)
                ->update(['es_principal' => false]);
        }

        $sucursal->update([
            'nombre'       => $request->nombre,
            'direccion'    => $request->direccion,
            'telefono'     => $request->telefono,
            'email'        => $request->email,
            'descripcion'  => $request->descripcion,
            'es_principal' => $request->boolean('es_principal'),
            'activo'       => $request->boolean('activo'),
        ]);

        return redirect()->route('admin.sucursales.index')
            ->with('success', 'Sucursal actualizada exitosamente.');
    }

    public function asignarEncargado(Request $request, Sucursal $sucursal)
    {
        $modo = $request->input('modo', 'existente');

        if ($modo === 'nuevo') {
            $request->validate([
                'name'                  => 'required|string|max:100',
                'email'                 => 'required|email|unique:users,email',
                'password'              => 'required|string|min:8|confirmed',
            ], [
                'email.unique'          => 'Ya existe un usuario con ese correo.',
                'password.confirmed'    => 'Las contraseñas no coinciden.',
                'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
            ]);

            $user = User::create([
                'name'        => $request->input('name'),
                'email'       => $request->input('email'),
                'password'    => Hash::make($request->input('password')),
                'sucursal_id' => $sucursal->id,
            ]);
        } else {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ], [
                'user_id.required' => 'Selecciona un usuario.',
            ]);

            $user = User::findOrFail($request->input('user_id'));

            // No permitir si ya es encargado de otra sucursal
            if ($user->hasRole('encargado') && $user->sucursal_id !== $sucursal->id) {
                return back()->withErrors(['user_id' => 'Este usuario ya es encargado de otra sucursal.']);
            }

            $user->sucursal_id = $sucursal->id;
            $user->save();
        }

        $user->syncRoles(['encargado']);

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', 'Encargado asignado correctamente.');
    }

    public function quitarEncargado(Sucursal $sucursal)
    {
        $encargado = $sucursal->encargado;

        if ($encargado) {
            $encargado->removeRole('encargado');
            // Desvincula la sucursal para que no quede huérfano
            $encargado->sucursal_id = null;
            $encargado->save();
        }

        return redirect()->route('admin.sucursales.edit', $sucursal)
            ->with('success', 'Encargado removido. El usuario sigue activo sin rol asignado.');
    }

    public function cambiar(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'developer', 'cristina']), 403);

        $request->validate(['sucursal_id' => 'nullable|exists:sucursales,id']);

        $id = $request->filled('sucursal_id') ? (int) $request->sucursal_id : null;
        session(['sucursal_activa_id' => $id]);

        return redirect()->back();
    }
}
