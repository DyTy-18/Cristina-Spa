<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ContratoPago;
use App\Models\ContratoPaquete;
use App\Models\ContratoServicio;
use App\Models\Paquete;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContratoPaqueteController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $q = ContratoPaquete::with(['cliente', 'paquete.nivel', 'contratoServicios', 'contratoPagos'])
            ->orderByDesc('created_at');

        if ($request->filled('estado')) {
            $q->where('estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $q->conCliente($request->buscar);
        }
        if ($request->filled('cliente_id')) {
            $q->where('cliente_id', $request->cliente_id);
        }

        $contratos = $q->paginate(20)->withQueryString();
        $clientes  = Cliente::orderBy('apellido')->orderBy('nombre')->get(['id', 'nombre', 'apellido']);
        $paquetes  = Paquete::where('activo', true)->with(['nivel', 'servicios'])->orderBy('categoria')->orderBy('nombre')->get();

        $paquetesJson = $paquetes->map(fn($p) => [
            'id'          => $p->id,
            'nombre'      => $p->nombre,
            'precio_total'=> $p->precio_total,
            'servicios'   => $p->servicios->map(fn($s) => [
                'id'     => $s->id,
                'nombre' => $s->nombre,
                'precio' => (float) $s->precio,
            ])->values(),
        ])->values();

        return view('admin.contratos.index', compact('contratos', 'clientes', 'paquetes', 'paquetesJson'));
    }

    // ── Store contrato ────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'          => 'required|exists:clientes,id',
            'paquete_id'          => 'required|exists:paquetes,id',
            'fecha_inicio'        => 'required|date',
            'precio_total'        => 'required|numeric|min:0',
            'notas'               => 'nullable|string',
            'pagos'               => 'required|array|min:1',
            'pagos.*.monto'       => 'required|numeric|min:0',
            'pagos.*.fecha_pago'  => 'required|date',
            'pagos.*.metodo_pago' => 'nullable|string|max:50',
        ]);

        $contrato = ContratoPaquete::create([
            'cliente_id'   => $data['cliente_id'],
            'paquete_id'   => $data['paquete_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'precio_total' => $data['precio_total'],
            'tipo_pago'    => 'cuotas',
            'notas'        => $data['notas'] ?? null,
            'estado'       => 'activo',
        ]);

        // Crear registros de servicios
        $paquete = Paquete::with('servicios')->find($data['paquete_id']);
        foreach ($paquete->servicios as $i => $s) {
            ContratoServicio::create([
                'contrato_id' => $contrato->id,
                'servicio_id' => $s->id,
                'estado'      => 'pendiente',
                'orden'       => $i,
            ]);
        }

        // Registrar pagos iniciales (adelantos)
        foreach ($data['pagos'] as $i => $p) {
            ContratoPago::create([
                'contrato_id'  => $contrato->id,
                'numero_cuota' => $i + 1,
                'monto'        => $p['monto'],
                'fecha_pago'   => $p['fecha_pago'],
                'metodo_pago'  => $p['metodo_pago'] ?? null,
                'estado'       => 'pagado',
            ]);
        }

        return redirect()->route('admin.contratos.show', $contrato)
            ->with('success', 'Contrato creado exitosamente.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────
    public function show(ContratoPaquete $contrato)
    {
        $contrato->load([
            'cliente',
            'paquete.nivel',
            'contratoServicios.servicio',
            'contratoServicios.cita',
            'contratoPagos',
        ]);

        return view('admin.contratos.show', compact('contrato'));
    }

    // ── Toggle servicio ───────────────────────────────────────────────────────
    public function toggleServicio(ContratoPaquete $contrato, ContratoServicio $servicio, Request $request)
    {
        if ($servicio->estado === 'completado') {
            $servicio->update(['estado' => 'pendiente', 'fecha_completado' => null]);
        } else {
            $servicio->update([
                'estado'          => 'completado',
                'fecha_completado'=> today(),
            ]);
        }

        // Auto-completar contrato si todos los servicios y pagos están listos
        $this->checkAutoComplete($contrato);

        return back()->with('success', 'Servicio actualizado.');
    }

    // ── Registrar pago de cuota ───────────────────────────────────────────────
    public function pagarCuota(ContratoPaquete $contrato, ContratoPago $pago, Request $request)
    {
        $data = $request->validate([
            'fecha_pago'  => 'required|date',
            'metodo_pago' => 'nullable|string|max:50',
            'notas'       => 'nullable|string',
        ]);

        $pago->update([
            'estado'      => 'pagado',
            'fecha_pago'  => $data['fecha_pago'],
            'metodo_pago' => $data['metodo_pago'] ?? null,
            'notas'       => $data['notas'] ?? null,
        ]);

        $this->checkAutoComplete($contrato);

        return back()->with('success', 'Pago registrado.');
    }

    // ── Registrar pago adicional ──────────────────────────────────────────────
    public function addCuota(ContratoPaquete $contrato, Request $request)
    {
        $contrato->load('contratoPagos');
        $pagado    = $contrato->contratoPagos->sum('monto');
        $pendiente = round($contrato->precio_total - $pagado, 2);

        $data = $request->validate([
            'monto'       => ['required', 'numeric', 'min:0.01', 'max:' . $pendiente],
            'fecha_pago'  => 'required|date',
            'metodo_pago' => 'nullable|string|max:50',
            'notas'       => 'nullable|string',
        ], [
            'monto.max' => "El monto no puede superar el saldo pendiente de Bs. " . number_format($pendiente, 2) . ".",
        ]);

        $siguiente = ($contrato->contratoPagos->max('numero_cuota') ?? 0) + 1;

        ContratoPago::create([
            'contrato_id'  => $contrato->id,
            'numero_cuota' => $siguiente,
            'monto'        => $data['monto'],
            'fecha_pago'   => $data['fecha_pago'],
            'metodo_pago'  => $data['metodo_pago'] ?? null,
            'notas'        => $data['notas'] ?? null,
            'estado'       => 'pagado',
        ]);

        $this->checkAutoComplete($contrato);

        return back()->with('success', 'Pago registrado.');
    }

    // ── Cambiar estado del contrato ───────────────────────────────────────────
    public function cambiarEstado(ContratoPaquete $contrato, Request $request)
    {
        $data = $request->validate(['estado' => 'required|in:activo,completado,cancelado']);
        $contrato->update(['estado' => $data['estado']]);
        return back()->with('success', 'Estado actualizado.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(ContratoPaquete $contrato)
    {
        $contrato->delete();
        return redirect()->route('admin.contratos.index')->with('success', 'Contrato eliminado.');
    }

    // ── Helper: auto-completar ────────────────────────────────────────────────
    private function checkAutoComplete(ContratoPaquete $contrato): void
    {
        $contrato->load('contratoServicios', 'contratoPagos');
        $todosServiciosOk = $contrato->contratoServicios->every(fn($s) => $s->estado === 'completado');
        $totalPagado      = $contrato->contratoPagos->sum('monto');
        $pagoCompleto     = $totalPagado >= $contrato->precio_total;

        if ($todosServiciosOk && $pagoCompleto && $contrato->estado === 'activo') {
            $contrato->update(['estado' => 'completado']);
        }
    }
}
