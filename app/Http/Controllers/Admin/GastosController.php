<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CitaServicio;
use App\Models\Comision;
use App\Models\ComisionEmpleado;
use App\Models\Empleado;
use App\Models\Gasto;
use App\Models\PagoSueldo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GastosController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', 'diario');
        $seccion = $request->get('seccion', 'gastos'); // gastos | sueldos
        $sid     = session('sucursal_activa_id');

        $data = match ($periodo) {
            'mensual'    => $this->datosMensual($request, $sid),
            'trimestral' => $this->datosTrimestral($request, $sid),
            'rango'      => $this->datosRango($request, $sid),
            default      => $this->datosDiario($request, $sid),
        };

        $empleados = Empleado::where('activo', true)->orderBy('nombre')->get();

        return view('admin.gastos.index', array_merge($data, compact('periodo', 'seccion', 'empleados')));
    }

    // ── Store Gasto ───────────────────────────────────────────────────────────
    public function storeGasto(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'monto'       => 'required|numeric|min:0.01',
            'categoria'   => 'required|string',
            'fecha'       => 'required|date',
            'notas'       => 'nullable|string|max:500',
        ]);

        Gasto::create(array_merge($validated, [
            'metodo_pago' => 'efectivo',
            'sucursal_id' => session('sucursal_activa_id'),
            'user_id'     => auth()->id(),
        ]));

        return back()->with('success_gastos', 'Gasto registrado correctamente.');
    }

    // ── Destroy Gasto ─────────────────────────────────────────────────────────
    public function destroyGasto(Gasto $gasto)
    {
        $gasto->delete();
        return back()->with('success_gastos', 'Gasto eliminado.');
    }

    // ── Store Pago Sueldo ─────────────────────────────────────────────────────
    public function storePago(Request $request)
    {
        $request->validate([
            'password_confirmacion' => 'required|string',
        ], [
            'password_confirmacion.required' => 'La contraseña es obligatoria.',
        ]);

        if ($request->password_confirmacion !== config('app.edit_completada_password')) {
            return back()
                ->withInput()
                ->withErrors(['password_confirmacion' => 'Contraseña incorrecta.'])
                ->with('reabrir_modal_pago', true);
        }

        $validated = $request->validate([
            'empleado_id'   => 'required|exists:empleados,id',
            'tipo'          => 'required|in:sueldo,comision,adelanto,bono',
            'monto'         => 'required|numeric|min:0.01',
            'fecha_pago'    => 'required|date',
            'periodo_desde' => 'nullable|date',
            'periodo_hasta' => 'nullable|date',
            'metodo_pago'   => 'required|in:efectivo,transferencia,qr',
            'metodo_pago_2' => 'nullable|in:efectivo,transferencia,qr|different:metodo_pago|required_with:monto_2',
            'monto_2'       => 'nullable|numeric|min:0.01|lt:monto|required_with:metodo_pago_2',
            'notas'         => 'nullable|string|max:500',
        ]);

        PagoSueldo::create(array_merge($validated, [
            'sucursal_id' => session('sucursal_activa_id'),
            'user_id'     => auth()->id(),
        ]));

        return back()->with('success_sueldos', 'Pago registrado correctamente.');
    }

    // ── Destroy Pago Sueldo ───────────────────────────────────────────────────
    public function destroyPago(PagoSueldo $pago)
    {
        $pago->delete();
        return back()->with('success_sueldos', 'Pago eliminado.');
    }

    // ── Períodos ──────────────────────────────────────────────────────────────
    private function datosDiario(Request $request, ?int $sid): array
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->get('fecha'))
            : Carbon::today();

        $gastos  = $this->queryGastos($sid)->whereDate('fecha', $fecha)->orderBy('fecha')->get();
        $pagos   = $this->queryPagos($sid)->whereDate('fecha_pago', $fecha)->orderBy('fecha_pago')->get();

        $totalGastos = (float) $gastos->sum('monto');
        $totalPagos  = (float) $pagos->sum('monto');

        $fechaAnterior  = $fecha->copy()->subDay()->format('Y-m-d');
        $fechaSiguiente = $fecha->copy()->addDay()->format('Y-m-d');

        return compact('fecha', 'gastos', 'pagos', 'totalGastos', 'totalPagos', 'fechaAnterior', 'fechaSiguiente');
    }

    private function datosMensual(Request $request, ?int $sid): array
    {
        $anio  = (int) $request->get('anio', now()->year);
        $mes   = (int) $request->get('mes',  now()->month);
        $desde = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();

        $gastos = $this->queryGastos($sid)->whereBetween('fecha', [$desde, $hasta])->orderBy('fecha')->get();
        $pagos  = $this->queryPagos($sid)->whereBetween('fecha_pago', [$desde, $hasta])->orderBy('fecha_pago')->get();

        $totalGastos = (float) $gastos->sum('monto');
        $totalPagos  = (float) $pagos->sum('monto');

        [$mesPrev, $anioPrev] = $mes === 1  ? [12, $anio - 1] : [$mes - 1, $anio];
        [$mesSig,  $anioSig]  = $mes === 12 ? [1,  $anio + 1] : [$mes + 1, $anio];

        $mesesNombres = $this->mesesNombres();
        $labelPeriodo = $mesesNombres[$mes] . ' ' . $anio;

        return compact('anio', 'mes', 'desde', 'hasta', 'gastos', 'pagos', 'totalGastos', 'totalPagos',
            'mesPrev', 'anioPrev', 'mesSig', 'anioSig', 'labelPeriodo', 'mesesNombres');
    }

    private function datosTrimestral(Request $request, ?int $sid): array
    {
        $anio      = (int) $request->get('anio',      now()->year);
        $trimestre = (int) $request->get('trimestre', (int) ceil(now()->month / 3));

        $mesInicio = ($trimestre - 1) * 3 + 1;
        $mesFin    = $mesInicio + 2;
        $desde     = Carbon::createFromDate($anio, $mesInicio, 1)->startOfMonth();
        $hasta     = Carbon::createFromDate($anio, $mesFin,   1)->endOfMonth();

        $gastos = $this->queryGastos($sid)->whereBetween('fecha', [$desde, $hasta])->orderBy('fecha')->get();
        $pagos  = $this->queryPagos($sid)->whereBetween('fecha_pago', [$desde, $hasta])->orderBy('fecha_pago')->get();

        $totalGastos = (float) $gastos->sum('monto');
        $totalPagos  = (float) $pagos->sum('monto');

        [$trimPrev, $anioPrev] = $trimestre === 1 ? [4, $anio - 1] : [$trimestre - 1, $anio];
        [$trimSig,  $anioSig]  = $trimestre === 4 ? [1, $anio + 1] : [$trimestre + 1, $anio];

        $mesesNombres = $this->mesesNombres();
        $labelPeriodo = "T{$trimestre} · {$anio}";

        return compact('anio', 'trimestre', 'desde', 'hasta', 'gastos', 'pagos', 'totalGastos', 'totalPagos',
            'trimPrev', 'anioPrev', 'trimSig', 'anioSig', 'labelPeriodo', 'mesesNombres');
    }

    private function datosRango(Request $request, ?int $sid): array
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->get('desde'))->startOfDay()
            : Carbon::today()->startOfDay();
        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->get('hasta'))->endOfDay()
            : Carbon::today()->endOfDay();

        if ($desde->gt($hasta)) [$desde, $hasta] = [$hasta, $desde];

        $gastos = $this->queryGastos($sid)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha')->get();
        $pagos = $this->queryPagos($sid)
            ->whereBetween('fecha_pago', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha_pago')->get();

        $totalGastos  = (float) $gastos->sum('monto');
        $totalPagos   = (float) $pagos->sum('monto');
        $diasRango    = $desde->diffInDays($hasta) + 1;
        $labelPeriodo = $desde->format('d/m/Y') . ' — ' . $hasta->format('d/m/Y');

        return compact('desde', 'hasta', 'gastos', 'pagos', 'totalGastos', 'totalPagos', 'diasRango', 'labelPeriodo');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function queryGastos(?int $sid)
    {
        return Gasto::when($sid, fn($q) => $q->where('sucursal_id', $sid));
    }

    private function queryPagos(?int $sid)
    {
        return PagoSueldo::with('empleado')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid));
    }

    private function mesesNombres(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',    4 => 'Abril',
            5 => 'Mayo',  6 => 'Junio',   7 => 'Julio',    8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    // ── AJAX: verificar contraseña ────────────────────────────────────────────
    public function verificarPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if ($request->password !== config('app.edit_completada_password')) {
            return response()->json(['ok' => false, 'message' => 'Contraseña incorrecta.'], 401);
        }

        return response()->json(['ok' => true]);
    }

    // ── AJAX: servicios realizados por un empleado en un rango ────────────────
    public function serviciosEmpleado(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'desde'       => 'required|date',
            'hasta'       => 'required|date',
        ]);

        $empleadoId = $request->empleado_id;
        $desde      = Carbon::parse($request->desde)->startOfDay();
        $hasta      = Carbon::parse($request->hasta)->endOfDay();
        $sid        = session('sucursal_activa_id');

        // Cargar comisiones específicas del empleado y generales de una vez
        $comisionesEmp = ComisionEmpleado::where('empleado_id', $empleadoId)
            ->where('activo', true)
            ->pluck('porcentaje', 'servicio_id');

        $comisionesGen = Comision::where('activo', true)
            ->pluck('porcentaje', 'servicio_id');

        $filas = CitaServicio::with(['cita.cliente', 'servicio'])
            ->where('empleado_id', $empleadoId)
            ->whereHas('cita', function ($q) use ($desde, $hasta, $sid) {
                $q->where('estado', 'completada')
                  ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
                  ->when($sid, fn($q2) => $q2->where('sucursal_id', $sid));
            })
            ->orderBy('created_at')
            ->get()
            ->map(function ($cs) use ($comisionesEmp, $comisionesGen) {
                $precioNeto = $cs->precio_neto ?? (float) $cs->precio_unitario;
                $pct = $comisionesEmp[$cs->servicio_id]
                    ?? $comisionesGen[$cs->servicio_id]
                    ?? 0;
                $comisionMonto = round($precioNeto * $pct / 100, 2);

                return [
                    'cita_id'        => $cs->cita_id,
                    'fecha'          => $cs->cita->fecha->format('d/m/Y'),
                    'cliente'        => $cs->cita->cliente
                                        ? $cs->cita->cliente->nombre . ' ' . $cs->cita->cliente->apellido
                                        : '—',
                    'servicio'       => $cs->servicio->nombre ?? '—',
                    'precio_neto'    => $precioNeto,
                    'pct_comision'   => (float) $pct,
                    'monto_comision' => $comisionMonto,
                ];
            });

        return response()->json([
            'servicios'        => $filas,
            'total_precio'     => round($filas->sum('precio_neto'), 2),
            'total_comisiones' => round($filas->sum('monto_comision'), 2),
            'cantidad'         => $filas->count(),
        ]);
    }
}
