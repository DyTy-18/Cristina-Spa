@extends('admin.layouts.app')

@section('title', $cliente->nombre_completo . ' — Historial')
@section('page-title', 'Historial del Cliente')

@push('styles')
<style>
    /* ===== Client Profile ===== */
    .client-profile {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 2rem;
        display: flex;
        align-items: flex-start;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .client-avatar-lg {
        width: 72px;
        height: 72px;
        background: var(--accent-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--white);
        flex-shrink: 0;
    }

    .client-meta {
        flex: 1;
    }

    .client-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 1px;
        line-height: 1.1;
    }

    .client-details {
        display: flex;
        gap: 1.5rem;
        margin-top: 0.6rem;
        flex-wrap: wrap;
    }

    .client-detail-item {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .client-detail-item strong {
        color: var(--text-dark);
        font-weight: 400;
    }

    /* ===== Period Filter Tabs ===== */
    .period-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 2rem;
        border: 1px solid rgba(0,0,0,0.1);
        background: var(--white);
        width: fit-content;
        flex-wrap: wrap;
    }

    .period-tab {
        padding: 0.6rem 1.3rem;
        font-size: 0.78rem;
        font-weight: 300;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
        color: var(--text-light);
        border-right: 1px solid rgba(0,0,0,0.1);
        transition: var(--transition);
        white-space: nowrap;
    }

    .period-tab:last-child {
        border-right: none;
    }

    .period-tab:hover {
        background: var(--light-bg);
        color: var(--text-dark);
    }

    .period-tab.active {
        background: var(--primary-color);
        color: var(--white);
    }

    /* ===== Timeline ===== */
    .timeline-wrapper {
        position: relative;
    }

    .timeline-month-group {
        margin-bottom: 2.5rem;
    }

    .timeline-month-label {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--secondary-color);
        letter-spacing: 2px;
        margin-bottom: 1rem;
        padding-left: 2.5rem;
        position: relative;
    }

    .timeline-month-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 1.2rem;
        height: 1px;
        background: var(--accent-color);
    }

    .timeline-events {
        position: relative;
        padding-left: 2.5rem;
    }

    .timeline-events::before {
        content: '';
        position: absolute;
        left: 0.55rem;
        top: 0;
        bottom: 0;
        width: 1px;
        background: rgba(0,0,0,0.08);
    }

    .timeline-event {
        position: relative;
        margin-bottom: 1rem;
    }

    .timeline-dot {
        position: absolute;
        left: -2.04rem;
        top: 1.2rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid var(--white);
        box-shadow: 0 0 0 1px rgba(0,0,0,0.15);
        background: var(--text-light);
        z-index: 1;
    }

    .timeline-dot.completada  { background: var(--success-color); box-shadow: 0 0 0 1px var(--success-color); }
    .timeline-dot.confirmada  { background: var(--accent-color);  box-shadow: 0 0 0 1px var(--accent-color); }
    .timeline-dot.pendiente   { background: var(--warning-color); box-shadow: 0 0 0 1px var(--warning-color); }
    .timeline-dot.cancelada   { background: var(--error-color);   box-shadow: 0 0 0 1px var(--error-color); }

    .timeline-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.2rem 1.5rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        transition: var(--transition);
        flex-wrap: wrap;
    }

    .timeline-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        transform: translateX(2px);
    }

    .timeline-card-left {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        min-width: 0;
    }

    .timeline-date {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
        letter-spacing: 0.5px;
    }

    .timeline-service {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem;
        color: var(--primary-color);
        font-weight: 400;
    }

    .timeline-stylist {
        font-size: 0.82rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .timeline-card-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.4rem;
        flex-shrink: 0;
    }

    .timeline-price {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem;
        color: var(--secondary-color);
    }

    .timeline-notas {
        font-size: 0.78rem;
        color: var(--text-light);
        font-style: italic;
        margin-top: 0.4rem;
        font-weight: 300;
    }

    /* ===== Líneas de servicio en modal ===== */
    .linea-modal {
        display: grid;
        grid-template-columns: 1fr 90px auto;
        gap: 0.6rem;
        align-items: end;
        padding: 0.6rem;
        background: var(--light-bg);
        border: 1px solid rgba(0,0,0,0.06);
        margin-bottom: 0.5rem;
    }
    .linea-prof-modal {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 0.6rem;
        align-items: end;
        padding: 0.6rem;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.07);
        margin-bottom: 0.5rem;
    }
    .linea-precio-modal-wrap { position: relative; }
    .linea-precio-modal-prefix {
        position: absolute;
        left: 0.6rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .linea-precio-modal-input { padding-left: 1.8rem !important; }
    .btn-remove-linea-modal {
        background: none;
        border: 1px solid rgba(0,0,0,0.12);
        color: var(--text-light);
        cursor: pointer;
        padding: 0.4rem 0.55rem;
        font-size: 0.85rem;
        line-height: 1;
        height: 38px;
        transition: var(--transition);
    }
    .btn-remove-linea-modal:hover { color: var(--error-color); border-color: var(--error-color); }

    /* ===== Modal ===== */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop.open { display: flex; }

    .modal-box {
        background: var(--white);
        width: 100%;
        max-width: 720px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .modal-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--primary-color);
        letter-spacing: 1px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        color: var(--text-light);
        padding: 0.2rem 0.5rem;
        transition: var(--transition);
    }
    .modal-close:hover { color: var(--text-dark); }

    .modal-body { padding: 1.5rem; }

    /* ===== Period summary bar ===== */
    .period-summary {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 1.5rem;
        display: flex;
        gap: 2.5rem;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .period-summary-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .period-summary-value {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--primary-color);
        line-height: 1;
    }

    .period-summary-label {
        font-size: 0.72rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* ===== Campaign cards in modal ===== */
    .campana-cards-modal {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.4rem;
    }
    .campana-card-modal {
        border: 1px solid rgba(0,0,0,0.12);
        padding: 0.45rem 0.85rem;
        cursor: pointer;
        font-size: 0.78rem;
        letter-spacing: 0.5px;
        color: var(--text-light);
        background: var(--white);
        transition: var(--transition);
        user-select: none;
    }
    .campana-card-modal:hover { border-color: var(--accent-color); color: var(--text-dark); }
    .campana-card-modal.selected {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--white);
    }
