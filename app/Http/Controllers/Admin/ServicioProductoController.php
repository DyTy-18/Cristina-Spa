<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioProductoController extends Controller
{
    public function store(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => [
                'required',
                'integer',
                'exists:productos,id',
                Rule::unique('producto_catalogo_servicio', 'producto_id')->where('servicio_id', $servicio->id),
            ],
        ]);

        $servicio->productosInventario()->attach($validated['producto_id']);

        $producto = Producto::findOrFail($validated['producto_id']);

        return response()->json([
            'id'     => $producto->id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio_venta,
        ], 201);
    }

    public function destroy(Servicio $servicio, Producto $producto): JsonResponse
    {
        $servicio->productosInventario()->detach($producto->id);

        return response()->json(['success' => true]);
    }
}
