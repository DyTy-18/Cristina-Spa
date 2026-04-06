@extends('admin.layouts.app')

@section('title', 'Contratos de Paquetes')
@section('page-title', 'Contratos de Paquetes')

@push('styles')
<style>
    .contratos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .filter-bar {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .filter-bar input, .filter-bar select { font-size: .82rem; }

    /* Contrato card */
    .contrato-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,.07);
        padding: 1.25rem;
        margin-bottom: 1rem;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        transition: var(--transition);
    }
    .contrato-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.07); }
    .contrato-card.completado { opacity: .7; }
    .contrato-card.cancelado  { opacity: .5; }

    .contrato-top { display: flex; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
    .contrato-cliente {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem;
        color: var(--primary-color);
        font-weight: 400;
    }
    .contrato-paquete {
        font-size: .8rem;
        color: var(--text-light);
        margin-top: .1rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .estado-chip {
        display: inline-block;
        font-size: .62rem;
        font-weight: 600;
        letter-spacing: .8px;
        text-transform: uppercase;
        padding: .15rem .55rem;
        border-radius: 2px;
    }
    .estado-chip.activo    { background: rgba(201,169,110,.15); color: #92710a; }
    .estado-chip.completado{ background: rgba(34,197,94,.12);  color: #166534; }
    .estado-chip.cancelado { background: rgba(239,68,68,.1);   color: #991b1b; }

    /* Progress bars */
    .progress-row { display: flex; flex-direction: column; gap: .35rem; margin-top: .75rem; }
    .progress-item { display: flex; align-items: center; gap: .6rem; font-size: .75rem; color: var(--text-light); }
    .progress-bar-wrap { flex: 1; height: 5px; background: rgba(0,0,0,.08); border-radius: 10px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 10px; transition: width .4s; }
    .progress-bar-fill.svcs  { background: var(--accent-color); }
    .progress-bar-fill.pagos { background: #22c55e; }
    .progress-label { min-width: 38px; text-align: right; font-weight: 500; color: var(--text-dark); }

    .contrato-actions { display: flex; flex-direction: column; gap: .4rem; align-items: flex-end; justify-content: flex-start; }

    /* Modal */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; overflow-y: auto; padding: 2rem 1rem; }
    .modal-overlay.open { display: flex; align-items: flex-start; justify-content: center; }
    .modal-box { background: var(--white); width: 100%; max-width: 580px; padding: 2rem; position: relative; margin: auto; }
    .modal-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-light); }
    .modal-title { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: var(--primary-color); margin-bottom: 1.5rem; }

    /* Pagos dinámicos */
    .pago-row-form { display: grid; grid-template-columns: 110px 130px 1fr 28px; gap: .4rem; align-items: center; margin-bottom: .4rem; }
    .pago-row-form input, .pago-row-form select { font-size: .8rem; }
    .adelanto-label { font-size:.68rem;color:var(--accent-color);font-weight:500;letter-spacing:.5px;text-transform:uppercase;margin-bottom:.3rem; }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.5rem;">{{ session('success') }}</div>
@endif

<div class="contratos-header">
    <div>
        <p style="font-size:.82rem;color:var(--text-light);margin:0;">
            Seguimiento de paquetes: servicios realizados y cuotas de pago por cliente.
        </p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modalNuevoContrato').classList.add('open')">
        + Nuevo contrato
    </button>
</div>

{{-- Filtros --}}
<form method="GET" class="filter-bar">
    <input type="text" name="buscar" class="form-control" placeholder="Buscar cliente…"
        value="{{ request('buscar') }}" style="width:200px;">
    <select name="estado" class="form-control" style="width:150px;">
        <option value="">Todos los estados</option>
        <option value="activo"     {{ request('estado')==='activo'     ? 'selected':'' }}>Activos</option>
        <option value="completado" {{ request('estado')==='completado' ? 'selected':'' }}>Completados</option>
        <option value="cancelado"  {{ request('estado')==='cancelado'  ? 'selected':'' }}>Cancelados</option>
    </select>
    <button class="btn btn-secondary" type="submit">Filtrar</button>
    @if(request('buscar') || request('estado'))
        <a href="{{ route('admin.contratos.index') }}" class="btn btn-outline">Limpiar</a>
    @endif
</form>

{{-- Lista de contratos --}}
@forelse($contratos as $c)
@php
    $svcsTotal = $c->contratoServicios->count();
    $svcsOk    = $c->contratoServicios->where('estado', 'completado')->count();
    $progSvcs  = $svcsTotal > 0 ? round($svcsOk / $svcsTotal * 100) : 0;
    $montoPagado   = $c->contratoPagos->where('estado', 'pagado')->sum('monto');
    $progPagos = $c->precio_total > 0 ? min(100, round($montoPagado / $c->precio_total * 100)) : 100;
@endphp
<div class="contrato-card {{ $c->estado }}">
    <div>
        <div class="contrato-top">
            <div>
                <div class="contrato-cliente">{{ $c->cliente->nombre_completo ?? ($c->cliente->nombre . ' ' . $c->cliente->apellido) }}</div>
                <div class="contrato-paquete">
                    @if($c->paquete?->nivel)
                        <span style="background:{{ $c->paquete->nivel->color }};color:#fff;font-size:.58rem;font-weight:700;padding:.05rem .35rem;border-radius:2px;letter-spacing:.8px;text-transform:uppercase;">
                            {{ $c->paquete->nivel->nombre }}
                        </span>
                    @endif
                    {{ $c->paquete?->nombre ?? '(paquete eliminado)' }}
                    · Bs. {{ number_format($c->precio_total, 2) }}
                    · {{ $c->fecha_inicio->format('d/m/Y') }}
                </div>
            </div>
            <span class="estado-chip {{ $c->estado }}">{{ ucfirst($c->estado) }}</span>
        </div>

        <div class="progress-row">
            <div class="progress-item">
                <span style="min-width:75px;">Servicios</span>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill svcs" style="width:{{ $progSvcs }}%"></div>
                </div>
                <span class="progress-label">{{ $svcsOk }}/{{ $svcsTotal }}</span>
            </div>
            <div class="progress-item">
                <span style="min-width:75px;">Pagos</span>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill pagos" style="width:{{ $progPagos }}%"></div>
                </div>
                <span class="progress-label">{{ $progPagos }}%</span>
            </div>
        </div>
    </div>
    <div class="contrato-actions">
        <a href="{{ route('admin.contratos.show', $c) }}" class="btn btn-primary btn-sm">Ver detalle</a>
        <form method="POST" action="{{ route('admin.contratos.destroy', $c) }}"
            onsubmit="return confirm('¿Eliminar este contrato?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger btn-sm" style="width:100%;">Eliminar</button>
        </form>
    </div>
</div>
@empty
<div style="text-align:center;padding:3rem 1rem;color:var(--text-light);">
    <div style="font-size:3rem;margin-bottom:1rem;">📋</div>
    <p>No hay contratos registrados aún.</p>
    <button class="btn btn-primary" onclick="document.getElementById('modalNuevoContrato').classList.add('open')">
        Crear primer contrato
    </button>
</div>
@endforelse

{{ $contratos->links() }}

{{-- ══════════════════════════════════════
     Modal: Nuevo Contrato
══════════════════════════════════════ --}}
<div class="modal-overlay" id="modalNuevoContrato">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('modalNuevoContrato').classList.remove('open')">✕</button>
        <div class="modal-title">Nuevo Contrato de Paquete</div>

        <form method="POST" action="{{ route('admin.contratos.store') }}">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label">Cliente *</label>
                    <select name="cliente_id" class="form-control" required>
                        <option value="">— Selecciona —</option>
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->apellido }}, {{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Fecha inicio *</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="form-label">Paquete *</label>
                <select name="paquete_id" id="selectPaquete" class="form-control" required onchange="onPaqueteChange(this)">
                    <option value="">— Selecciona un paquete —</option>
                    @foreach($paquetes as $p)
                    <option value="{{ $p->id }}" data-precio="{{ $p->precio_total }}">
                        {{ $p->nivel ? '['.$p->nivel->nombre.'] ' : '' }}{{ $p->nombre }}
                        — Bs. {{ number_format($p->precio_total, 2) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Servicios del paquete (preview) --}}
            <div id="paqueteServiciosPreview" style="display:none;margin-bottom:1rem;padding:.65rem .8rem;background:var(--light-bg);border:1px solid rgba(0,0,0,.06);font-size:.78rem;">
                <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);margin-bottom:.4rem;">Servicios incluidos</div>
                <div id="paqueteServiciosList"></div>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="form-label">Precio total (Bs.) *</label>
                <input type="number" name="precio_total" id="inputPrecioTotal" class="form-control"
                    step="0.01" min="0" required placeholder="0.00" oninput="actualizarSumaPagos()">
            </div>

            {{-- Pagos iniciales --}}
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
                    <label class="form-label" style="margin:0;">Pagos registrados *</label>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addPagoRow()">+ Añadir pago</button>
                </div>
                <div style="display:grid;grid-template-columns:110px 130px 1fr 28px;gap:.4rem;margin-bottom:.25rem;padding:0 .1rem;">
                    <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-light);">Monto (Bs.)</span>
                    <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-light);">Fecha de pago</span>
                    <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-light);">Método</span>
                    <span></span>
                </div>
                <div id="pagosContainer">
                    {{-- Primera fila: adelanto obligatorio (no se puede quitar) --}}
                    <div class="pago-row-form">
                        <input type="number" name="pagos[0][monto]" class="form-control pago-monto"
                            step="0.01" min="0" placeholder="0.00" required oninput="actualizarSumaPagos()">
                        <input type="date" name="pagos[0][fecha_pago]" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <select name="pagos[0][metodo_pago]" class="form-control">
                            <option value="">— Método —</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="qr">QR / Banca móvil</option>
                        </select>
                        <span style="font-size:.7rem;color:var(--accent-color);font-weight:600;white-space:nowrap;">★</span>
                    </div>
                </div>
                <div id="pagosSuma" style="font-size:.78rem;color:var(--text-light);margin-top:.35rem;text-align:right;"></div>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="form-label">Notas</label>
                <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones…"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:.75rem;">
                <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('modalNuevoContrato').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear contrato</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const PKG_DATA = @json($paquetesJson);
