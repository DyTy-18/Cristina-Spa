<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    /**
     * Listar todos los servicios
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $categoriaFiltro = $request->query('categoria');

        $query = Servicio::orderBy('categoria')->orderBy('nombre');
        if ($categoriaFiltro) {
            $query->where('categoria', $categoriaFiltro);
        }

        $servicios  = $query->get();
        $categorias = Servicio::whereNotNull('categoria')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('admin.servicios.index', compact('servicios', 'categorias', 'categoriaFiltro'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('admin.servicios.create');
    }

    /**
     * Guardar nuevo servicio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:5',
            'categoria' => 'nullable|string|max:100',
        ]);

        Servicio::create($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Servicio $servicio)
    {
        $materiales = $servicio->materiales()->with('producto')->get();
        $productos = \App\Models\Producto::orderBy('nombre')->get();

        $productosServicio  = $servicio->productosInventario()->get();
        $catalogoProductos  = \App\Models\Producto::reventaConStock(session('sucursal_activa_id'));

        return view('admin.servicios.edit', compact(
            'servicio', 'materiales', 'productos', 'productosServicio', 'catalogoProductos'
        ));
    }

    /**
     * Actualizar servicio
     */
    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:5',
            'categoria' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ]);

        $validated['activo'] = $request->has('activo');
        
        $servicio->update($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Eliminar servicio
     */
    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }
}
