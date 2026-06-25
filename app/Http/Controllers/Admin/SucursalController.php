<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::orderBy('es_principal', 'desc')
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

        // Si se marca como principal, desmarcar las demás
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
        return view('admin.sucursales.edit', compact('sucursal'));
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

        // Si se marca como principal, desmarcar las demás
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

    public function cambiar(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin', 'developer']), 403);

        $request->validate(['sucursal_id' => 'nullable|exists:sucursales,id']);

        // sucursal_id vacío o "0" = modo global (todas las sucursales)
        $id = $request->filled('sucursal_id') ? (int) $request->sucursal_id : null;
        session(['sucursal_activa_id' => $id]);

        return redirect()->back();
    }
}
