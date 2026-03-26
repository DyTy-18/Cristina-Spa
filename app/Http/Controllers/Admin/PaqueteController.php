<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paquete;
use App\Models\PaqueteNivel;
use App\Models\PaqueteServicio;
use App\Models\Servicio;
use Illuminate\Http\Request;

class PaqueteController extends Controller
{
    // ── Categorías fijas (pueden añadirse aquí) ───────────────────────────────
    const CATEGORIAS = ['novia', 'quinceañera', 'eventos', 'otro'];

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $paquetes  = Paquete::with(['nivel', 'servicios'])->orderBy('categoria')->orderBy('nombre')->get();
        $niveles   = PaqueteNivel::orderBy('orden')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();

        $paquetesPorCategoria = $paquetes->groupBy('categoria');

        return view('admin.paquetes.index', compact(
            'paquetes', 'niveles', 'servicios', 'paquetesPorCategoria'
        ));
    }

    // ── Store paquete ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'                          => 'required|string|max:150',
            'categoria'                       => 'required|string|max:60',
            'nivel_id'                        => 'nullable|exists:paquete_niveles,id',
            'descripcion'                     => 'nullable|string',
            'descuento_general'               => 'nullable|numeric|min:0|max:100',
            'servicios'                       => 'required|array|min:1',
            'servicios.*.servicio_id'         => 'required|exists:servicios,id',
            'servicios.*.descuento_porcentaje'=> 'nullable|numeric|min:0|max:100',
        ]);

        $paquete = Paquete::create([
            'nombre'            => $data['nombre'],
            'categoria'         => $data['categoria'],
            'nivel_id'          => $data['nivel_id'] ?? null,
            'descripcion'       => $data['descripcion'] ?? null,
            'descuento_general' => $data['descuento_general'] ?? null,
            'activo'            => true,
        ]);

        foreach ($data['servicios'] as $i => $s) {
            PaqueteServicio::create([
                'paquete_id'          => $paquete->id,
                'servicio_id'         => $s['servicio_id'],
                'descuento_porcentaje'=> isset($s['descuento_porcentaje']) && $s['descuento_porcentaje'] !== '' ? $s['descuento_porcentaje'] : null,
                'orden'               => $i,
            ]);
        }

        return redirect()->route('admin.paquetes.index')
            ->with('success', 'Paquete creado exitosamente.');
    }

    // ── Update paquete ────────────────────────────────────────────────────────
    public function update(Request $request, Paquete $paquete)
    {
        $data = $request->validate([
            'nombre'                          => 'required|string|max:150',
            'categoria'                       => 'required|string|max:60',
            'nivel_id'                        => 'nullable|exists:paquete_niveles,id',
            'descripcion'                     => 'nullable|string',
            'descuento_general'               => 'nullable|numeric|min:0|max:100',
            'servicios'                       => 'required|array|min:1',
            'servicios.*.servicio_id'         => 'required|exists:servicios,id',
            'servicios.*.descuento_porcentaje'=> 'nullable|numeric|min:0|max:100',
        ]);

        $paquete->update([
            'nombre'            => $data['nombre'],
            'categoria'         => $data['categoria'],
            'nivel_id'          => $data['nivel_id'] ?? null,
            'descripcion'       => $data['descripcion'] ?? null,
            'descuento_general' => $data['descuento_general'] ?? null,
        ]);

        $paquete->paqueteServicios()->delete();

        foreach ($data['servicios'] as $i => $s) {
            PaqueteServicio::create([
                'paquete_id'          => $paquete->id,
                'servicio_id'         => $s['servicio_id'],
                'descuento_porcentaje'=> isset($s['descuento_porcentaje']) && $s['descuento_porcentaje'] !== '' ? $s['descuento_porcentaje'] : null,
                'orden'               => $i,
            ]);
        }

        return redirect()->route('admin.paquetes.index')
            ->with('success', 'Paquete actualizado exitosamente.');
    }

    // ── Toggle activo ─────────────────────────────────────────────────────────
    public function toggle(Paquete $paquete)
    {
        $paquete->update(['activo' => !$paquete->activo]);
        return back()->with('success', 'Estado del paquete actualizado.');
    }

    // ── Destroy paquete ───────────────────────────────────────────────────────
    public function destroy(Paquete $paquete)
    {
        $paquete->delete();
        return back()->with('success', 'Paquete eliminado.');
    }

    // ── JSON: servicios del paquete para formularios de reserva ───────────────
    public function serviciosJson(Paquete $paquete)
    {
        $paquete->load('servicios');
        $descGeneral = (float) ($paquete->descuento_general ?? 0);

        $items = $paquete->servicios->map(function ($s) use ($descGeneral) {
            $descS = $s->pivot->descuento_porcentaje !== null
                        ? (float) $s->pivot->descuento_porcentaje
                        : $descGeneral;
            return [
                'servicio_id' => $s->id,
                'nombre'      => $s->nombre,
                'precio'      => (float) $s->precio,
                'descuento'   => $descS,
            ];
        });

        return response()->json([
            'paquete'  => ['nombre' => $paquete->nombre, 'categoria' => $paquete->categoria],
            'servicios'=> $items,
        ]);
    }

    // ── Niveles ───────────────────────────────────────────────────────────────
    public function storeNivel(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:60',
            'color'  => 'required|string|size:7',
            'orden'  => 'nullable|integer|min:0',
        ]);

        PaqueteNivel::create([
            'nombre' => $data['nombre'],
            'color'  => $data['color'],
            'orden'  => $data['orden'] ?? PaqueteNivel::max('orden') + 1,
        ]);

        return back()->with('success', 'Nivel creado.');
    }

    public function updateNivel(Request $request, PaqueteNivel $nivel)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:60',
            'color'  => 'required|string|size:7',
            'orden'  => 'nullable|integer|min:0',
        ]);

        $nivel->update($data);
        return back()->with('success', 'Nivel actualizado.');
    }

    public function destroyNivel(PaqueteNivel $nivel)
    {
        if ($nivel->paquetes()->exists()) {
            return back()->with('error', 'No se puede eliminar un nivel que tiene paquetes asociados.');
        }
        $nivel->delete();
        return back()->with('success', 'Nivel eliminado.');
    }
}
