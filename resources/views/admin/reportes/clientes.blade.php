@extends('admin.layouts.app')

@section('title', 'Reporte de Clientes')
@section('page-title', 'Reporte de Clientes')

@push('styles')
<style>
    /* ── Barra de filtros ── */
    .rpt-filter-bar {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.06);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
    }
    .rpt-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .rpt-filter-row + .rpt-filter-row {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        align-items: center;
    }
    .rpt-filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
    .rpt-filter-label {
        font-size: 0.68rem; text-transform: uppercase;
        letter-spacing: 0.8px; color: var(--text-light); font-weight: 300;
    }
    .rpt-filter-bar input[type="date"] {
        width: 152px;
        padding: 0.45rem 0.65rem;
        border: 1px solid rgba(0,0,0,0.12);
        background: var(--white);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.83rem;
        font-weight: 300;
        color: var(--text-dark);
        transition: border-color 0.15s;
    }
    .rpt-filter-bar input[type="date"]:focus {
        outline: none; border-color: var(--accent-color);
    }

    /* ── Pills de contacto ── */
    .pill-group { display: flex; }
    .pill-group input[type="radio"] { display: none; }
    .pill {
        padding: 0.3rem 0.65rem; font-size: 0.68rem; text-transform: uppercase;
        letter-spacing: 0.5px; border: 1px solid rgba(0,0,0,0.13);
        cursor: pointer; display: inline-block; color: var(--text-light);
        margin-right: -1px; transition: all 0.15s; white-space: nowrap;
        user-select: none; font-family: 'Montserrat', sans-serif;
    }
    .pill:hover { color: var(--primary-color); border-color: rgba(0,0,0,0.3); position: relative; z-index: 1; }
    .pill-group input[type="radio"]:checked + .pill {
        background: var(--primary-color); color: var(--white);
        border-color: var(--primary-color); position: relative; z-index: 1;
    }
    .pill-group input[value="si"]:checked + .pill  { background: #2d7a46; border-color: #2d7a46; }
    .pill-group input[value="no"]:checked + .pill  { background: #5a6270; border-color: #5a6270; }

    /* ── Multi-select de servicios ── */
    .ms-wrapper { position: relative; }
    .ms-trigger {
        display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;
        width: 200px; padding: 0.45rem 0.75rem;
        border: 1px solid rgba(0,0,0,0.12); background: var(--white);
        cursor: pointer; font-size: 0.83rem; font-family: 'Montserrat', sans-serif;
        font-weight: 300; color: var(--text-dark); user-select: none;
        transition: border-color 0.15s;
    }
    .ms-trigger:hover, .ms-trigger.open { border-color: var(--accent-color); }
    .ms-trigger.has-selection { color: var(--accent-color); border-color: var(--accent-color); }
    .ms-arrow { font-size: 0.6rem; color: var(--text-light); flex-shrink: 0; }
    .ms-dropdown {
        display: none; position: absolute; top: calc(100% + 3px); left: 0; z-index: 400;
        background: var(--white); border: 1px solid rgba(0,0,0,0.1);
        width: 250px; max-height: 280px; overflow-y: auto;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .ms-dropdown.open { display: block; }
    .ms-dropdown-header {
        padding: 0.45rem 0.85rem; font-size: 0.65rem; text-transform: uppercase;
        letter-spacing: 0.8px; color: var(--text-light);
        border-bottom: 1px solid rgba(0,0,0,0.06); background: var(--light-bg);
    }
    .ms-option {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.48rem 0.85rem; cursor: pointer; font-size: 0.82rem;
        line-height: 1.2; user-select: none;
    }
    .ms-option:hover { background: var(--light-bg); }
    .ms-option input[type="checkbox"] {
        width: auto; flex-shrink: 0; cursor: pointer;
        accent-color: var(--accent-color);
    }

    /* ── Badges de filtros activos ── */
    .active-filters {
        display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;
        margin-top: 0.9rem; padding-top: 0.75rem; border-top: 1px solid rgba(0,0,0,0.05);
    }
    .active-filters-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); white-space: nowrap; }
    .filter-badge {
        display: inline-block; background: var(--primary-color); color: var(--white);
        font-size: 0.67rem; padding: 0.2rem 0.55rem; letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .filter-badge.badge-si  { background: #2d7a46; }
    .filter-badge.badge-no  { background: #5a6270; }
    .filter-badge.badge-period { background: var(--secondary-color); }
    .filter-badge.badge-service { background: var(--accent-color); color: var(--primary-color); }

    /* ── Tarjetas resumen ── */
    .rpt-resumen {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem; margin-bottom: 1.25rem;
    }
    .rpt-card { background: var(--white); border: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem; }
    .rpt-card.rpt-card-accent { border-left: 3px solid var(--accent-color); }
    .rpt-valor { font-family: 'Cormorant Garamond', serif; font-size: 1.9rem; color: var(--primary-color); line-height: 1; }
    .rpt-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); margin-top: 0.25rem; }

    /* ── Tabla ── */
    .rpt-table-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.9rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap; gap: 0.75rem;
    }
    .rpt-table-title { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; letter-spacing: 1.5px; color: var(--primary-color); }
    .rpt-table-count { font-size: 0.75rem; color: var(--text-light); margin-left: 0.5rem; }
    .rpt-search {
        width: 230px; padding: 0.38rem 0.75rem;
        border: 1px solid rgba(0,0,0,0.1); background: var(--white);
        font-family: 'Montserrat', sans-serif; font-size: 0.82rem; font-weight: 300;
        transition: border-color 0.15s;
    }
    .rpt-search:focus { outline: none; border-color: var(--accent-color); }

    .rpt-table { width: 100%; border-collapse: collapse; background: var(--white); }
    .rpt-table th {
        padding: 0.6rem 0.9rem; font-size: 0.67rem; text-transform: uppercase;
        letter-spacing: 0.8px; color: var(--text-light); font-weight: 300;
        border-bottom: 1px solid rgba(0,0,0,0.07); background: var(--light-bg);
        text-align: left; white-space: nowrap;
    }
    .rpt-table td {
        padding: 0.7rem 0.9rem; font-size: 0.83rem;
        border-bottom: 1px solid rgba(0,0,0,0.04); vertical-align: middle;
    }
    .rpt-table tbody tr:last-child td { border-bottom: none; }
    .rpt-table tbody tr:hover td { background: rgba(201,169,110,0.04); }

    .rpt-nombre { font-weight: 400; white-space: nowrap; }
    .rpt-desde  { font-size: 0.71rem; color: var(--text-light); margin-top: 0.1rem; }
    .rpt-monto  { font-family: 'Cormorant Garamond', serif; font-size: 1rem; color: var(--secondary-color); }
    .rpt-num    { font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; }
    .rpt-dim    { color: #ccc; }
    .rpt-date   { font-size: 0.8rem; color: var(--text-light); white-space: nowrap; }
    .rpt-email  { font-size: 0.78rem; }
    .rpt-tag {
        display: inline-block; font-size: 0.67rem; padding: 0.1rem 0.4rem;
        background: rgba(201,169,110,0.13); color: var(--accent-color);
        margin: 1px 2px 1px 0; white-space: nowrap; letter-spacing: 0.2px;
    }
    .rpt-footer { padding: 0.6rem 0.9rem; font-size: 0.75rem; color: var(--text-light); border-top: 1px solid rgba(0,0,0,0.04); }

    /* ── Indicador auto-filtro ── */
    .rpt-auto-status {
        font-size: 0.72rem; color: var(--text-light); letter-spacing: 0.3px;
        display: flex; align-items: center; gap: 0.35rem; white-space: nowrap;
    }
    .rpt-auto-status.pending { color: var(--accent-color); }
    .rpt-auto-status.pending::before {
        content: ''; display: inline-block; width: 7px; height: 7px;
        border: 1.5px solid var(--accent-color); border-top-color: transparent;
        border-radius: 50%; animation: rpt-spin 0.6s linear infinite;
    }
    @keyframes rpt-spin { to { transform: rotate(360deg); } }

    @media print {
        .rpt-filter-bar, .btn, .rpt-search { display: none !important; }
    }
</style>
@endpush

@section('content')

{{-- ─────────── FILTROS ─────────── --}}
<form method="GET" action="{{ route('admin.reportes.clientes') }}" id="filterForm">
    <div class="rpt-filter-bar">

        {{-- Fila 1: período + servicios + acciones --}}
        <div class="rpt-filter-row">

            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}">
            </div>

            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}">
            </div>

            {{-- Multi-select servicios (vanilla JS) --}}
            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Servicios</label>
                <div class="ms-wrapper" id="msWrapper">
                    <div class="ms-trigger" id="msTrigger">
                        <span id="msLabel">Todos los servicios</span>
                        <span class="ms-arrow" id="msArrow">▼</span>
                    </div>
                    <div class="ms-dropdown" id="msDropdown">
                        <div class="ms-dropdown-header">Selecciona uno o más</div>
                        @foreach($servicios as $s)
                            <label class="ms-option">
                                <input type="checkbox"
                                       name="servicios[]"
                                       value="{{ $s->id }}"
                                       {{ in_array($s->id, $servicioIds) ? 'checked' : '' }}>
                                {{ $s->nombre }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rpt-filter-group" style="margin-left:auto;">
                <label class="rpt-filter-label">&nbsp;</label>
                <div style="display:flex;gap:0.75rem;align-items:center;">
                    <span id="autoFilterStatus" class="rpt-auto-status"></span>
                    @if($desde || $hasta || $servicioIds || $conTelefono || $conEmail || $conFechaNac)
                        <a href="{{ route('admin.reportes.clientes') }}" class="btn btn-sm btn-outline" style="white-space:nowrap;">
                            Limpiar filtros
                        </a>
                    @endif
                </div>
            </div>

            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Exportar</label>
                <div style="display:flex;gap:0.4rem;">
                    <a href="{{ route('admin.reportes.clientes.pdf', request()->query()) }}"
                       class="btn btn-sm btn-outline" target="_blank" title="Descargar PDF">↓ PDF</a>
                    <a href="{{ route('admin.reportes.clientes.excel', request()->query()) }}"
                       class="btn btn-sm btn-outline" title="Descargar Excel">↓ Excel</a>
                </div>
            </div>

        </div>

        {{-- Fila 2: filtros de datos de contacto --}}
        <div class="rpt-filter-row">

            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Teléfono</label>
                <div class="pill-group">
                    <input type="radio" name="con_telefono" id="tel-any" value="" {{ !$conTelefono ? 'checked' : '' }}>
                    <label for="tel-any" class="pill">Todos</label>
                    <input type="radio" name="con_telefono" id="tel-si" value="si" {{ $conTelefono === 'si' ? 'checked' : '' }}>
                    <label for="tel-si" class="pill">Con tel.</label>
                    <input type="radio" name="con_telefono" id="tel-no" value="no" {{ $conTelefono === 'no' ? 'checked' : '' }}>
                    <label for="tel-no" class="pill">Sin tel.</label>
                </div>
            </div>

            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Email</label>
                <div class="pill-group">
                    <input type="radio" name="con_email" id="email-any" value="" {{ !$conEmail ? 'checked' : '' }}>
                    <label for="email-any" class="pill">Todos</label>
                    <input type="radio" name="con_email" id="email-si" value="si" {{ $conEmail === 'si' ? 'checked' : '' }}>
                    <label for="email-si" class="pill">Con email</label>
                    <input type="radio" name="con_email" id="email-no" value="no" {{ $conEmail === 'no' ? 'checked' : '' }}>
                    <label for="email-no" class="pill">Sin email</label>
                </div>
            </div>

            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Fecha de nacimiento</label>
                <div class="pill-group">
                    <input type="radio" name="con_fecha_nacimiento" id="fnac-any" value="" {{ !$conFechaNac ? 'checked' : '' }}>
                    <label for="fnac-any" class="pill">Todos</label>
                    <input type="radio" name="con_fecha_nacimiento" id="fnac-si" value="si" {{ $conFechaNac === 'si' ? 'checked' : '' }}>
                    <label for="fnac-si" class="pill">Con fecha</label>
                    <input type="radio" name="con_fecha_nacimiento" id="fnac-no" value="no" {{ $conFechaNac === 'no' ? 'checked' : '' }}>
                    <label for="fnac-no" class="pill">Sin fecha</label>
                </div>
            </div>

        </div>

        {{-- Filtros activos --}}
        @php
            $hayFiltros = $desde || $hasta || $servicioIds || $conTelefono || $conEmail || $conFechaNac;
        @endphp
        @if($hayFiltros)
            <div class="active-filters">
                <span class="active-filters-label">Filtros:</span>

                @if($desde || $hasta)
                    <span class="filter-badge badge-period">
                        {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : '…' }}
                        —
                        {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : '…' }}
                    </span>
                @endif

                @foreach($servicioIds as $sId)
                    @php $svc = $servicios->firstWhere('id', $sId); @endphp
                    @if($svc)
                        <span class="filter-badge badge-service">{{ $svc->nombre }}</span>
                    @endif
                @endforeach

                @if($conTelefono === 'si') <span class="filter-badge badge-si">Con teléfono</span>
                @elseif($conTelefono === 'no') <span class="filter-badge badge-no">Sin teléfono</span>
                @endif

                @if($conEmail === 'si') <span class="filter-badge badge-si">Con email</span>
                @elseif($conEmail === 'no') <span class="filter-badge badge-no">Sin email</span>
                @endif

                @if($conFechaNac === 'si') <span class="filter-badge badge-si">Con fecha nac.</span>
                @elseif($conFechaNac === 'no') <span class="filter-badge badge-no">Sin fecha nac.</span>
                @endif
            </div>
        @endif

    </div>