</style>
@endpush

@section('content')

    {{-- Back + actions --}}
    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.clientes.index') }}" class="btn btn-sm btn-outline">← Todos los clientes</a>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-sm btn-outline">
                Editar datos
            </a>
            <button type="button" class="btn btn-accent btn-sm" onclick="document.getElementById('modalVisita').classList.add('open')">
                + Registrar visita
            </button>
        </div>
    </div>

    {{-- Profile card --}}
    <div class="client-profile">
        <div class="client-avatar-lg">{{ mb_substr($cliente->nombre, 0, 1) }}</div>
        <div class="client-meta">
            <div class="client-name">{{ $cliente->nombre_completo }}</div>
            <div class="client-details">
                @if($cliente->email)
                    <span class="client-detail-item"><strong>Email</strong> {{ $cliente->email }}</span>
                @endif
                @if($cliente->telefono)
                    <span class="client-detail-item"><strong>Tel.</strong> {{ $cliente->telefono }}</span>
                @endif
                @if($cliente->fecha_nacimiento)
                    <span class="client-detail-item"><strong>Nacimiento</strong> {{ $cliente->fecha_nacimiento->format('d/m/Y') }}</span>
                @endif
                @if($cliente->direccion)
                    <span class="client-detail-item"><strong>Dir.</strong> {{ $cliente->direccion }}</span>
                @endif
            </div>
            @if($cliente->notas)
                <p style="margin-top:0.6rem;font-size:0.82rem;color:var(--text-light);font-style:italic;font-weight:300;">
                    {{ $cliente->notas }}
                </p>
            @endif
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon">💆</div>
            <div class="stat-value">{{ $stats['citas_completadas'] }}</div>
            <div class="stat-label">Visitas completadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">Bs. {{ number_format($stats['total_gastado'], 0) }}</div>
            <div class="stat-label">Total invertido</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-value" style="font-size:1.6rem;">
                {{ $stats['primera_visita'] ? \Carbon\Carbon::parse($stats['primera_visita'])->format('d/m/Y') : '—' }}
            </div>
            <div class="stat-label">Primera visita</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-value" style="font-size:1.4rem;line-height:1.2;">
                {{ $servicioFavorito?->servicio?->nombre ?? '—' }}
            </div>
            <div class="stat-label">Servicio favorito</div>
        </div>
    </div>

    {{-- Period filter --}}
    <div class="period-tabs">
        @php
            $tabs = ['todo' => 'Todo el historial', 'anio' => 'Este año', 'trimestre' => 'Último trimestre', 'mes' => 'Este mes'];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.clientes.show', [$cliente, 'periodo' => $key]) }}"
               class="period-tab {{ $periodo === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Period summary bar --}}
    @if($citas->count() > 0)
        <div class="period-summary">
            <div class="period-summary-item">
                <span class="period-summary-value">{{ $citas->count() }}</span>
                <span class="period-summary-label">Citas en período</span>
            </div>
            <div class="period-summary-item">
                <span class="period-summary-value">{{ $citas->where('estado','completada')->count() }}</span>
                <span class="period-summary-label">Completadas</span>
            </div>
            <div class="period-summary-item">
                <span class="period-summary-value">Bs. {{ number_format($citas->where('estado','completada')->sum('precio_final'), 0) }}</span>
                <span class="period-summary-label">Gastado en período</span>
            </div>
            @if($citas->where('estado','cancelada')->count() > 0)
                <div class="period-summary-item">
                    <span class="period-summary-value" style="color:var(--error-color);">{{ $citas->where('estado','cancelada')->count() }}</span>
                    <span class="period-summary-label">Canceladas</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Timeline --}}
    @if($citas->isEmpty())
        <div class="table-container">
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p class="empty-state-text">No hay citas registradas en este período</p>
                <a href="{{ route('admin.clientes.show', [$cliente, 'periodo' => 'todo']) }}" class="btn btn-outline">
                    Ver todo el historial
                </a>
            </div>
        </div>
    @else
        <div class="timeline-wrapper">
            @foreach($citasPorMes as $mesKey => $citasDelMes)
                @php
                    $fecha = \Carbon\Carbon::createFromFormat('Y-m', $mesKey);
                    $mesLabel = mb_convert_case($fecha->translatedFormat('F Y'), MB_CASE_TITLE, 'UTF-8');
                @endphp
                <div class="timeline-month-group">
                    <div class="timeline-month-label">{{ $mesLabel }}</div>
                    <div class="timeline-events">
                        @foreach($citasDelMes as $cita)
                            <div class="timeline-event">
                                <div class="timeline-dot {{ $cita->estado }}"></div>
                                <div class="timeline-card">
                                    <div class="timeline-card-left">
                                        <div class="timeline-date">
                                            {{ \Carbon\Carbon::parse($cita->fecha)->format('l d') }} &nbsp;·&nbsp;
                                            {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                                        </div>
                                        @php
                                            $nombresServs = $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter();
                                            $nombresEmps  = $cita->citaServicios->filter(fn($cs) => $cs->empleado)->map(fn($cs) => $cs->empleado->nombre . ' ' . $cs->empleado->apellido)->unique();
                                        @endphp
                                        <div class="timeline-service">
                                            {{ $nombresServs->isNotEmpty() ? $nombresServs->implode(' + ') : 'Servicio no encontrado' }}
                                        </div>
                                        @if($nombresEmps->isNotEmpty())
                                            <div class="timeline-stylist">
                                                con {{ $nombresEmps->implode(', ') }}
                                            </div>
                                        @endif
                                        @if($cita->notas)
                                            <div class="timeline-notas">{{ $cita->notas }}</div>
                                        @endif
                                    </div>
                                    <div class="timeline-card-right">
                                        @php
                                            $badgeMap = [
                                                'completada' => 'badge-success',
                                                'confirmada' => 'badge-info',
                                                'pendiente'  => 'badge-warning',
                                                'cancelada'  => 'badge-danger',
                                            ];
                                        @endphp
                                        <span class="badge {{ $badgeMap[$cita->estado] ?? 'badge-info' }}">
                                            {{ $cita->estado }}
                                        </span>
                                        @if($cita->precio_final)
                                            <div class="timeline-price">
                                                Bs. {{ number_format($cita->precio_final, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal: Registrar visita --}}
    <div id="modalVisita" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Registrar visita</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('modalVisita').classList.remove('open')">✕</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.clientes.storeCita', $cliente) }}" method="POST">
                    @csrf

                    {{-- Servicios --}}
                    <div class="form-group">
                        <label class="form-label" style="margin-bottom:0.4rem;">Servicios <span style="color:var(--error-color)">*</span></label>
                        <div style="display:grid;grid-template-columns:1fr 90px auto;gap:0.4rem;margin-bottom:0.3rem;padding:0 0.1rem;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Servicio</span>
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Precio</span>
                            <span></span>
                        </div>
                        <div id="modalServiciosContainer">
                            <div class="linea-modal" data-index="0">
                                <select name="servicios[0][servicio_id]" class="form-control modal-select-servicio" required
                                        onchange="onModalServicioChange(this)">
                                    <option value="">— Seleccionar —</option>
                                    @foreach($servicios as $s)
                                        <option value="{{ $s->id }}" data-precio="{{ $s->precio }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="linea-precio-modal-wrap">
                                    <span class="linea-precio-modal-prefix">Bs.</span>
                                    <input type="number" name="servicios[0][precio]" class="form-control linea-precio-modal-input"
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <button type="button" class="btn-remove-linea-modal" onclick="removeModalServicio(this)" title="Quitar">✕</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addModalServicio()" style="margin-top:0.25rem;">
                            + Agregar servicio
                        </button>
                    </div>

                    {{-- Profesionales --}}
                    <div class="form-group" style="margin-top:1rem;">
                        <label class="form-label" style="margin-bottom:0.2rem;">Profesionales</label>
                        <div style="font-size:0.75rem;color:var(--text-light);font-style:italic;margin-bottom:0.4rem;">Opcional — asigna quién realizó cada servicio</div>
                        <div id="modalProfHeadersRow" style="display:none;grid-template-columns:1fr 1fr auto;gap:0.4rem;margin-bottom:0.3rem;padding:0 0.1rem;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Profesional</span>
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Realizó</span>
                            <span></span>
                        </div>
                        <div id="modalProfesionalesContainer"></div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addModalProfesional()" style="margin-top:0.25rem;">
                            + Agregar profesional
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha <span style="color:var(--error-color)">*</span></label>
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hora <span style="color:var(--error-color)">*</span></label>
                            <input type="time" name="hora" class="form-control" value="10:00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estado <span style="color:var(--error-color)">*</span></label>
                        <select name="estado" class="form-control" required>
                            <option value="completada" selected>Completada</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas de la visita</label>
                        <textarea name="notas" class="form-control" rows="2"
                                  placeholder="Observaciones, preferencias del cliente..."></textarea>
                    </div>

                    @if($campanas->isNotEmpty())
                    <div class="form-group">
                        <label class="form-label">Campaña <span style="font-size:0.75rem;font-weight:300;color:var(--text-light);">(opcional)</span></label>
                        <input type="hidden" name="campana_id" id="modalCampanaId" value="">
                        <div class="campana-cards-modal">
                            <div class="campana-card-modal selected" data-id="" onclick="selectModalCampana(this)">Sin campaña</div>
                            @foreach($campanas as $camp)
                                <div class="campana-card-modal" data-id="{{ $camp->id }}" onclick="selectModalCampana(this)">{{ $camp->nombre }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                        <button type="submit" class="btn btn-primary">Guardar visita</button>
                        <button type="button" class="btn btn-outline"
                                onclick="document.getElementById('modalVisita').classList.remove('open')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const MODAL_SERVICIOS = @json($modalServiciosJson);
    const MODAL_EMPLEADOS = @json($modalEmpleadosJson);

    let modalServicioIdx = 1;
    let modalProfIdx     = 0;
    const modalServiciosSeleccionados = new Map();

    // ===== Servicios del modal =====
    function buildModalServicioOptions() {
        return '<option value="">— Seleccionar —</option>' +
            MODAL_SERVICIOS.map(s => `<option value="${s.id}" data-precio="${s.precio}">${s.nombre}</option>`).join('');
    }

    function onModalServicioChange(sel) {
        const row   = sel.closest('.linea-modal');
        const idx   = row.dataset.index;
        const opt   = sel.options[sel.selectedIndex];
        const input = row.querySelector('.linea-precio-modal-input');

        if (opt.value) {
            modalServiciosSeleccionados.set(idx, { servicio_id: opt.value, nombre: opt.text });
            if (input) input.value = parseFloat(opt.dataset.precio || 0).toFixed(2);
        } else {
            modalServiciosSeleccionados.delete(idx);
            if (input) input.value = '';
        }
        rebuildModalProfServDropdowns();
    }

    function addModalServicio() {
        const idx = String(modalServicioIdx++);
        const div = document.createElement('div');
        div.className = 'linea-modal';
        div.dataset.index = idx;
        div.innerHTML = `
            <select name="servicios[${idx}][servicio_id]" class="form-control modal-select-servicio" required
                    onchange="onModalServicioChange(this)">
                ${buildModalServicioOptions()}
            </select>
            <div class="linea-precio-modal-wrap">
                <span class="linea-precio-modal-prefix">Bs.</span>
                <input type="number" name="servicios[${idx}][precio]" class="form-control linea-precio-modal-input"
                       step="0.01" min="0" placeholder="0.00">
            </div>
            <button type="button" class="btn-remove-linea-modal" onclick="removeModalServicio(this)" title="Quitar">✕</button>`;
        document.getElementById('modalServiciosContainer').appendChild(div);
    }

    function removeModalServicio(btn) {
        const container = document.getElementById('modalServiciosContainer');
        if (container.querySelectorAll('.linea-modal').length <= 1) return;
        const row = btn.closest('.linea-modal');
        modalServiciosSeleccionados.delete(row.dataset.index);
        row.remove();
        rebuildModalProfServDropdowns();
    }

    // ===== Profesionales del modal =====
    function buildModalEmpleadoOptions() {
        return '<option value="">— Seleccionar —</option>' +
            MODAL_EMPLEADOS.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
    }

    function buildModalProfServicioOptions() {
        const items = [...modalServiciosSeleccionados.values()];
        if (items.length === 0) return '<option value="">— Elige servicios primero —</option>';
        return '<option value="">— Qué realizó —</option>' +
            items.map(s => `<option value="${s.servicio_id}">${s.nombre}</option>`).join('');
    }

    function rebuildModalProfServDropdowns() {
        document.querySelectorAll('.modal-select-prof-serv').forEach(sel => {
            const current = sel.value;
            const items   = [...modalServiciosSeleccionados.values()];
            sel.innerHTML = '<option value="">— Qué realizó —</option>' +
                items.map(s => `<option value="${s.servicio_id}"${s.servicio_id == current ? ' selected' : ''}>${s.nombre}</option>`).join('');
        });
    }

    function addModalProfesional() {
        const idx = modalProfIdx++;
        document.getElementById('modalProfHeadersRow').style.display = 'grid';
        const div = document.createElement('div');
        div.className = 'linea-prof-modal';
        div.dataset.index = idx;
        div.innerHTML = `
            <select name="profesionales[${idx}][empleado_id]" class="form-control">
                ${buildModalEmpleadoOptions()}
            </select>
            <select name="profesionales[${idx}][servicio_id]" class="form-control modal-select-prof-serv">
                ${buildModalProfServicioOptions()}
            </select>
            <button type="button" class="btn-remove-linea-modal" onclick="removeModalProfesional(this)" title="Quitar">✕</button>`;
        document.getElementById('modalProfesionalesContainer').appendChild(div);
    }

    function removeModalProfesional(btn) {
        btn.closest('.linea-prof-modal').remove();
        if (document.querySelectorAll('.linea-prof-modal').length === 0) {
            document.getElementById('modalProfHeadersRow').style.display = 'none';
        }
    }

    function selectModalCampana(card) {
        document.querySelectorAll('.campana-card-modal').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('modalCampanaId').value = card.dataset.id;
    }
</script>
@endpush
