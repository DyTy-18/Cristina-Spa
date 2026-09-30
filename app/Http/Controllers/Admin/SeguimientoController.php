<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Producto;
use App\Models\SeguimientoProducto;
use App\Services\SeguimientoConsumoService;
use Illuminate\Http\Request;

class SeguimientoController extends Controller
{
    public function __construct(private SeguimientoConsumoService $consumo)
    {
    }

    public function show(Cita $cita)
    {
        $cita->load([
            'cliente',
            'citaServicios.servicio.productosInventario',
            'seguimientoNotas.user',
            'seguimientoProductos.producto',
        ]);

        $productosDeServicios = $cita->citaServicios
            ->pluck('servicio.productosInventario')
            ->filter()
            ->flatten()
            ->unique('id')
            ->values();

        // Solo productos marcados como "Seguimiento" en inventario, con stock de la sucursal de la cita.
        $catalogoProductos = Producto::seguimientoConStock($cita->sucursal_id ?? session('sucursal_activa_id'));

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
            'modo'          => 'required|in:existente,nuevo',
            'producto_id'   => 'required_if:modo,existente|nullable|exists:productos,id',
            'gramos'        => 'required_if:modo,existente|nullable|numeric|min:0.01',
            'nombre'        => 'required_if:modo,nuevo|nullable|string|max:255',
        ], [
            'gramos.required_if' => 'Indica cuántos gramos del producto se usaron.',
        ]);

        if ($data['modo'] === 'existente') {
            $producto = Producto::findOrFail($data['producto_id']);

            if ($producto->tipo !== Producto::TIPO_SEGUIMIENTO || ! $producto->peso_gramos) {
                return back()->withErrors(['producto_id' => 'Este producto no está marcado como "Seguimiento" o no tiene peso en gramos.']);
            }

            $cita->seguimientoProductos()->create([
                'producto_id' => $producto->id,
                'gramos'      => $data['gramos'],
            ]);

            $this->consumo->recalcular($producto, $cita->sucursal_id);

            return back()->with('success', 'Producto agregado al seguimiento. Se descontará del inventario según los gramos usados.');
        }

        $cita->seguimientoProductos()->create([
            'nombre_personalizado' => $data['nombre'],
        ]);

        return back()->with('success', 'Producto agregado al seguimiento (exclusivo de este cliente).');
    }

    public function destroyProducto(Cita $cita, SeguimientoProducto $seguimientoProducto)
    {
        abort_if($seguimientoProducto->cita_id !== $cita->id, 404);

        $producto = $seguimientoProducto->producto;
        $seguimientoProducto->delete();

        if ($producto && $seguimientoProducto->gramos) {
            $this->consumo->recalcular($producto, $cita->sucursal_id);
        }

        return back()->with('success', 'Producto quitado del seguimiento.');
    }
}