</form>

{{-- ─────────── RESUMEN ─────────── --}}
<div class="rpt-resumen">
    <div class="rpt-card rpt-card-accent">
        <div class="rpt-valor">{{ $totales['clientes'] }}</div>
        <div class="rpt-label">Clientes</div>
    </div>
    <div class="rpt-card">
        <div class="rpt-valor">{{ $totales['total_visitas'] }}</div>
        <div class="rpt-label">Visitas</div>
    </div>
    <div class="rpt-card">
        <div class="rpt-valor" style="color:var(--accent-color);">Bs. {{ number_format($totales['total_gastado'], 2) }}</div>
        <div class="rpt-label">Total generado</div>
    </div>
    <div class="rpt-card">
        <div class="rpt-valor" style="color:#2d7a46;">{{ $totales['con_telefono'] }}</div>
        <div class="rpt-label">Con teléfono</div>
    </div>
    <div class="rpt-card">
        <div class="rpt-valor" style="color:#2d7a46;">{{ $totales['con_email'] }}</div>
        <div class="rpt-label">Con email</div>
    </div>
    <div class="rpt-card">
        <div class="rpt-valor" style="color:#2d7a46;">{{ $totales['con_fecha_nac'] }}</div>
        <div class="rpt-label">Con fecha nac.</div>
    </div>
