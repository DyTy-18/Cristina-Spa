<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\ProductoCatalogo;
use App\Models\SeguimientoProducto;
use Illuminate\Http\Request;

class SeguimientoController extends Controller
{
    public function show(Cita $cita)
    {
        $cita->load([
            'cliente',
            'citaServicios.servicio.productos',
            'seguimientoNotas.user',
            'seguimientoProductos.producto',
        ]);

        $productosDeServicios = $cita->citaServicios
            ->pluck('servicio.productos')
            ->filter()
            ->flatten()
            ->unique('id')
            ->values();

        $catalogoProductos = ProductoCatalogo::activos()->orderBy('nombre')->get();

        return view('admin.citas.seguimiento', compact('cita', 'productosDeServicios', 'catalogoProductos'));
    }

    public function storeNota(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'nota' => 'required|string',
        ]);

        $cita->seguimientoNotas()->create([
            'nota'    => $data['nota'],
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Nota agregada al seguimiento.');
    }

    public function storeProducto(Request $request, Cita $cita)
    {
        $data = $request->validate([
            'modo'                  => 'required|in:existente,nuevo',
            'producto_catalogo_id'  => 'required_if:modo,existente|nullable|exists:productos_catalogo,id',
            'nombre'                => 'required_if:modo,nuevo|nullable|string|max:255',
            'publicar_catalogo'     => 'boolean',
            'precio'                => 'required_if:publicar_catalogo,1|nullable|numeric|min:0',
        ]);

        if ($data['modo'] === 'existente') {
            $cita->seguimientoProductos()->create([
                'producto_catalogo_id' => $data['producto_catalogo_id'],
            ]);

            return back()->with('success', 'Producto agregado al seguimiento.');
        }

        if ($request->boolean('publicar_catalogo')) {
            $producto = ProductoCatalogo::create([
                'nombre' => $data['nombre'],
                'precio' => $data['precio'],
                'activo' => true,
            ]);

            $cita->seguimientoProductos()->create([
                'producto_catalogo_id' => $producto->id,
            ]);

            return back()->with('success', 'Producto creado, publicado en el catálogo y agregado al seguimiento.');
        }

        $cita->seguimientoProductos()->create([
            'nombre_personalizado' => $data['nombre'],
        ]);

        return back()->with('success', 'Producto agregado al seguimiento (exclusivo de este cliente).');
    }

    public function destroyProducto(Cita $cita, SeguimientoProducto $seguimientoProducto)
    {
        abort_if($seguimientoProducto->cita_id !== $cita->id, 404);

        $seguimientoProducto->delete();

        return back()->with('success', 'Producto quitado del seguimiento.');
    }
}
