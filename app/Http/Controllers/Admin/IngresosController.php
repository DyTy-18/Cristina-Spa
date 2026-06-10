<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IngresosController extends Controller
{
    public function index(Request $request)
    {
        $periodo        = $request->get('periodo', 'diario');
        $tipoPagoFiltro = $request->get('tipo_pago', '');   // '' = todos
        $sid            = session('sucursal_activa_id');

        $data = match ($periodo) {
            'mensual'    => $this->datosMensual($request, $sid, $tipoPagoFiltro),
            'trimestral' => $this->datosTrimestral($request, $sid, $tipoPagoFiltro),
            'rango'      => $this->datosRango($request, $sid, $tipoPagoFiltro),
            default      => $this->datosDiario($request, $sid, $tipoPagoFiltro),
        };

        return view('admin.ingresos.index', array_merge($data, compact('periodo', 'tipoPagoFiltro')));
    }

    // ── Diario ────────────────────────────────────────────────────────────────
    private function datosDiario(Request $request, ?int $sid, string $tipoPago): array
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->get('fecha'))
            : Carbon::today();

        $citas = $this->baseQuery($sid, $tipoPago)
            ->whereDate('fecha', $fecha)
            ->orderBy('hora')
            ->get();

        $totalGeneral   = (float) $citas->sum('precio_final');
        $porTipoPago    = $this->resumenPorTipo($citas);
        $fechaAnterior  = $fecha->copy()->subDay()->format('Y-m-d');
        $fechaSiguiente = $fecha->copy()->addDay()->format('Y-m-d');

        return compact('fecha', 'citas', 'totalGeneral', 'porTipoPago', 'fechaAnterior', 'fechaSiguiente');
    }

    // ── Mensual ───────────────────────────────────────────────────────────────
    private function datosMensual(Request $request, ?int $sid, string $tipoPago): array
    {
        $anio  = (int) $request->get('anio', now()->year);
        $mes   = (int) $request->get('mes',  now()->month);
        $desde = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $hasta = $desde->copy()->endOfMonth();

        $citas = $this->baseQuery($sid, $tipoPago)
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')->orderBy('hora')
            ->get();

        $totalGeneral = (float) $citas->sum('precio_final');
        $porTipoPago  = $this->resumenPorTipo($citas);

        $porDia = [];
        for ($d = 1; $d <= $desde->daysInMonth; $d++) {
            $key   = Carbon::createFromDate($anio, $mes, $d)->format('Y-m-d');
            $grupo = $citas->filter(fn($c) => $c->fecha->format('Y-m-d') === $key);
            if ($grupo->isEmpty()) continue;
            $porDia[$key] = $this->fila($grupo);
        }

        [$mesPrev, $anioPrev] = $mes === 1  ? [12, $anio - 1] : [$mes - 1, $anio];
        [$mesSig,  $anioSig]  = $mes === 12 ? [1,  $anio + 1] : [$mes + 1, $anio];

        $mesesNombres = $this->mesesNombres();
        $labelPeriodo = $mesesNombres[$mes] . ' ' . $anio;

        return compact(
            'anio', 'mes', 'desde', 'citas', 'totalGeneral', 'porTipoPago',
            'porDia', 'mesPrev', 'anioPrev', 'mesSig', 'anioSig', 'labelPeriodo', 'mesesNombres'
        );
    }

    // ── Trimestral ────────────────────────────────────────────────────────────
    private function datosTrimestral(Request $request, ?int $sid, string $tipoPago): array
    {
        $anio      = (int) $request->get('anio',      now()->year);
        $trimestre = (int) $request->get('trimestre', (int) ceil(now()->month / 3));

        $mesInicio = ($trimestre - 1) * 3 + 1;
        $mesFin    = $mesInicio + 2;
        $desde     = Carbon::createFromDate($anio, $mesInicio, 1)->startOfMonth();
        $hasta     = Carbon::createFromDate($anio, $mesFin,   1)->endOfMonth();

        $citas = $this->baseQuery($sid, $tipoPago)
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->get();

        $totalGeneral = (float) $citas->sum('precio_final');
        $porTipoPago  = $this->resumenPorTipo($citas);
        $mesesNombres = $this->mesesNombres();

        $porMes = [];
        for ($m = $mesInicio; $m <= $mesFin; $m++) {
            $grupo      = $citas->filter(fn($c) => $c->fecha->month === $m);
            $porMes[$m] = array_merge(['nombre' => $mesesNombres[$m]], $this->fila($grupo));
        }

        [$trimPrev, $anioPrev] = $trimestre === 1 ? [4, $anio - 1] : [$trimestre - 1, $anio];
        [$trimSig,  $anioSig]  = $trimestre === 4 ? [1, $anio + 1] : [$trimestre + 1, $anio];
        $labelPeriodo = "T{$trimestre} · {$anio}";

        return compact(
            'anio', 'trimestre', 'desde', 'hasta', 'citas', 'totalGeneral', 'porTipoPago',
            'porMes', 'trimPrev', 'anioPrev', 'trimSig', 'anioSig', 'labelPeriodo', 'mesesNombres'
        );
    }

    // ── Rango libre ───────────────────────────────────────────────────────────
    private function datosRango(Request $request, ?int $sid, string $tipoPago): array
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->get('desde'))->startOfDay()
            : Carbon::today()->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->get('hasta'))->endOfDay()
            : Carbon::today()->endOfDay();

        // Aseguramos que desde ≤ hasta
        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $citas = $this->baseQuery($sid, $tipoPago)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha')->orderBy('hora')
            ->get();

        $totalGeneral = (float) $citas->sum('precio_final');
        $porTipoPago  = $this->resumenPorTipo($citas);

        // Desglose por día (solo con citas)
        $porDia = [];
        $current = $desde->copy();
        while ($current->lte($hasta)) {
            $key   = $current->format('Y-m-d');
            $grupo = $citas->filter(fn($c) => $c->fecha->format('Y-m-d') === $key);
            if ($grupo->isNotEmpty()) {
                $porDia[$key] = $this->fila($grupo);
            }
            $current->addDay();
        }

        $diasRango    = $desde->diffInDays($hasta) + 1;
        $labelPeriodo = $desde->format('d/m/Y') . ' — ' . $hasta->format('d/m/Y');

        return compact('desde', 'hasta', 'citas', 'totalGeneral', 'porTipoPago', 'porDia', 'diasRango', 'labelPeriodo');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function baseQuery(?int $sid, string $tipoPago)
    {
        return Cita::with(['cliente', 'citaServicios.servicio'])
            ->where('estado', 'completada')
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->when($tipoPago !== '', function ($q) use ($tipoPago) {
                if ($tipoPago === 'sin_especificar') {
                    $q->whereNull('tipo_pago');
                } else {
                    $q->where('tipo_pago', $tipoPago);
                }
            });
    }

    private function resumenPorTipo($citas): array
    {
        $result = [];
        foreach (['efectivo', 'tarjeta', 'qr', 'sin_especificar'] as $tipo) {
            $grupo = $tipo === 'sin_especificar'
                ? $citas->filter(fn($c) => is_null($c->tipo_pago))
                : $citas->where('tipo_pago', $tipo);

            $result[$tipo] = [
                'cantidad' => $grupo->count(),
                'total'    => (float) $grupo->sum('precio_final'),
            ];
        }
        return $result;
    }

    private function fila($grupo): array
    {
        return [
            'cantidad'        => $grupo->count(),
            'total'           => (float) $grupo->sum('precio_final'),
            'efectivo'        => (float) $grupo->where('tipo_pago', 'efectivo')->sum('precio_final'),
            'tarjeta'         => (float) $grupo->where('tipo_pago', 'tarjeta')->sum('precio_final'),
            'qr'              => (float) $grupo->where('tipo_pago', 'qr')->sum('precio_final'),
            'sin_especificar' => (float) $grupo->filter(fn($c) => is_null($c->tipo_pago))->sum('precio_final'),
        ];
    }

    private function mesesNombres(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',    4 => 'Abril',
            5 => 'Mayo',  6 => 'Junio',   7 => 'Julio',    8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }
}