</div>

{{-- ─────────── TABLA ─────────── --}}
<div class="table-container">
    <div class="rpt-table-header">
        <div>
            <span class="rpt-table-title">Resultados</span>
            <span class="rpt-table-count">{{ $filas->count() }} cliente(s)</span>
        </div>
        @if($filas->isNotEmpty())
            <input type="text" id="searchInput" class="rpt-search"
                   placeholder="Buscar en resultados..." autocomplete="off">
        @endif
    </div>

    @if($filas->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <p class="empty-state-text">No se encontraron clientes con los filtros aplicados</p>
        </div>
    @else
        <table class="rpt-table" id="clientesTable">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Nacimiento</th>
                    <th style="text-align:center;">Visitas</th>
                    <th>Total gastado</th>
                    <th>Última visita</th>
                    <th>Servicios utilizados</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($filas as $fila)
                    @php $c = $fila['cliente']; @endphp
                    <tr class="cliente-row">
                        <td>
                            <div class="rpt-nombre">{{ $c->nombre_completo }}</div>
                            @if($fila['primera_visita'])
                                <div class="rpt-desde">
                                    cliente desde {{ \Carbon\Carbon::parse($fila['primera_visita'])->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($c->telefono)
                                <span style="white-space:nowrap;">{{ $c->telefono }}</span>
                            @else
                                <span class="rpt-dim">—</span>
                            @endif
                        </td>
                        <td>
                            @if($c->email)
                                <span class="rpt-email">{{ $c->email }}</span>
                            @else
                                <span class="rpt-dim">—</span>
                            @endif
                        </td>
                        <td class="rpt-date">
                            {{ $c->fecha_nacimiento ? $c->fecha_nacimiento->format('d/m/Y') : '—' }}
                        </td>
                        <td style="text-align:center;">
                            @if($fila['visitas'] > 0)
                                <span class="rpt-num">{{ $fila['visitas'] }}</span>
                            @else
                                <span class="rpt-dim">—</span>
                            @endif
                        </td>
                        <td>
                            @if($fila['total_gastado'] > 0)
                                <span class="rpt-monto">Bs. {{ number_format($fila['total_gastado'], 2) }}</span>
                            @else
                                <span class="rpt-dim">—</span>
                            @endif
                        </td>
                        <td class="rpt-date">
                            {{ $fila['ultima_visita'] ? \Carbon\Carbon::parse($fila['ultima_visita'])->format('d/m/Y') : '—' }}
                        </td>
                        <td style="max-width:210px;">
                            @forelse($fila['servicios'] as $srv)
                                <span class="rpt-tag">{{ $srv }}</span>
                            @empty
                                <span class="rpt-dim">—</span>
                            @endforelse
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.clientes.show', $c) }}" class="btn btn-sm btn-outline">
                                Historial
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="rpt-footer">
            {{ $filas->count() }} cliente(s)
            @if($totales['total_gastado'] > 0)
                &nbsp;·&nbsp; Total periodo: <strong>Bs. {{ number_format($totales['total_gastado'], 2) }}</strong>
            @endif
            @if($desde || $hasta)
                &nbsp;·&nbsp; {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : '…' }} — {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : '…' }}
            @endif
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    const form      = document.getElementById('filterForm');
    const status    = document.getElementById('autoFilterStatus');
    const trigger   = document.getElementById('msTrigger');
    const dropdown  = document.getElementById('msDropdown');
    const label     = document.getElementById('msLabel');
    const arrow     = document.getElementById('msArrow');

    /* ── Auto-submit con debounce ── */
    let debounceTimer = null;

    function scheduleSubmit(delayMs) {
        clearTimeout(debounceTimer);
        if (status) {
            status.textContent = 'Aplicando…';
            status.classList.add('pending');
        }
        debounceTimer = setTimeout(() => form.submit(), delayMs);
    }

    /* Fechas: esperar a que el usuario termine (evento change = salir del campo / elegir del picker) */
    document.querySelectorAll('#filterForm input[type="date"]').forEach(input => {
        input.addEventListener('change', () => scheduleSubmit(400));
    });

    /* Pills de contacto: inmediato */
    document.querySelectorAll('#filterForm .pill-group input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => scheduleSubmit(0));
    });

    /* ── Multi-select de servicios ── */
    if (!trigger) return;

    const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]');

    function updateLabel() {
        const n = [...checkboxes].filter(c => c.checked).length;
        label.textContent = n === 0 ? 'Todos los servicios' : n + (n === 1 ? ' servicio' : ' servicios');
        trigger.classList.toggle('has-selection', n > 0);
    }

    function openDropdown() {
        dropdown.classList.add('open');
        trigger.classList.add('open');
        arrow.textContent = '▲';
    }

    function closeDropdown() {
        dropdown.classList.remove('open');
        trigger.classList.remove('open');
        arrow.textContent = '▼';
    }

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.contains('open') ? closeDropdown() : openDropdown();
    });

    dropdown.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.addEventListener('click', closeDropdown);

    /* Checkboxes de servicios: debounce 500ms (el usuario puede marcar varios) */
    checkboxes.forEach(cb => cb.addEventListener('change', () => {
        updateLabel();
        scheduleSubmit(500);
    }));

    updateLabel();

    /* ── Búsqueda local en tabla ── */
    const searchInput = document.getElementById('searchInput');
    searchInput?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#clientesTable .cliente-row').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
})();
</script>
@endpush
