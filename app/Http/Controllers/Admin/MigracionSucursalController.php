<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Entrada;
use App\Models\Salida;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MigracionSucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.sucursales.migracion', compact('sucursales'));
    }

    public function procesar(Request $request)
    {
        $data = $request->validate([
            'origen_id'   => ['required', Rule::in(array_merge(['sin_asignar'], Sucursal::pluck('id')->map(fn($id) => (string) $id)->all()))],
            'destino_id'  => 'required|exists:sucursales,id|different:origen_id',
            'migrar'      => 'required|array|min:1',
            'migrar.*'    => 'in:empleados,citas,clientes,inventario',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'accion'      => 'required|in:preview,ejecutar',
        ], [
            'destino_id.different' => 'La sucursal destino debe ser diferente al origen.',
            'migrar.required'      => 'Selecciona al menos un tipo de dato a migrar.',
        ]);

        $sinAsignar = $data['origen_id'] === 'sin_asignar';
        $origen     = $sinAsignar ? null : Sucursal::find($data['origen_id']);
        $destino    = Sucursal::find($data['destino_id']);
        $migrar     = $data['migrar'];
        $desde      = $data['fecha_desde'] ?? null;
        $hasta      = $data['fecha_hasta'] ?? null;

        // ── Conteo previo ──────────────────────────────────────────────────────
        $preview = $this->calcularPreview($sinAsignar, $origen, $destino, $migrar, $desde, $hasta);

        if ($data['accion'] === 'preview') {
            $sucursales = Sucursal::orderBy('es_principal', 'desc')->orderBy('nombre')->get();
            return view('admin.sucursales.migracion', compact('sucursales', 'preview', 'data', 'origen', 'destino', 'sinAsignar'));
        }

        // ── Ejecutar migración ─────────────────────────────────────────────────
        $resultados = [];

        if (in_array('empleados', $migrar)) {
            $count = 0;
            Empleado::when($sinAsignar,
                fn($q) => $q->whereDoesntHave('sucursales'),
                fn($q) => $q->whereHas('sucursales', fn($qq) => $qq->where('sucursal_id', $origen->id))
            )->each(function (Empleado $emp) use ($destino, &$count) {
                $emp->sucursales()->syncWithoutDetaching([$destino->id]);
                $count++;
            });
            $resultados['empleados'] = $count;
        }

        if (in_array('citas', $migrar)) {
            $count = Cita::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
                ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
                ->update(['sucursal_id' => $destino->id]);
            $resultados['citas'] = $count;
        }

        if (in_array('clientes', $migrar)) {
            $count = Cliente::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                ->when($desde, fn($q) => $q->where('created_at', '>=', $desde))
                ->when($hasta, fn($q) => $q->where('created_at', '<=', $hasta))
                ->update(['sucursal_id' => $destino->id]);
            $resultados['clientes'] = $count;
        }

        if (in_array('inventario', $migrar)) {
            $ent = Entrada::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
                ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
                ->update(['sucursal_id' => $destino->id]);

            $sal = Salida::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
                ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
                ->update(['sucursal_id' => $destino->id]);

            $resultados['inventario'] = ['entradas' => $ent, 'salidas' => $sal];
        }

        return redirect()->route('admin.sucursales.migracion')
            ->with('migracion_resultados', $resultados)
            ->with('migracion_origen', $sinAsignar ? 'Sin sucursal asignada' : $origen->nombre)
            ->with('migracion_destino', $destino->nombre);
    }

    private function calcularPreview(bool $sinAsignar, ?Sucursal $origen, Sucursal $destino, array $migrar, ?string $desde, ?string $hasta): array
    {
        $preview = [];

        if (in_array('empleados', $migrar)) {
            $preview['empleados'] = Empleado::when($sinAsignar,
                fn($q) => $q->whereDoesntHave('sucursales'),
                fn($q) => $q->whereHas('sucursales', fn($qq) => $qq->where('sucursal_id', $origen->id))
            )->whereDoesntHave('sucursales', fn($q) => $q->where('sucursal_id', $destino->id))
                ->count();
        }

        if (in_array('citas', $migrar)) {
            $preview['citas'] = Cita::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
                ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
                ->count();
        }

        if (in_array('clientes', $migrar)) {
            $preview['clientes'] = Cliente::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                ->when($desde, fn($q) => $q->where('created_at', '>=', $desde))
                ->when($hasta, fn($q) => $q->where('created_at', '<=', $hasta))
                ->count();
        }

        if (in_array('inventario', $migrar)) {
            $preview['inventario'] = [
                'entradas' => Entrada::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                    ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
                    ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
                    ->count(),
                'salidas'  => Salida::when($sinAsignar, fn($q) => $q->whereNull('sucursal_id'), fn($q) => $q->where('sucursal_id', $origen->id))
                    ->when($desde, fn($q) => $q->where('fecha', '>=', $desde))
                    ->when($hasta, fn($q) => $q->where('fecha', '<=', $hasta))
                    ->count(),
            ];
        }

        return $preview;
    }
}
