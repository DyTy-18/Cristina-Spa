<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoServicioController extends Controller
{
    public function store(Request $request, Producto $producto): JsonResponse
    {
        $validated = $request->validate([
            'servicio_id' => [
                'required',
                'integer',
                'exists:servicios,id',
                Rule::unique('producto_catalogo_servicio', 'servicio_id')->where('producto_id', $producto->id),
            ],
        ]);

        $producto->servicios()->attach($validated['servicio_id']);

        $servicio = Servicio::findOrFail($validated['servicio_id']);

        return response()->json([
            'id'     => $servicio->id,
            'nombre' => $servicio->nombre,
        ], 201);
    }

    public function destroy(Producto $producto, Servicio $servicio): JsonResponse
    {
        $producto->servicios()->detach($servicio->id);

        return response()->json(['success' => true]);
    }
}
