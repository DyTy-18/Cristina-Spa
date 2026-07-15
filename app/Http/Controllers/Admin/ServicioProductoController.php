<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductoCatalogo;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioProductoController extends Controller
{
    public function store(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'producto_catalogo_id' => [
                'required',
                'integer',
                'exists:productos_catalogo,id',
                Rule::unique('producto_catalogo_servicio', 'producto_catalogo_id')->where('servicio_id', $servicio->id),
            ],
        ]);

        $servicio->productos()->attach($validated['producto_catalogo_id']);

        $producto = ProductoCatalogo::findOrFail($validated['producto_catalogo_id']);

        return response()->json([
            'id'     => $producto->id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
        ], 201);
    }

    public function destroy(Servicio $servicio, ProductoCatalogo $producto): JsonResponse
    {
        $servicio->productos()->detach($producto->id);

        return response()->json(['success' => true]);
    }
}
