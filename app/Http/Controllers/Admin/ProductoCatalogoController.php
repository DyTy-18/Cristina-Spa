<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ProductoCatalogoController extends Controller
{
    /**
     * Productos de reventa de la sucursal activa, con stock en vivo.
     * En modo global (sin sucursal seleccionada) se ven todos.
     */
    public function index(Request $request)
    {
        $q = $request->input('q');

        $productos = Producto::reventaConStock(session('sucursal_activa_id'));

        if ($q) {
            $productos = $productos->filter(fn ($p) => str_contains(mb_strtolower($p->nombre), mb_strtolower($q)))->values();
        }

        $productos->load('servicios');
        $servicios = Servicio::orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos', 'q', 'servicios'));
    }

    public function edit(Producto $producto)
    {
        $servicios = Servicio::orderBy('nombre')->get();
        $servicioIdsSeleccionados = $producto->servicios()->pluck('servicios.id')->toArray();

        return view('admin.productos.edit', compact('producto', 'servicios', 'servicioIdsSeleccionados'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'precio_venta' => 'nullable|numeric|min:0',
            'servicios'    => 'array',
            'servicios.*'  => 'integer|exists:servicios,id',
        ]);

        $servicios = $validated['servicios'] ?? [];

        $producto->update(['precio_venta' => $validated['precio_venta'] ?? null]);
        $producto->servicios()->sync($servicios);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }
}
