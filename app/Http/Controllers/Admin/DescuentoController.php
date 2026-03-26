<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\CuponUso;
use App\Models\DescuentoProgramado;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DescuentoController extends Controller
{
    // ── Vista principal ────────────────────────────────────────────────────
    public function index()
    {
        $descuentos = DescuentoProgramado::with('servicio')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $cupones = Cupon::withCount('usos')
            ->orderBy('created_at', 'desc')
            ->get();

        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();

        return view('admin.descuentos.index', compact('descuentos', 'cupones', 'servicios'));
    }

    // ── Descuentos programados ─────────────────────────────────────────────
    public function storeProgramado(Request $request)
    {
        $data = $request->validate([
            'servicio_id'  => 'nullable|exists:servicios,id',
            'descripcion'  => 'nullable|string|max:200',
            'porcentaje'   => 'required|numeric|min:1|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        DescuentoProgramado::create($data + ['activo' => true]);

        return back()->with('success', 'Descuento programado creado.');
    }

    public function toggleProgramado(DescuentoProgramado $descuento)
    {
        $descuento->update(['activo' => ! $descuento->activo]);
        return back()->with('success', $descuento->activo ? 'Descuento activado.' : 'Descuento desactivado.');
    }

    public function destroyProgramado(DescuentoProgramado $descuento)
    {
        $descuento->delete();
        return back()->with('success', 'Descuento eliminado.');
    }

    // ── Cupones ────────────────────────────────────────────────────────────
    public function storeCupon(Request $request)
    {
        $data = $request->validate([
            'codigo'            => 'nullable|string|max:30|unique:cupones,codigo',
            'descripcion'       => 'nullable|string|max:200',
            'tipo'              => 'required|in:porcentaje,monto_fijo',
            'valor'             => 'required|numeric|min:0.01',
            'usos_maximos'      => 'nullable|integer|min:1',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
        ]);

        if (empty($data['codigo'])) {
            do {
                $codigo = strtoupper(Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3));
            } while (Cupon::where('codigo', $codigo)->exists());
            $data['codigo'] = $codigo;
        } else {
            $data['codigo'] = strtoupper($data['codigo']);
        }

        Cupon::create($data + ['activo' => true]);

        return back()->with('success', 'Cupón creado: ' . $data['codigo']);
    }

    public function toggleCupon(Cupon $cupon)
    {
        $cupon->update(['activo' => ! $cupon->activo]);
        return back()->with('success', $cupon->activo ? 'Cupón activado.' : 'Cupón desactivado.');
    }

    public function destroyCupon(Cupon $cupon)
    {
        $cupon->delete();
        return back()->with('success', 'Cupón eliminado.');
    }

    // ── Verificar cupón (JSON) ─────────────────────────────────────────────
    public function verificarCupon(Request $request)
    {
        $codigo = strtoupper(trim($request->get('codigo', '')));
        $cupon  = Cupon::withCount('usos')->where('codigo', $codigo)->first();

        if (! $cupon) {
            return response()->json(['estado' => 'no_encontrado', 'mensaje' => 'Código no encontrado.']);
        }

        $usos  = $cupon->usos()->with('cliente', 'cita')->latest()->take(10)->get();
        $estado = $cupon->estado;

        $mensajes = [
            'valido'   => 'Cupón válido.',
            'agotado'  => 'Cupón agotado — se alcanzó el límite de usos.',
            'vencido'  => 'Cupón vencido.',
            'inactivo' => 'Cupón inactivo.',
        ];

        return response()->json([
            'estado'          => $estado,
            'mensaje'         => $mensajes[$estado] ?? '—',
            'codigo'          => $cupon->codigo,
            'descripcion'     => $cupon->descripcion,
            'tipo'            => $cupon->tipo,
            'valor'           => (float) $cupon->valor,
            'valor_label'     => $cupon->valor_label,
            'usos_actuales'   => $cupon->usos_count,
            'usos_maximos'    => $cupon->usos_maximos,
            'usos_restantes'  => $cupon->usos_restantes,
            'vencimiento'     => $cupon->fecha_vencimiento?->format('d/m/Y'),
            'usos'            => $usos->map(fn($u) => [
                'fecha'   => $u->created_at->format('d/m/Y H:i'),
                'cliente' => $u->cliente?->nombre_completo ?? '—',
                'notas'   => $u->notas,
            ]),
        ]);
    }
}
