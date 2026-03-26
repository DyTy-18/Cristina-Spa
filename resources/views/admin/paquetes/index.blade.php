@extends('admin.layouts.app')

@section('title', 'Paquetes de Servicios')
@section('page-title', 'Paquetes de Servicios')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────────────── */
    .paquetes-layout {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 900px) {
        .paquetes-layout { grid-template-columns: 1fr; }
    }

    /* ── Category header ────────────────────────────────────── */
    .cat-section { margin-bottom: 2.5rem; }
    .cat-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--primary-color);
        letter-spacing: 1px;
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid rgba(0,0,0,.08);
        text-transform: capitalize;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .cat-count {
        font-family: 'Raleway', sans-serif;
        font-size: .7rem;
        background: var(--accent-color);
        color: #fff;
        border-radius: 20px;
        padding: .1rem .55rem;
        font-weight: 500;
        letter-spacing: .5px;
    }

    /* ── Package card ───────────────────────────────────────── */
    .paquetes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1rem;
    }
    .paquete-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,.07);
        padding: 1.25rem;
        position: relative;
        transition: var(--transition);
    }
    .paquete-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .paquete-card.inactivo { opacity: .55; }

    .paquete-nivel {
        display: inline-block;
        font-size: .65rem;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: .15rem .6rem;
        border-radius: 2px;
        color: #fff;
        margin-bottom: .6rem;
    }
    .paquete-nombre {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.15rem;
        color: var(--primary-color);
        margin-bottom: .3rem;
        font-weight: 500;
    }
    .paquete-desc {
        font-size: .78rem;
        color: var(--text-light);
        margin-bottom: .8rem;
        line-height: 1.4;
    }
    .paquete-servicios-list {
        list-style: none;
        padding: 0;
        margin: 0 0 .9rem;
        font-size: .78rem;
    }
    .paquete-servicios-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .25rem 0;
        border-bottom: 1px dashed rgba(0,0,0,.06);
        gap: .5rem;
    }
    .paquete-servicios-list li:last-child { border-bottom: none; }
    .ps-nombre { color: var(--text-dark); flex: 1; }
    .ps-precio-wrap { display: flex; align-items: center; gap: .3rem; white-space: nowrap; }
    .ps-precio-orig { text-decoration: line-through; color: var(--text-light); font-size: .72rem; }
    .ps-precio-neto { color: var(--primary-color); font-weight: 500; }
    .ps-desc-badge {
        font-size: .6rem;
        background: #22c55e;
        color: #fff;
        padding: .05rem .3rem;
        border-radius: 2px;
    }

    .paquete-totals {
        background: var(--light-bg);
        padding: .6rem .8rem;
        border-radius: 2px;
        margin-bottom: .8rem;
        font-size: .8rem;
    }
    .paquete-totals .row {
        display: flex;
        justify-content: space-between;
        margin-bottom: .15rem;
    }
    .paquete-totals .row:last-child { margin-bottom: 0; }
    .total-label { color: var(--text-light); }
    .total-value { font-weight: 500; }
    .total-value.ahorro { color: #22c55e; }
    .total-value.final { color: var(--primary-color); font-size: .9rem; }

    .paquete-actions {
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
    }
    .btn-xs {
        font-size: .7rem;
        padding: .25rem .65rem;
        letter-spacing: .5px;
    }

    /* ── Empty state ────────────────────────────────────────── */
    .empty-cat {
        font-size: .8rem;
        color: var(--text-light);
        font-style: italic;
        padding: 1rem 0;
    }

    /* ── Sidebar panels ─────────────────────────────────────── */
    .sidebar-panel {
        background: var(--white);
        border: 1px solid rgba(0,0,0,.07);
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .sidebar-panel-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }

    .nivel-row {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .4rem 0;
        border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .nivel-row:last-child { border-bottom: none; }
    .nivel-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .nivel-nombre { flex: 1; font-size: .82rem; }
    .nivel-actions { display: flex; gap: .3rem; }

    /* ── Modal ──────────────────────────────────────────────── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 1000;
        overflow-y: auto;
        padding: 2rem 1rem;
    }
    .modal-overlay.open { display: flex; align-items: flex-start; justify-content: center; }
    .modal-box {
        background: var(--white);
        width: 100%;
        max-width: 640px;
        padding: 2rem;
        position: relative;
        margin: auto;
    }
    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.3rem;
        cursor: pointer;
        color: var(--text-light);
        line-height: 1;
    }
    .modal-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    /* ── Service rows in modal ──────────────────────────────── */
    .ps-rows { display: flex; flex-direction: column; gap: .5rem; margin-bottom: .75rem; }
    .ps-row {
        display: grid;
        grid-template-columns: 1fr 90px 36px;
        gap: .4rem;
        align-items: center;
    }
    .ps-row select, .ps-row input { font-size: .8rem; }
    .desc-general-note {
        font-size: .72rem;
        color: var(--text-light);
        margin-top: .2rem;
    }

    /* ── Color swatch input ─────────────────────────────────── */
    .color-input-wrap { display: flex; align-items: center; gap: .5rem; }
    .color-swatch {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid rgba(0,0,0,.15);
        cursor: pointer;
        padding: 0;
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.5rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:1.5rem;">{{ session('error') }}</div>
@endif

{{-- ── Header bar ─────────────────────────────────────────────────────── --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <p style="font-size:.82rem;color:var(--text-light);margin:0;">
            Gestiona paquetes por categoría (Novia, Quinceañera, Eventos) y nivel (Esmeralda, Rubí, Diamante).
        </p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">+ Nuevo Paquete</button>
</div>

{{-- ── Main layout ─────────────────────────────────────────────────────── --}}
<div class="paquetes-layout">

    {{-- ── Left: packages by category ───────────────────────────────────── --}}
    <div>
        @php
            $todasCategorias = $paquetesPorCategoria->keys()
                ->merge(['novia','quinceañera','eventos','otro'])
                ->unique()->sort()->values();
        @endphp

        @forelse($todasCategorias as $cat)
            @php $grupo = $paquetesPorCategoria->get($cat, collect()); @endphp
            @if($grupo->isNotEmpty())
            <div class="cat-section">
                <div class="cat-title">
                    @php $catIcons = ['novia'=>'👰','quinceañera'=>'🌸','eventos'=>'🎉','otro'=>'✨']; @endphp
                    {{ $catIcons[$cat] ?? '📦' }} {{ ucfirst($cat) }}
                    <span class="cat-count">{{ $grupo->count() }}</span>
                </div>

                <div class="paquetes-grid">
                    @foreach($grupo as $paquete)
                    <div class="paquete-card {{ $paquete->activo ? '' : 'inactivo' }}">

                        {{-- Nivel badge --}}
                        @if($paquete->nivel)
                        <div>
                            <span class="paquete-nivel" style="background:{{ $paquete->nivel->color }}">
                                {{ $paquete->nivel->nombre }}
                            </span>
                        </div>
                        @endif

                        <div class="paquete-nombre">{{ $paquete->nombre }}</div>

                        @if($paquete->descripcion)
                        <div class="paquete-desc">{{ $paquete->descripcion }}</div>
                        @endif

                        {{-- Services list --}}
                        @if($paquete->servicios->isNotEmpty())
                        <ul class="paquete-servicios-list">
                            @foreach($paquete->servicios as $s)
                            @php
                                $descS = $s->pivot->descuento_porcentaje !== null
                                    ? (float)$s->pivot->descuento_porcentaje
                                    : (float)($paquete->descuento_general ?? 0);
                                $neto = round((float)$s->precio * (1 - $descS/100), 2);
                            @endphp
                            <li>
                                <span class="ps-nombre">{{ $s->nombre }}</span>
                                <div class="ps-precio-wrap">
                                    @if($descS > 0)
                                        <span class="ps-precio-orig">Bs.{{ number_format($s->precio,0) }}</span>
                                        <span class="ps-precio-neto">Bs.{{ number_format($neto,0) }}</span>
                                        <span class="ps-desc-badge">-{{ $descS }}%</span>
                                    @else
                                        <span class="ps-precio-neto">Bs.{{ number_format($s->precio,0) }}</span>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        {{-- Totals --}}
                        @php
                            $precioBase  = $paquete->precio_base;
                            $precioTotal = $paquete->precio_total;
                            $ahorro      = $paquete->ahorro;
                        @endphp
                        <div class="paquete-totals">
                            @if($ahorro > 0)
                            <div class="row">
                                <span class="total-label">Precio base</span>
                                <span class="total-value" style="text-decoration:line-through;color:var(--text-light)">Bs.{{ number_format($precioBase,2) }}</span>
                            </div>
                            <div class="row">
                                <span class="total-label">Ahorro</span>
                                <span class="total-value ahorro">-Bs.{{ number_format($ahorro,2) }}</span>
                            </div>
                            @endif
                            <div class="row">
                                <span class="total-label"><strong>Total paquete</strong></span>
                                <span class="total-value final">Bs.{{ number_format($precioTotal,2) }}</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="paquete-actions">
                            <button class="btn btn-secondary btn-xs"
                                onclick="openEditModal({{ $paquete->id }})">Editar</button>

                            <form method="POST" action="{{ route('admin.paquetes.toggle', $paquete) }}" style="display:inline">
                                @csrf @method('PUT')
                                <button class="btn btn-xs {{ $paquete->activo ? 'btn-warning' : 'btn-success' }}">
                                    {{ $paquete->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.paquetes.destroy', $paquete) }}" style="display:inline"
                                onsubmit="return confirm('¿Eliminar el paquete {{ addslashes($paquete->nombre) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-xs">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @empty
            <div class="empty-cat">No hay paquetes registrados aún. Crea el primero.</div>
        @endforelse

        @if($paquetes->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;color:var(--text-light);">
            <div style="font-size:3rem;margin-bottom:1rem;">🎁</div>
            <p>No hay paquetes registrados aún.</p>
            <button class="btn btn-primary" onclick="openModal()">Crear primer paquete</button>
        </div>
        @endif
    </div>

    {{-- ── Right sidebar: niveles ─────────────────────────────────────────── --}}
    <div>
        {{-- Niveles existentes --}}
        <div class="sidebar-panel">
            <div class="sidebar-panel-title">Niveles / Clasificaciones</div>

            @foreach($niveles as $nivel)
            <div class="nivel-row" id="nivel-row-{{ $nivel->id }}">
                <span class="nivel-dot" style="background:{{ $nivel->color }}"></span>
                <span class="nivel-nombre" id="nivel-nombre-{{ $nivel->id }}">{{ $nivel->nombre }}</span>
                <div class="nivel-actions">
                    <button class="btn btn-secondary btn-xs"
                        onclick="openNivelEdit({{ $nivel->id }}, '{{ addslashes($nivel->nombre) }}', '{{ $nivel->color }}', {{ $nivel->orden }})">
                        ✏️
                    </button>
                    <form method="POST" action="{{ route('admin.paquetes.niveles.destroy', $nivel) }}" style="display:inline"
                        onsubmit="return confirm('¿Eliminar nivel {{ addslashes($nivel->nombre) }}? Solo se puede si no tiene paquetes.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-xs">✕</button>
                    </form>
                </div>
            </div>
            @endforeach

            {{-- Add nivel form --}}
            <form method="POST" action="{{ route('admin.paquetes.niveles.store') }}" style="margin-top:1rem;">
                @csrf
                <div style="margin-bottom:.5rem;">
                    <label class="form-label" style="font-size:.75rem;">Nombre del nivel</label>
                    <input type="text" name="nombre" class="form-control" placeholder="ej. Platino" required maxlength="60">
                </div>
                <div style="margin-bottom:.5rem;">
                    <label class="form-label" style="font-size:.75rem;">Color</label>
                    <div class="color-input-wrap">
                        <input type="color" name="color" class="color-swatch" value="#c9a96e"
                            oninput="this.nextElementSibling.textContent=this.value">
                        <span style="font-size:.75rem;color:var(--text-light);">#c9a96e</span>
                    </div>
                </div>
                <input type="hidden" name="orden" value="99">
                <button class="btn btn-primary" style="width:100%;font-size:.78rem;padding:.4rem;">
                    + Añadir nivel
                </button>
            </form>
        </div>

        {{-- Legend --}}
        <div class="sidebar-panel" style="font-size:.78rem;color:var(--text-light);line-height:1.7;">
            <div class="sidebar-panel-title" style="font-size:.9rem;">Lógica de descuentos</div>
            <p>• Cada servicio puede tener su <strong>propio descuento</strong>.</p>
            <p>• Si un servicio <em>no</em> tiene descuento propio, se aplica el <strong>descuento general</strong> del paquete.</p>
            <p>• Ambos valores pueden ser 0% (sin descuento).</p>
        </div>
    </div>

</div>{{-- end layout --}}

{{-- ══════════════════════════════════════════════════════════════
     Modal: crear / editar paquete
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalPaquete">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <div class="modal-title" id="modalPaqueteTitulo">Nuevo Paquete</div>

        <form method="POST" id="formPaquete" action="{{ route('admin.paquetes.store') }}">
            @csrf
            <span id="methodField"></span>{{-- PUT override injected by JS for edit --}}

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label">Nombre del paquete *</label>
                    <input type="text" name="nombre" id="pkNombre" class="form-control" required maxlength="150"
                        placeholder="ej. Paquete Novia Completo">
                </div>
                <div>
                    <label class="form-label">Categoría *</label>
                    <select name="categoria" id="pkCategoria" class="form-control" required>
                        <option value="">— Selecciona —</option>
                        <option value="novia">👰 Novia</option>
                        <option value="quinceañera">🌸 Quinceañera</option>
                        <option value="eventos">🎉 Eventos</option>
                        <option value="otro">✨ Otro</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label">Nivel</label>
                    <select name="nivel_id" id="pkNivel" class="form-control">
                        <option value="">— Sin nivel —</option>
                        @foreach($niveles as $n)
                        <option value="{{ $n->id }}" data-color="{{ $n->color }}">{{ $n->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Descuento general (%)</label>
                    <input type="number" name="descuento_general" id="pkDescGeneral" class="form-control"
                        min="0" max="100" step="0.01" placeholder="0">
                    <div class="desc-general-note">Se aplica a servicios sin descuento propio</div>
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" id="pkDescripcion" class="form-control" rows="2"
                    placeholder="Descripción breve del paquete…"></textarea>
            </div>

            {{-- Services --}}
            <div style="margin-bottom:.5rem;">
                <label class="form-label">Servicios incluidos *</label>
                <div style="display:grid;grid-template-columns:1fr 90px 36px;gap:.4rem;margin-bottom:.3rem;">
                    <span style="font-size:.7rem;color:var(--text-light);letter-spacing:.5px;text-transform:uppercase;">Servicio</span>
                    <span style="font-size:.7rem;color:var(--text-light);letter-spacing:.5px;text-transform:uppercase;">Desc.%</span>
                    <span></span>
                </div>
            </div>
            <div class="ps-rows" id="psRows"></div>
            <button type="button" class="btn btn-secondary btn-xs" onclick="addPsRow()">+ Añadir servicio</button>

            <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="pkSubmitBtn">Crear paquete</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     Modal: editar nivel
══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalNivel">
    <div class="modal-box" style="max-width:380px;">
        <button class="modal-close" onclick="document.getElementById('modalNivel').classList.remove('open')">✕</button>
        <div class="modal-title">Editar Nivel</div>
        <form method="POST" id="formNivel">
            @csrf @method('PUT')
            <div style="margin-bottom:.75rem;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" id="nivelNombreInput" class="form-control" required maxlength="60">
            </div>
            <div style="margin-bottom:.75rem;">
                <label class="form-label">Color</label>
                <div class="color-input-wrap">
                    <input type="color" name="color" id="nivelColorInput" class="color-swatch"
                        oninput="this.nextElementSibling.textContent=this.value">
                    <span id="nivelColorLabel" style="font-size:.75rem;color:var(--text-light);"></span>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <label class="form-label">Orden</label>
                <input type="number" name="orden" id="nivelOrdenInput" class="form-control" min="0">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.5rem;">
                <button type="button" class="btn btn-secondary"
                    onclick="document.getElementById('modalNivel').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- Paquetes JSON for edit ─────────────────────────────────────────────── --}}
@php
$serviciosJson = $servicios->map(fn($s) => ['id' => $s->id, 'nombre' => $s->nombre, 'precio' => (float) $s->precio])->values();
$paquetesJson  = $paquetes->map(fn($p) => [
    'id'                => $p->id,
    'nombre'            => $p->nombre,
    'categoria'         => $p->categoria,
    'nivel_id'          => $p->nivel_id,
    'descripcion'       => $p->descripcion,
    'descuento_general' => $p->descuento_general,
    'servicios'         => $p->servicios->map(fn($s) => [
        'servicio_id'          => $s->id,
        'descuento_porcentaje' => $s->pivot->descuento_porcentaje,
    ])->values(),
])->values();
@endphp
<script>
const ALL_SERVICIOS = @json($serviciosJson);
const ALL_PAQUETES  = @json($paquetesJson);

// ── Modal helpers ──────────────────────────────────────────────────────────
let psRowIdx = 0;

function openModal(paqueteId = null) {
    const modal = document.getElementById('modalPaquete');
    const form  = document.getElementById('formPaquete');

    // Reset
    form.action  = '{{ route("admin.paquetes.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('modalPaqueteTitulo').textContent = 'Nuevo Paquete';
    document.getElementById('pkSubmitBtn').textContent = 'Crear paquete';
    document.getElementById('pkNombre').value       = '';
    document.getElementById('pkCategoria').value    = '';
    document.getElementById('pkNivel').value        = '';
    document.getElementById('pkDescGeneral').value  = '';
    document.getElementById('pkDescripcion').value  = '';
    document.getElementById('psRows').innerHTML     = '';
    psRowIdx = 0;
    addPsRow(); // start with one empty row

    if (paqueteId) {
        const p = ALL_PAQUETES.find(x => x.id === paqueteId);
        if (!p) return;
        document.getElementById('modalPaqueteTitulo').textContent = 'Editar Paquete';
        document.getElementById('pkSubmitBtn').textContent = 'Guardar cambios';
        form.action = `/admin/paquetes/${p.id}`;
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('pkNombre').value       = p.nombre;
        document.getElementById('pkCategoria').value    = p.categoria;
        document.getElementById('pkNivel').value        = p.nivel_id ?? '';
        document.getElementById('pkDescGeneral').value  = p.descuento_general ?? '';
        document.getElementById('pkDescripcion').value  = p.descripcion ?? '';
        document.getElementById('psRows').innerHTML     = '';
        psRowIdx = 0;
        (p.servicios.length ? p.servicios : [{}]).forEach(s => addPsRow(s));
    }

    modal.classList.add('open');
}

function openEditModal(id) { openModal(id); }

function closeModal() {
    document.getElementById('modalPaquete').classList.remove('open');
}

// ── Service rows ───────────────────────────────────────────────────────────
function addPsRow(data = {}) {
    const i    = psRowIdx++;
    const wrap = document.getElementById('psRows');
    const row  = document.createElement('div');
    row.className = 'ps-row';

    const options = ALL_SERVICIOS.map(s =>
        `<option value="${s.id}" ${data.servicio_id == s.id ? 'selected' : ''}>${s.nombre} (Bs.${s.precio.toFixed(0)})</option>`
    ).join('');

    row.innerHTML = `
        <select name="servicios[${i}][servicio_id]" class="form-control" required>
            <option value="">— Servicio —</option>
            ${options}
        </select>
        <div style="position:relative;">
            <input type="number" name="servicios[${i}][descuento_porcentaje]" class="form-control"
                min="0" max="100" step="0.01" placeholder="0"
                value="${data.descuento_porcentaje ?? ''}"
                style="padding-right:1.5rem;">
            <span style="position:absolute;right:.4rem;top:50%;transform:translateY(-50%);font-size:.75rem;color:var(--text-light);pointer-events:none;">%</span>
        </div>
        <button type="button" onclick="this.closest('.ps-row').remove()"
            style="background:none;border:1px solid rgba(200,0,0,.3);color:#e55;cursor:pointer;border-radius:2px;font-size:.9rem;width:32px;height:32px;display:flex;align-items:center;justify-content:center;">✕</button>`;

    wrap.appendChild(row);
}

// ── Nivel edit modal ───────────────────────────────────────────────────────
function openNivelEdit(id, nombre, color, orden) {
    document.getElementById('formNivel').action = `/admin/paquetes/niveles/${id}`;
    document.getElementById('nivelNombreInput').value = nombre;
    document.getElementById('nivelColorInput').value  = color;
    document.getElementById('nivelColorLabel').textContent = color;
    document.getElementById('nivelOrdenInput').value  = orden;
    document.getElementById('modalNivel').classList.add('open');
}

// Close on overlay click
['modalPaquete','modalNivel'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});
</script>
@endsection
