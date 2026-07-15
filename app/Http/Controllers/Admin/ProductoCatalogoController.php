<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductoCatalogo;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ProductoCatalogoController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $query = ProductoCatalogo::with('servicios')->orderBy('nombre');

        if ($q) {
            $query->where('nombre', 'like', "%{$q}%");
        }

        $productos = $query->get();
        $servicios = Servicio::orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos', 'q', 'servicios'));
    }

    public function create()
    {
        $servicios = Servicio::orderBy('nombre')->get();

        return view('admin.productos.create', compact('servicios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'nullable|numeric|min:0',
            'activo'      => 'boolean',
            'servicios'   => 'array',
            'servicios.*' => 'integer|exists:servicios,id',
        ]);

        $validated['activo'] = $request->has('activo');
        $servicios = $validated['servicios'] ?? [];
        unset($validated['servicios']);

        $producto = ProductoCatalogo::create($validated);
        $producto->servicios()->sync($servicios);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(ProductoCatalogo $producto)
    {
        $servicios = Servicio::orderBy('nombre')->get();
        $servicioIdsSeleccionados = $producto->servicios()->pluck('servicios.id')->toArray();

        return view('admin.productos.edit', compact('producto', 'servicios', 'servicioIdsSeleccionados'));
    }

    public function update(Request $request, ProductoCatalogo $producto)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'nullable|numeric|min:0',
            'activo'      => 'boolean',
            'servicios'   => 'array',
            'servicios.*' => 'integer|exists:servicios,id',
        ]);

        $validated['activo'] = $request->has('activo');
        $servicios = $validated['servicios'] ?? [];
        unset($validated['servicios']);

        $producto->update($validated);
        $producto->servicios()->sync($servicios);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(ProductoCatalogo $producto)
    {
        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
