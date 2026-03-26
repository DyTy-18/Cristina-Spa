@extends('admin.layouts.app')

@section('title', 'Contrato — ' . $contrato->cliente->nombre . ' ' . $contrato->cliente->apellido)
@section('page-title', 'Detalle de Contrato')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────── */
    .contrato-layout {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 860px) { .contrato-layout { grid-template-columns: 1fr; } }

    /* ── Header card ─────────────────────────── */
    .contrato-header-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,.07);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .ch-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--primary-color);
        font-weight: 400;
        line-height: 1.1;
    }
    .ch-sub { font-size: .82rem; color: var(--text-light); margin-top: .3rem; display: flex; gap: 1rem; flex-wrap: wrap; }
    .ch-sub span { display: flex; align-items: center; gap: .3rem; }

    .estado-chip {
        font-size: .65rem; font-weight: 700; letter-spacing: 1px;
        text-transform: uppercase; padding: .2rem .7rem; border-radius: 2px;
    }
    .estado-chip.activo     { background: rgba(201,169,110,.15); color: #92710a; }
    .estado-chip.completado { background: rgba(34,197,94,.12);  color: #166534; }
    .estado-chip.cancelado  { background: rgba(239,68,68,.1);   color: #991b1b; }

    /* Progress summary */
    .progress-summary {
        display: flex; gap: 2rem; flex-wrap: wrap; margin-top: .75rem;
    }
    .progress-block { flex: 1; min-width: 140px; }
    .progress-block-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .8px; color: var(--text-light); margin-bottom: .35rem; }
    .progress-bar-wrap { height: 6px; background: rgba(0,0,0,.08); border-radius: 10px; overflow: hidden; margin-bottom: .3rem; }
    .progress-bar-fill { height: 100%; border-radius: 10px; }
    .progress-bar-fill.svcs  { background: var(--accent-color); }
    .progress-bar-fill.pagos { background: #22c55e; }
    .progress-nums { font-size: .8rem; color: var(--text-dark); font-weight: 500; }

    /* ── Panel cards ─────────────────────────── */
    .panel-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,.07);
        margin-bottom: 1.25rem;
    }
    .panel-header {
        padding: .9rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }
    .panel-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        color: var(--primary-color);
    }
    .panel-body { padding: 1.1rem 1.25rem; }

    /* ── Servicio rows ───────────────────────── */
    .srv-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem 0;
        border-bottom: 1px solid rgba(0,0,0,.05);
        cursor: pointer;
        transition: background .15s;
    }
    .srv-row:last-child { border-bottom: none; }
    .srv-row:hover { background: var(--light-bg); margin: 0 -.5rem; padding-left: .5rem; padding-right: .5rem; }

    .srv-check {
        width: 22px; height: 22px;
        border: 2px solid rgba(0,0,0,.2);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: .75rem;
        transition: var(--transition);
    }
    .srv-check.done { background: var(--accent-color); border-color: var(--accent-color); color: #fff; }
    .srv-check.cancelado { background: rgba(239,68,68,.1); border-color: #ef4444; color: #ef4444; }

    .srv-nombre { flex: 1; font-size: .88rem; color: var(--text-dark); }
    .srv-fecha { font-size: .75rem; color: var(--text-light); white-space: nowrap; }

    .srv-estado-badge {
        font-size: .62rem; font-weight: 600; letter-spacing: .6px; text-transform: uppercase;
        padding: .1rem .45rem; border-radius: 2px;
    }
    .srv-estado-badge.pendiente  { background: rgba(0,0,0,.06);  color: var(--text-light); }
    .srv-estado-badge.completado { background: rgba(201,169,110,.15); color: #92710a; }
    .srv-estado-badge.cancelado  { background: rgba(239,68,68,.1);    color: #991b1b; }

    /* ── Pago rows ───────────────────────────── */
    .pago-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .65rem 0;
        border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .pago-row:last-child { border-bottom: none; }

    .pago-cuota {
        width: 28px; height: 28px;
        background: var(--light-bg);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; font-weight: 600;
        color: var(--text-light);
        flex-shrink: 0;
    }
    .pago-cuota.pagado { background: rgba(34,197,94,.15); color: #166534; }
    .pago-cuota.vencido { background: rgba(239,68,68,.1); color: #991b1b; }

    .pago-info { flex: 1; }
    .pago-monto { font-size: .95rem; font-weight: 500; color: var(--text-dark); }
    .pago-detalle { font-size: .72rem; color: var(--text-light); margin-top: .05rem; }

    .pago-badge {
        font-size: .62rem; font-weight: 600; letter-spacing: .6px; text-transform: uppercase;
        padding: .12rem .5rem; border-radius: 2px;
    }
    .pago-badge.pendiente  { background: rgba(0,0,0,.06);    color: var(--text-light); }
    .pago-badge.pagado     { background: rgba(34,197,94,.12); color: #166534; }
    .pago-badge.vencido    { background: rgba(239,68,68,.1);  color: #991b1b; }

    /* Totals box */
    .pagos-totals {
        margin-top: .75rem;
        padding: .75rem 1rem;
        background: var(--light-bg);
        border: 1px solid rgba(0,0,0,.06);
        font-size: .82rem;
    }
    .pagos-totals .row { display: flex; justify-content: space-between; padding: .15rem 0; }
    .pagos-totals .row.total { font-weight: 500; border-top: 1px solid rgba(0,0,0,.08); padding-top: .4rem; margin-top: .2rem; }
    .pagos-totals .pendiente-val { color: #e55; }
    .pagos-totals .pagado-val   { color: #166534; }

    /* Modal pago */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; overflow-y: auto; padding: 2rem 1rem; }
    .modal-overlay.open { display: flex; align-items: flex-start; justify-content: center; }
    .modal-box { background: var(--white); width: 100%; max-width: 420px; padding: 2rem; position: relative; margin: auto; }
    .modal-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-light); }
    .modal-title { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; color: var(--primary-color); margin-bottom: 1.25rem; }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.25rem;">{{ session('success') }}</div>
@endif

{{-- Back + Estado --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
    <a href="{{ route('admin.contratos.index') }}" class="btn btn-outline btn-sm">← Volver a contratos</a>
    <form method="POST" action="{{ route('admin.contratos.estado', $contrato) }}" style="display:flex;gap:.4rem;align-items:center;">
        @csrf @method('PUT')
        <select name="estado" class="form-control" style="font-size:.78rem;width:140px;">
            <option value="activo"     {{ $contrato->estado === 'activo'     ? 'selected':'' }}>Activo</option>
            <option value="completado" {{ $contrato->estado === 'completado' ? 'selected':'' }}>Completado</option>
            <option value="cancelado"  {{ $contrato->estado === 'cancelado'  ? 'selected':'' }}>Cancelado</option>
        </select>
        <button class="btn btn-secondary btn-sm" type="submit">Cambiar estado</button>
    </form>
</div>

{{-- Header card --}}
<div class="contrato-header-card">
    <div>
        <div class="ch-title">
            {{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}
        </div>
        <div class="ch-sub">
            <span>
                @if($contrato->paquete->nivel)
                <span style="background:{{ $contrato->paquete->nivel->color }};color:#fff;font-size:.6rem;font-weight:700;padding:.05rem .35rem;border-radius:2px;letter-spacing:.8px;text-transform:uppercase;">
                    {{ $contrato->paquete->nivel->nombre }}
                </span>
                @endif
                {{ $contrato->paquete->nombre }}
            </span>
            <span>📅 {{ $contrato->fecha_inicio->format('d/m/Y') }}</span>
            <span>💰 Total: Bs. {{ number_format($contrato->precio_total, 2) }}</span>
        </div>

        @php
            $svcsTotal   = $contrato->contratoServicios->count();
            $svcsOk      = $contrato->contratoServicios->where('estado', 'completado')->count();
            $progSvcs    = $svcsTotal > 0 ? round($svcsOk / $svcsTotal * 100) : 0;
            $montoPagado = $contrato->contratoPagos->where('estado', 'pagado')->sum('monto');
            $progPagos   = $contrato->precio_total > 0 ? min(100, round($montoPagado / $contrato->precio_total * 100)) : 100;
        @endphp
        <div class="progress-summary">
            <div class="progress-block">
                <div class="progress-block-label">Servicios</div>
                <div class="progress-bar-wrap"><div class="progress-bar-fill svcs" style="width:{{ $progSvcs }}%"></div></div>
                <div class="progress-nums">{{ $svcsOk }} / {{ $svcsTotal }} completados ({{ $progSvcs }}%)</div>
            </div>
            <div class="progress-block">
                <div class="progress-block-label">Pagos</div>
                <div class="progress-bar-wrap"><div class="progress-bar-fill pagos" style="width:{{ $progPagos }}%"></div></div>
                <div class="progress-nums">Bs. {{ number_format($montoPagado, 2) }} / {{ number_format($contrato->precio_total, 2) }} ({{ $progPagos }}%)</div>
            </div>
        </div>
    </div>
    <span class="estado-chip {{ $contrato->estado }}">{{ ucfirst($contrato->estado) }}</span>
</div>

{{-- Main layout --}}
<div class="contrato-layout">

    {{-- ── LEFT: Servicios ─────────────────────────────────────────────── --}}
    <div>
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">✂️ Servicios del paquete</span>
                <span style="font-size:.75rem;color:var(--text-light);">
                    Haz clic para marcar como realizado
                </span>
            </div>
            <div class="panel-body">
                @forelse($contrato->contratoServicios as $cs)
                <form method="POST"
                    action="{{ route('admin.contratos.servicios.toggle', [$contrato, $cs]) }}"
                    style="margin:0;">
                    @csrf @method('PUT')
                    <button type="submit" class="srv-row" style="width:100%;text-align:left;background:none;border:none;padding:.6rem 0;">
                        <div class="srv-check {{ $cs->estado === 'completado' ? 'done' : ($cs->estado === 'cancelado' ? 'cancelado' : '') }}">
                            @if($cs->estado === 'completado') ✓
                            @elseif($cs->estado === 'cancelado') ✕
                            @endif
                        </div>
                        <span class="srv-nombre"
                            style="{{ $cs->estado === 'completado' ? 'text-decoration:line-through;opacity:.6;' : '' }}">
                            {{ $cs->servicio->nombre }}
                        </span>
                        @if($cs->fecha_completado)
                        <span class="srv-fecha">{{ $cs->fecha_completado->format('d/m/Y') }}</span>
                        @endif
                        <span class="srv-estado-badge {{ $cs->estado }}">{{ ucfirst($cs->estado) }}</span>
                    </button>
                </form>
                @empty
                <p style="font-size:.82rem;color:var(--text-light);font-style:italic;">Sin servicios registrados.</p>
                @endforelse
            </div>
        </div>

        @if($contrato->notas)
        <div class="panel-card">
            <div class="panel-header"><span class="panel-title">📝 Notas</span></div>
            <div class="panel-body" style="font-size:.85rem;color:var(--text-dark);line-height:1.6;">
                {{ $contrato->notas }}
            </div>
        </div>
        @endif
    </div>

    {{-- ── RIGHT: Pagos ─────────────────────────────────────────────────── --}}
    <div>
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">💳 Pagos realizados</span>
                <button class="btn btn-secondary btn-sm"
                    onclick="document.getElementById('modalRegistrarPago').classList.add('open')">
                    + Registrar pago
                </button>
            </div>
            <div class="panel-body">
                @php
                    $pagado    = $contrato->contratoPagos->sum('monto');
                    $pendiente = $contrato->precio_total - $pagado;
                @endphp

                @forelse($contrato->contratoPagos as $pago)
                <div class="pago-row">
                    <div class="pago-cuota pagado">{{ $pago->numero_cuota }}</div>
                    <div class="pago-info">
                        <div class="pago-monto">Bs. {{ number_format($pago->monto, 2) }}</div>
                        <div class="pago-detalle">
                            {{ $pago->fecha_pago?->format('d/m/Y') ?? '—' }}
                            @if($pago->metodo_pago) · {{ ucfirst($pago->metodo_pago) }}@endif
                            @if($pago->notas) · <em>{{ $pago->notas }}</em>@endif
                        </div>
                    </div>
                    <span class="pago-badge pagado">Pagado</span>
                </div>
                @empty
                <p style="font-size:.82rem;color:var(--text-light);font-style:italic;">Sin pagos registrados.</p>
                @endforelse

                <div class="pagos-totals" style="margin-top:.75rem;">
                    <div class="row">
                        <span>Total paquete</span>
                        <span>Bs. {{ number_format($contrato->precio_total, 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Pagado</span>
                        <span class="pagado-val">Bs. {{ number_format($pagado, 2) }}</span>
                    </div>
                    <div class="row total">
                        <span>Pendiente</span>
                        <span class="{{ $pendiente > 0 ? 'pendiente-val' : 'pagado-val' }}">
                            Bs. {{ number_format(max(0, $pendiente), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════
     Modal: Registrar pago
══════════════════════════════════════ --}}
<div class="modal-overlay" id="modalRegistrarPago">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('modalRegistrarPago').classList.remove('open')">✕</button>
        <div class="modal-title">Registrar Pago</div>
        <form method="POST" action="{{ route('admin.contratos.pagos.store', $contrato) }}">
            @csrf
            @php $pendienteMax = max(0, round($contrato->precio_total - $contrato->contratoPagos->sum('monto'), 2)); @endphp
            <div style="margin-bottom:1rem;padding:.65rem .85rem;background:var(--light-bg);border-left:3px solid {{ $pendienteMax > 0 ? 'var(--accent-color)' : '#22c55e' }};">
                <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.7px;color:var(--text-light);margin-bottom:.2rem;">Saldo pendiente</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:{{ $pendienteMax > 0 ? 'var(--primary-color)' : '#166534' }};line-height:1;">
                    Bs. {{ number_format($pendienteMax, 2) }}
                </div>
                @if($pendienteMax <= 0)
                <div style="font-size:.75rem;color:#166534;margin-top:.2rem;">✓ El contrato está completamente pagado.</div>
                @endif
            </div>
            <div style="margin-bottom:.75rem;">
                <label class="form-label">Monto a registrar (Bs.) *</label>
                <input type="number" name="monto" id="modalPagoMonto" class="form-control"
                       step="0.01" min="0.01" max="{{ $pendienteMax }}" required placeholder="0.00"
                       oninput="validarMontoPago(this, {{ $pendienteMax }})">
                <div id="modalPagoError" style="display:none;color:var(--error-color);font-size:.75rem;margin-top:.25rem;"></div>
            </div>
            <div style="margin-bottom:.75rem;">
                <label class="form-label">Fecha de pago *</label>
                <input type="date" name="fecha_pago" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div style="margin-bottom:.75rem;">
                <label class="form-label">Método de pago</label>
                <select name="metodo_pago" class="form-control">
                    <option value="">— Selecciona —</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="qr">QR / Banca móvil</option>
                </select>
            </div>
            <div style="margin-bottom:1.25rem;">
                <label class="form-label">Notas</label>
                <input type="text" name="notas" class="form-control" placeholder="Opcional…">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.5rem;">
                <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('modalRegistrarPago').classList.remove('open')">Cancelar</button>
                <button type="submit" id="btnConfirmarPago" class="btn btn-primary">✓ Registrar pago</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('modalRegistrarPago').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});

function validarMontoPago(inp, max) {
    const val  = parseFloat(inp.value) || 0;
    const err  = document.getElementById('modalPagoError');
    const btn  = document.getElementById('btnConfirmarPago');
    if (max <= 0) {
        err.textContent = 'El contrato no tiene saldo pendiente.';
        err.style.display = 'block';
        btn.disabled = true;
    } else if (val > max) {
        err.textContent = `El monto no puede superar el saldo pendiente de Bs. ${max.toFixed(2)}.`;
        err.style.display = 'block';
        btn.disabled = true;
    } else if (val <= 0) {
        err.style.display = 'none';
        btn.disabled = false;
    } else {
        err.style.display = 'none';
        btn.disabled = false;
    }
}
</script>
@endpush
@endsection
