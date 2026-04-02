<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\ServicioMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioMaterialController extends Controller
{
    public function store(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => [
                'required',
                'integer',
                'exists:productos,id',
                Rule::unique('servicio_materiales')->where('servicio_id', $servicio->id),
            ],
            'cantidad'        => 'required|numeric|min:0.01',
            'unidad'          => 'required|string|max:30',
            'usos_por_unidad' => 'required|integer|min:1',
        ]);

        $material = $servicio->materiales()->create($validated);
        $material->load('producto');

        return response()->json([
            'id'             => $material->id,
            'producto'       => [
                'id'     => $material->producto->id,
                'nombre' => $material->producto->nombre,
                'marca'  => $material->producto->marca,
            ],
            'cantidad'        => $material->cantidad,
            'unidad'          => $material->unidad,
            'usos_por_unidad' => $material->usos_por_unidad,
        ], 201);
    }

    public function update(Request $request, Servicio $servicio, ServicioMaterial $material): JsonResponse
    {
        abort_if($material->servicio_id !== $servicio->id, 404);

        $validated = $request->validate([
            'cantidad'        => 'required|numeric|min:0.01',
            'unidad'          => 'required|string|max:30',
            'usos_por_unidad' => 'required|integer|min:1',
        ]);

        $material->update($validated);

        return response()->json([
            'id'              => $material->id,
            'cantidad'        => $material->cantidad,
            'unidad'          => $material->unidad,
            'usos_por_unidad' => $material->usos_por_unidad,
        ]);
    }

    public function destroy(Servicio $servicio, ServicioMaterial $material): JsonResponse
    {
        abort_if($material->servicio_id !== $servicio->id, 404);

        $material->delete();

        return response()->json(['success' => true]);
    }
}