let pagoIdx = 1;

function onPaqueteChange(sel) {
    const opt    = sel.options[sel.selectedIndex];
    const precio = parseFloat(opt.dataset.precio || 0);
    document.getElementById('inputPrecioTotal').value = precio > 0 ? precio.toFixed(2) : '';

    const pkg = PKG_DATA.find(p => p.id == sel.value);
    if (pkg) {
        document.getElementById('paqueteServiciosList').innerHTML = pkg.servicios.map(s =>
            `<div style="display:flex;justify-content:space-between;padding:.15rem 0;border-bottom:1px dashed rgba(0,0,0,.07);">
                <span>${s.nombre}</span>
                <span style="color:var(--text-light);">Bs. ${s.precio.toFixed(0)}</span>
            </div>`
        ).join('');
        document.getElementById('paqueteServiciosPreview').style.display = 'block';
    } else {
        document.getElementById('paqueteServiciosPreview').style.display = 'none';
    }
    actualizarSumaPagos();
}

const METODOS_OPTIONS = `
    <option value="">— Método —</option>
    <option value="efectivo">Efectivo</option>
    <option value="tarjeta">Tarjeta</option>
    <option value="transferencia">Transferencia</option>
    <option value="qr">QR / Banca móvil</option>`;

function addPagoRow() {
    const i   = pagoIdx++;
    const row = document.createElement('div');
    row.className = 'pago-row-form';
    row.innerHTML = `
        <input type="number" name="pagos[${i}][monto]" class="form-control pago-monto"
            step="0.01" min="0" placeholder="0.00" required oninput="actualizarSumaPagos()">
        <input type="date" name="pagos[${i}][fecha_pago]" class="form-control" value="{{ date('Y-m-d') }}" required>
        <select name="pagos[${i}][metodo_pago]" class="form-control">${METODOS_OPTIONS}</select>
        <button type="button" onclick="this.closest('.pago-row-form').remove();actualizarSumaPagos()"
            style="background:none;border:1px solid rgba(200,0,0,.3);color:#e55;cursor:pointer;border-radius:2px;font-size:.85rem;width:28px;height:32px;">✕</button>`;
    document.getElementById('pagosContainer').appendChild(row);
}

function actualizarSumaPagos() {
    const total = parseFloat(document.getElementById('inputPrecioTotal').value) || 0;
    const suma  = [...document.querySelectorAll('.pago-monto')].reduce((a, i) => a + (parseFloat(i.value) || 0), 0);
    const pend  = total - suma;
    const el    = document.getElementById('pagosSuma');
    if (suma > 0 || total > 0) {
        el.textContent = `Adelantado: Bs. ${suma.toFixed(2)} · Pendiente: Bs. ${Math.max(0, pend).toFixed(2)}`;
        el.style.color = pend <= 0 ? '#166534' : 'var(--text-light)';
    }
}

document.getElementById('modalNuevoContrato').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endsection
