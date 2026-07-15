@extends('admin.layouts.app')

@section('title', 'Citas')
@section('page-title', 'Gestión de Citas')

@push('styles')
<style>
    .filters-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
        padding: 1rem 1.5rem;
        background: var(--white);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .filters-bar .search-input,
    .filters-bar select {
        height: 38px;
        padding: 0 0.9rem;
        border: 1px solid rgba(0,0,0,0.1);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.85rem;
        font-weight: 300;
        color: var(--text-dark);
        background: var(--white);
        outline: none;
        transition: var(--transition);
    }
    .filters-bar .search-input { flex: 1; min-width: 200px; }
    .filters-bar select { min-width: 170px; }
    .filters-bar .search-input:focus,
    .filters-bar select:focus { border-color: var(--accent-color); }

    .citas-table th { white-space: nowrap; }

    .estado-select {
        appearance: none;
        -webkit-appearance: none;
        padding: 0.25rem 1.6rem 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
        letter-spacing: 0.3px;
        border: none;
        cursor: pointer;
        background-repeat: no-repeat;
        background-position: right 0.45rem center;
        background-size: 10px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E");
        outline: none;
        transition: opacity 0.15s;
    }
    .estado-select:hover { opacity: 0.85; }
    .estado-select.pendiente  { background-color: #fff3cd; color: #856404; }
    .estado-select.confirmada { background-color: #f5ecd7; color: #7a5c2e; }
    .estado-select.completada { background-color: #d1e7dd; color: #0f5132; }
    .estado-select.cancelada  { background-color: #f8d7da; color: #842029; }

    .servicios-list { font-size: 0.8rem; color: var(--text-light); margin-top: 0.15rem; }

    .action-btns { display: flex; gap: 0.4rem; }

    .pagination-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.25rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .pagination-wrap .pag-info { font-size: 0.82rem; color: var(--text-light); }
    .pagination-links { display: flex; gap: 0.25rem; }
    .pagination-links a,
    .pagination-links span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 0.5rem;
        border: 1px solid rgba(0,0,0,0.12);
        border-radius: 4px;
        font-size: 0.82rem;
        color: var(--text-dark);
        text-decoration: none;
        background: var(--white);
        transition: background 0.15s;
    }
    .pagination-links a:hover { background: #f5ecd7; border-color: var(--secondary-color); }
    .pagination-links span.active { background: var(--secondary-color); color: #fff; border-color: var(--secondary-color); }
    .pagination-links span.disabled { opacity: 0.4; pointer-events: none; }

    /* Modal contraseña (editar/eliminar) */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: var(--white);
        border-radius: 8px;
        padding: 2rem;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    .modal-box h4 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--primary-color);
        margin-bottom: 0.35rem;
        font-weight: 400;
    }
    .modal-box p { font-size: 0.85rem; color: var(--text-light); margin-bottom: 1.25rem; }
    .modal-box input[type="password"] {
        width: 100%;
        height: 40px;
        padding: 0 0.75rem;
        border: 1px solid rgba(0,0,0,0.18);
        border-radius: 4px;
        font-size: 0.9rem;
        outline: none;
        margin-bottom: 0.5rem;
        box-sizing: border-box;
    }
    .modal-box input[type="password"]:focus { border-color: var(--secondary-color); }
    .modal-error { font-size: 0.8rem; color: #842029; margin-bottom: 0.75rem; min-height: 1rem; }
    .modal-actions { display: flex; gap: 0.6rem; justify-content: flex-end; margin-top: 0.5rem; }

    /* ===== Modal grande: Nueva cita (mismo formato que "Registrar visita") ===== */
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

    .nc-modal-box {
        background: var(--white);
        width: 100%;
        max-width: 820px;
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

    /* Toggle cliente */
    .cliente-toggle {
        display: flex;
        border: 1px solid rgba(0,0,0,0.1);
        margin-bottom: 0.75rem;
        width: fit-content;
    }
    .toggle-btn {
        padding: 0.5rem 1.2rem;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.75rem;
        font-weight: 300;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        background: var(--white);
        border: none;
        cursor: pointer;
        color: var(--text-light);
        transition: var(--transition);
        border-right: 1px solid rgba(0,0,0,0.1);
    }
    .toggle-btn:last-child { border-right: none; }
    .toggle-btn.active { background: var(--primary-color); color: var(--white); }
    .toggle-btn:not(.active):hover { background: var(--light-bg); color: var(--text-dark); }

    /* Buscador de cliente */
    .search-cliente-wrapper { position: relative; }
    .search-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.12);
        z-index: 2100;
        max-height: 220px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .search-dropdown.visible { display: block; }
    .search-option {
        padding: 0.65rem 0.9rem;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 300;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.15s;
    }
    .search-option:hover { background: var(--light-bg); }
    .search-option .opt-name { color: var(--text-dark); font-weight: 400; }
    .search-option .opt-phone { color: var(--text-light); font-size: 0.75rem; }
    .search-option-empty {
        padding: 0.9rem;
        font-size: 0.82rem;
        color: var(--text-light);
        font-weight: 300;
        font-style: italic;
        text-align: center;
    }
    .search-selected-badge {
        display: none;
        align-items: center;
        gap: 0.6rem;
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: rgba(201,169,110,0.12);
        border: 1px solid rgba(201,169,110,0.3);
        font-size: 0.82rem;
        font-weight: 300;
    }
    .search-selected-badge.visible { display: flex; }
    .badge-clear {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-light);
        font-size: 0.9rem;
        margin-left: auto;
        padding: 0;
    }
    .badge-clear:hover { color: var(--error-color); }

    /* Service icon cards */
    .servicio-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
        gap: 0.5rem;
    }
    .servicio-card {
        border: 1px solid rgba(0,0,0,0.1);
        padding: 0.85rem 0.5rem 0.7rem;
        cursor: pointer;
        text-align: center;
        transition: var(--transition);
        background: var(--white);
        user-select: none;
        position: relative;
    }
    .servicio-card:hover { border-color: var(--accent-color); background: var(--light-bg); }
    .servicio-card.sel { border-color: var(--primary-color); background: var(--primary-color); }
    .servicio-card.sel .sc-name { color: var(--white); }
    .servicio-card.sel .sc-price { color: rgba(255,255,255,0.7); }
    .sc-check {
        position: absolute;
        top: 5px; right: 7px;
        font-size: 0.6rem;
        color: var(--white);
        opacity: 0;
        transition: var(--transition);
        font-weight: 700;
        letter-spacing: 0;
    }
    .servicio-card.sel .sc-check { opacity: 1; }
    .sc-icon { font-size: 1.8rem; display: block; margin-bottom: 0.35rem; line-height: 1; }
    .sc-name { font-size: 0.67rem; color: var(--text-dark); line-height: 1.25; letter-spacing: 0.2px; }
    .sc-price { font-size: 0.62rem; color: var(--text-light); margin-top: 0.18rem; }

    /* Price panel for selected services */
    .sc-precio-row {
        display: grid;
        grid-template-columns: 1fr 100px 88px auto;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.7rem;
        background: var(--light-bg);
        border: 1px solid rgba(0,0,0,0.06);
        margin-bottom: 0.35rem;
    }
    .sc-precio-name {
        font-size: 0.8rem;
        color: var(--text-dark);
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sc-precio-wrap { position: relative; }
    .sc-precio-prefix {
        position: absolute;
        left: 0.6rem; top: 50%;
        transform: translateY(-50%);
        font-size: 0.72rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .sc-precio-input { padding-left: 1.9rem !important; width: 100%; }

    .sc-desc-wrap { position: relative; }
    .sc-desc-suffix {
        position: absolute;
        right: 0.55rem; top: 50%;
        transform: translateY(-50%);
        font-size: 0.72rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .sc-desc-input { padding-right: 1.6rem !important; width: 100%; }

    .sc-neto {
        font-size: 0.78rem;
        color: var(--secondary-color);
        font-family: 'Cormorant Garamond', serif;
        font-size: 1rem;
        white-space: nowrap;
        text-align: right;
    }
    .sc-neto.has-desc { color: var(--success-color); }

    /* Líneas de profesionales en modal */
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

    /* Pestañas Servicios / Paquetes en modal */
    .modal-srv-tabs {
        display: flex;
        align-items: stretch;
        border-bottom: 1px solid rgba(0,0,0,.08);
        background: var(--light-bg);
        margin-bottom: 1rem;
    }
    .modal-srv-tab {
        padding: .55rem 1.25rem;
        font-size: .72rem;
        letter-spacing: .7px;
        text-transform: uppercase;
        font-weight: 400;
        cursor: pointer;
        background: transparent;
        border: none;
        color: var(--text-light);
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: var(--transition);
        white-space: nowrap;
    }
    .modal-srv-tab:hover:not(.active) { color: var(--text-dark); }
    .modal-srv-tab.active { color: var(--primary-color); border-bottom-color: var(--accent-color); background: var(--white); font-weight: 500; }
    .modal-pkg-chip {
        display: none;
        align-items: center;
        gap: .3rem;
        margin-left: auto;
        padding: 0 .75rem;
        font-size: .7rem;
        color: #166534;
        background: rgba(34,197,94,.06);
        border-left: 1px solid rgba(0,0,0,.06);
    }
    .modal-pkg-chip.show { display: flex; }

    /* Package cards in modal */
    .modal-pkg-cat-strip { display: flex; gap: 0; margin-bottom: .75rem; flex-wrap: wrap; border: 1px solid rgba(0,0,0,.09); width: fit-content; }
    .modal-pkg-cat-btn {
        padding: .3rem .85rem;
        font-size: .68rem;
        letter-spacing: .5px;
        text-transform: uppercase;
        cursor: pointer;
        background: var(--white);
        border: none;
        border-right: 1px solid rgba(0,0,0,.09);
        color: var(--text-light);
        transition: var(--transition);
    }
    .modal-pkg-cat-btn:last-child { border-right: none; }
    .modal-pkg-cat-btn.active { background: var(--primary-color); color: #fff; }
    .modal-pkg-cat-btn:not(.active):hover { background: var(--light-bg); }

    .modal-pkg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .5rem; }
    .modal-pkg-card {
        border: 1px solid rgba(0,0,0,.08);
        padding: .65rem .75rem;
        cursor: pointer;
        transition: var(--transition);
    }
    .modal-pkg-card:hover { border-color: var(--accent-color); box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .modal-pkg-card.applied { border-color: #22c55e; background: rgba(34,197,94,.05); }

    /* Campaign cards in modal */
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
<div class="table-container">
    <div class="table-header">
        <h3 class="table-title">Todas las Citas</h3>
        <button type="button" class="btn btn-primary" onclick="abrirModalNuevaCita()">+ Nueva Cita</button>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.citas.index') }}" class="filters-bar">
        <input type="text" name="buscar" class="search-input" placeholder="Buscar cliente..." value="{{ request('buscar') }}" autocomplete="off">
        <select name="estado">
            <option value="">Todos los estados</option>
            @foreach(['pendiente','confirmada','completada','cancelada'] as $est)
                <option value="{{ $est }}" @selected(request('estado') === $est)>{{ ucfirst($est) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary" style="height:38px;padding:0 1rem;">Filtrar</button>
        @if(request()->hasAny(['buscar','estado']))
            <a href="{{ route('admin.citas.index') }}" class="btn btn-secondary" style="height:38px;padding:0 1rem;display:inline-flex;align-items:center;">Limpiar</a>
        @endif
    </form>

    @if($citas->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p class="empty-state-text">No hay citas que coincidan con los filtros</p>
            <button type="button" class="btn btn-primary" onclick="abrirModalNuevaCita()">Crear Primera Cita</button>
        </div>
    @else
        <div class="table-responsive">
            <table class="data-table citas-table">
                <thead>
                    <tr>
                        <th>Fecha & Hora</th>
                        <th>Cliente</th>
                        <th>Servicios</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($citas as $cita)
                        <tr>
                            <td>
                                <strong>{{ $cita->fecha->format('d/m/Y') }}</strong>
                                <div style="font-size:0.8rem;color:var(--text-light);">
                                    {{ $cita->getRawOriginal('hora') ? \Illuminate\Support\Str::substr($cita->getRawOriginal('hora'), 0, 5) : '—' }}
                                    @if($cita->sucursal)
                                        · {{ $cita->sucursal->nombre }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($cita->cliente)
                                    <a href="{{ route('admin.clientes.show', $cita->cliente) }}" style="color:var(--primary-color);text-decoration:none;font-weight:500;">
                                        {{ $cita->cliente->nombre_completo }}
                                    </a>
                                @else
                                    <span style="color:var(--text-light);">Sin cliente</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $servicioNombres = $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter();
                                    $empleadoNombres = $cita->citaServicios->map(fn($cs) => $cs->empleado?->nombre)->filter()->unique();
                                @endphp
                                <span>{{ $servicioNombres->implode(' + ') ?: '—' }}</span>
                                @if($empleadoNombres->isNotEmpty())
                                    <div class="servicios-list">{{ $empleadoNombres->implode(', ') }}</div>
                                @endif
                            </td>
                            <td>
                                @if($cita->precio_final)
                                    <strong>Bs {{ number_format($cita->precio_final, 2) }}</strong>
                                @else
                                    <span style="color:var(--text-light);">—</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.citas.estado', $cita) }}">
                                    @csrf @method('PATCH')
                                    <select name="estado" class="estado-select {{ $cita->estado }}" onchange="this.form.submit()" title="Cambiar estado">
                                        @foreach(['pendiente','confirmada','completada','cancelada'] as $est)
                                            <option value="{{ $est }}" @selected($cita->estado === $est)>{{ ucfirst($est) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="{{ route('admin.citas.show', $cita) }}"
                                       class="btn btn-secondary"
                                       style="padding:0.25rem 0.6rem;font-size:0.8rem;"
                                       title="Ver detalles">Ver</a>

                                    @if($cita->estado === 'completada')
                                        <button type="button"
                                                class="btn btn-primary"
                                                style="padding:0.25rem 0.6rem;font-size:0.8rem;"
                                                onclick="abrirModalEditar('{{ route('admin.citas.verificarEdicion', $cita) }}')"
                                                title="Editar (requiere contraseña)">Editar</button>
                                    @else
                                        <a href="{{ route('admin.citas.edit', $cita) }}"
                                           class="btn btn-primary"
                                           style="padding:0.25rem 0.6rem;font-size:0.8rem;"
                                           title="Editar">Editar</a>
                                    @endif

                                    @hasanyrole('admin|encargado|developer')
                                        <button type="button"
                                                class="btn btn-danger"
                                                style="padding:0.25rem 0.6rem;font-size:0.8rem;"
                                                onclick="abrirModalEliminarCita('{{ route('admin.citas.destroy', $cita) }}')"
                                                title="Eliminar (requiere contraseña)">Eliminar</button>
                                    @endhasanyrole
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="pagination-wrap">
            <span class="pag-info">
                Mostrando {{ $citas->firstItem() }}–{{ $citas->lastItem() }} de {{ $citas->total() }} citas
            </span>
            <div class="pagination-links">
                @if($citas->onFirstPage())
                    <span class="disabled">‹</span>
                @else
                    <a href="{{ $citas->previousPageUrl() }}">‹</a>
                @endif

                @foreach($citas->getUrlRange(max(1, $citas->currentPage() - 2), min($citas->lastPage(), $citas->currentPage() + 2)) as $page => $url)
                    @if($page === $citas->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($citas->hasMorePages())
                    <a href="{{ $citas->nextPageUrl() }}">›</a>
                @else
                    <span class="disabled">›</span>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Modal contraseña para editar cita completada --}}
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box">
        <h4>Editar cita completada</h4>
        <p>Esta cita ya fue completada. Ingresa la contraseña de administrador para continuar.</p>
        <form id="formVerificar" method="POST">
            @csrf
            <input type="password" name="password" id="modalPassword"
                   placeholder="Contraseña" autocomplete="current-password" required>
            <div class="modal-error" id="modalError">
                @if($errors->has('password'))
                    {{ $errors->first('password') }}
                @endif
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

@hasanyrole('admin|encargado|developer')
{{-- Modal contraseña para eliminar cita --}}
<div class="modal-overlay" id="modalEliminarCita">
    <div class="modal-box">
        <h4>Eliminar cita</h4>
        <p>Esta acción no se puede deshacer. Ingresa la contraseña de administrador para continuar.</p>
        <form id="formEliminarCita" method="POST">
            @csrf @method('DELETE')
            <input type="password" name="password" id="modalEliminarCitaPassword"
                   placeholder="Contraseña" autocomplete="current-password" required>
            <div class="modal-error" id="modalEliminarCitaError">
                @if($errors->has('password') && session('confirmar_eliminar_cita_id'))
                    {{ $errors->first('password') }}
                @endif
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEliminarCita()">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endhasanyrole

{{-- Modal: Nueva cita (mismo formato "popup" que Registrar visita) --}}
<div id="modalNuevaCita" class="modal-backdrop" onclick="if(event.target===this)cerrarModalNuevaCita()">
    <div class="nc-modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Nueva cita</h3>
            <button type="button" class="modal-close" onclick="cerrarModalNuevaCita()">✕</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.citas.store') }}" method="POST" id="formNuevaCita">
                @csrf
                <input type="hidden" name="_origen" value="popup">
                <input type="hidden" name="_form" value="nueva_cita">
                <input type="hidden" name="cliente_tipo" id="ncClienteTipo" value="{{ old('cliente_tipo', 'existente') }}">

                {{-- Cliente --}}
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <div class="cliente-toggle">
                        <button type="button" class="toggle-btn {{ old('cliente_tipo','existente') === 'existente' ? 'active' : '' }}"
                                id="ncBtnExistente" onclick="ncSwitchCliente('existente')">
                            Cliente registrado
                        </button>
                        <button type="button" class="toggle-btn {{ old('cliente_tipo') === 'nuevo' ? 'active' : '' }}"
                                id="ncBtnNuevo" onclick="ncSwitchCliente('nuevo')">
                            + Nuevo cliente
                        </button>
                    </div>

                    <div id="ncSectionExistente" style="{{ old('cliente_tipo') === 'nuevo' ? 'display:none' : '' }}">
                        <div class="search-cliente-wrapper">
                            <input type="text" id="ncSearchInput" class="form-control"
                                   placeholder="Nombre, apellido o teléfono..."
                                   autocomplete="off" value="{{ old('_cliente_display', '') }}">
                            <input type="hidden" name="cliente_id" id="ncClienteIdInput" value="{{ old('cliente_id') }}">
                            <div id="ncSearchDropdown" class="search-dropdown"></div>
                        </div>
                        <div class="search-selected-badge {{ old('cliente_id') ? 'visible' : '' }}" id="ncSelectedBadge">
                            <span id="ncSelectedName">{{ old('_cliente_display') }}</span>
                            <button type="button" class="badge-clear" onclick="ncClearCliente()" title="Cambiar">✕</button>
                        </div>
                        @error('cliente_id')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div id="ncSectionNuevo" style="{{ old('cliente_tipo') !== 'nuevo' ? 'display:none;' : '' }}margin-top:0.5rem;">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nombre <span style="color:var(--error-color)">*</span></label>
                                <input type="text" name="nuevo_nombre" class="form-control"
                                       value="{{ old('nuevo_nombre') }}" placeholder="Nombre">
                                @error('nuevo_nombre')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Apellido</label>
                                <input type="text" name="nuevo_apellido" class="form-control"
                                       value="{{ old('nuevo_apellido') }}" placeholder="Apellido">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Teléfono <span style="color:var(--error-color)">*</span></label>
                                <input type="text" name="nuevo_telefono" class="form-control"
                                       value="{{ old('nuevo_telefono') }}" placeholder="70000000">
                                @error('nuevo_telefono')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="nuevo_email" class="form-control"
                                       value="{{ old('nuevo_email') }}" placeholder="correo@ejemplo.com">
                                @error('nuevo_email')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pestañas Servicios / Paquetes / Productos --}}
                <div class="form-group">
                    <div class="modal-srv-tabs">
                        <button type="button" class="modal-srv-tab active" id="ncTabBtnServicios"
                            onclick="ncSwitchTab('servicios')">✂️ Servicios</button>
                        <button type="button" class="modal-srv-tab" id="ncTabBtnPaquetes"
                            onclick="ncSwitchTab('paquetes')">🎁 Paquetes</button>
                        <button type="button" class="modal-srv-tab" id="ncTabBtnProductos"
                            onclick="ncSwitchTab('productos')">🛍️ Productos</button>
                        <div class="modal-pkg-chip" id="ncPkgChip">
                            <span>✓</span>
                            <span id="ncPkgChipText"></span>
                            <button type="button" onclick="ncClearPaquete()"
                                style="background:none;border:none;cursor:pointer;color:#166534;font-size:.85rem;padding:0;"
                                title="Quitar paquete">✕</button>
                        </div>
                        <span id="ncScCountBadge" style="display:none;margin-left:auto;margin-right:.75rem;align-self:center;font-size:.72rem;font-weight:400;color:var(--accent-color);letter-spacing:0.5px;"></span>
                    </div>

                    {{-- Panel: Servicios --}}
                    <div id="ncPanelServicios">
                        <div class="modal-pkg-cat-strip" id="ncCatStrip" style="margin-bottom:0.6rem;">
                            <button type="button" class="modal-pkg-cat-btn active" data-cat="" onclick="ncFilterCat(this, '')">Todas</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="peluqueria" onclick="ncFilterCat(this, 'peluqueria')">Peluquería</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="peinados" onclick="ncFilterCat(this, 'peinados')">Peinados</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="coloracion" onclick="ncFilterCat(this, 'coloracion')">Coloración</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="alisado" onclick="ncFilterCat(this, 'alisado')">Alisado</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="depilacion" onclick="ncFilterCat(this, 'depilacion')">Depilación</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="maquillaje" onclick="ncFilterCat(this, 'maquillaje')">Maquillaje</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="pies_manos" onclick="ncFilterCat(this, 'pies_manos')">Pies y Manos</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="extensiones" onclick="ncFilterCat(this, 'extensiones')">Extensiones</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="spa" onclick="ncFilterCat(this, 'spa')">Spa</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="tratamientos" onclick="ncFilterCat(this, 'tratamientos')">Tratamientos</button>
                        </div>
                        <div id="ncServicioCardsGrid" class="servicio-cards-grid"></div>
                        <div id="ncErrorMsg" style="display:none;color:var(--error-color);font-size:0.78rem;margin-top:0.4rem;">
                            Selecciona al menos un servicio para continuar.
                        </div>

                        <div id="ncPreciosPanel" style="margin-top:0.9rem;display:none;">
                            <div style="display:grid;grid-template-columns:1fr 100px 88px auto;gap:0.5rem;padding:0 0.7rem;margin-bottom:0.3rem;">
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Servicio</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Precio</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Descuento</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);text-align:right;">Neto</span>
                            </div>
                            <div id="ncPreciosContainer"></div>
                        </div>

                        <div id="ncHiddenInputs"></div>
                    </div>

                    {{-- Panel: Paquetes --}}
                    <div id="ncPanelPaquetes" style="display:none;">
                        <div class="modal-pkg-cat-strip">
                            <button type="button" class="modal-pkg-cat-btn active" data-cat="todos" onclick="ncFilterPkgCat('todos')">Todos</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="novia" onclick="ncFilterPkgCat('novia')">👰 Novia</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="quinceañera" onclick="ncFilterPkgCat('quinceañera')">🌸 Quinceañera</button>
                            <button type="button" class="modal-pkg-cat-btn" data-cat="eventos" onclick="ncFilterPkgCat('eventos')">🎉 Eventos</button>
                        </div>
                        <div class="modal-pkg-grid" id="ncPkgCards"></div>
                        <p style="font-size:.72rem;color:var(--text-light);margin-top:.65rem;font-style:italic;">
                            Al seleccionar un paquete se marcan los servicios automáticamente.
                        </p>
                    </div>

                    {{-- Panel: Productos --}}
                    <div id="ncPanelProductos" style="display:none;">
                        <div id="ncPcCountBadge" style="display:none;font-size:.72rem;font-weight:400;color:var(--accent-color);letter-spacing:0.5px;margin-bottom:0.4rem;"></div>
                        <div id="ncProductoCardsGrid" class="servicio-cards-grid"></div>
                        @if($productos->isEmpty())
                            <p style="font-size:.78rem;color:var(--text-light);font-style:italic;margin-top:.5rem;">No hay productos registrados.</p>
                        @endif

                        <div id="ncProductosPreciosPanel" style="margin-top:0.9rem;display:none;">
                            <div style="display:grid;grid-template-columns:1fr 80px 100px 88px auto;gap:0.5rem;padding:0 0.7rem;margin-bottom:0.3rem;">
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Producto</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Cant.</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Precio</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Descuento</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);text-align:right;">Neto</span>
                            </div>
                            <div id="ncProductosPreciosContainer"></div>
                        </div>

                        <div id="ncProductosHiddenInputs"></div>
                    </div>
                </div>

                {{-- Profesionales --}}
                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label" style="margin-bottom:0.2rem;">Profesionales</label>
                    <div style="font-size:0.75rem;color:var(--text-light);font-style:italic;margin-bottom:0.4rem;">Opcional — asigna quién realizó cada servicio</div>
                    <div id="ncProfHeadersRow" style="display:none;grid-template-columns:1fr 1fr auto;gap:0.4rem;margin-bottom:0.3rem;padding:0 0.1rem;">
                        <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Profesional</span>
                        <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Realizó</span>
                        <span></span>
                    </div>
                    <div id="ncProfesionalesContainer"></div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="ncAddProfesional()" style="margin-top:0.25rem;">
                        + Agregar profesional
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha <span style="color:var(--error-color)">*</span></label>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', date('Y-m-d')) }}" required>
                        @error('fecha')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora <span style="color:var(--error-color)">*</span></label>
                        <input type="time" name="hora" class="form-control" value="{{ old('hora', '10:00') }}" required>
                        @error('hora')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Estado <span style="color:var(--error-color)">*</span></label>
                    <select name="estado" class="form-control" required>
                        @foreach(['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $val => $label)
                            <option value="{{ $val }}" {{ old('estado', 'pendiente') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo de pago</label>
                    <select name="tipo_pago" class="form-control">
                        <option value="">— Sin especificar —</option>
                        <option value="efectivo" {{ old('tipo_pago') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        <option value="tarjeta" {{ old('tipo_pago') === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                        <option value="qr" {{ old('tipo_pago') === 'qr' ? 'selected' : '' }}>QR</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sucursal <span style="color:var(--error-color)">*</span></label>
                    <select name="sucursal_id" class="form-control" required>
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}"
                                {{ (old('sucursal_id', $sucursalActiva->id ?? null) == $s->id) ? 'selected' : '' }}>
                                {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" class="form-control" rows="2"
                              placeholder="Observaciones, preferencias del cliente...">{{ old('notas') }}</textarea>
                </div>

                @if($campanas->isNotEmpty())
                <div class="form-group">
                    <label class="form-label">Campaña <span style="font-size:0.75rem;font-weight:300;color:var(--text-light);">(opcional)</span></label>
                    <input type="hidden" name="campana_id" id="ncCampanaId" value="{{ old('campana_id') }}">
                    <div class="campana-cards-modal">
                        <div class="campana-card-modal {{ old('campana_id') ? '' : 'selected' }}" data-id="" onclick="ncSelectCampana(this)">Sin campaña</div>
                        @foreach($campanas as $camp)
                            <div class="campana-card-modal {{ old('campana_id') == $camp->id ? 'selected' : '' }}" data-id="{{ $camp->id }}" onclick="ncSelectCampana(this)">{{ $camp->nombre }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Sección contrato (aparece al seleccionar un paquete) --}}
                <div id="ncContratoSection" style="display:none;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid rgba(201,169,110,0.25);background:rgba(201,169,110,0.04);padding:0.9rem;border:1px solid rgba(201,169,110,0.2);">
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--accent-color);font-weight:500;margin-bottom:0.65rem;">📋 Contrato del paquete</div>
                    <input type="hidden" name="crear_contrato" id="ncContrCrear" value="0">
                    <input type="hidden" name="paquete_id" id="ncContrPaqueteId" value="">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:0.5rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Fecha inicio</label>
                            <input type="date" name="contrato_fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Precio total (Bs.)</label>
                            <input type="number" name="contrato_precio_total" id="ncContrPrecio" class="form-control" step="0.01" min="0" placeholder="0.00" oninput="ncUpdateContrSuma()">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                            <div style="font-size:0.72rem;color:var(--text-light);">Pagos registrados</div>
                            <button type="button" class="btn btn-outline btn-sm" onclick="ncAddContrPago()" style="font-size:.7rem;padding:.2rem .6rem;">+ Añadir pago</button>
                        </div>
                        <div id="ncContrPagosContainer">
                            <div style="display:grid;grid-template-columns:1fr 100px 1fr auto;gap:0.35rem;align-items:center;margin-bottom:0.3rem;">
                                <input type="number" name="contrato_pagos[0][monto]" class="form-control ncContrMonto"
                                       step="0.01" min="0" placeholder="Monto (Bs.)" oninput="ncUpdateContrSuma()">
                                <input type="date" name="contrato_pagos[0][fecha_pago]" class="form-control" value="{{ date('Y-m-d') }}">
                                <select name="contrato_pagos[0][metodo_pago]" class="form-control" style="font-size:.78rem;">
                                    <option value="">— Método —</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="qr">QR</option>
                                </select>
                                <span style="font-size:.68rem;color:var(--accent-color);font-weight:600;">★</span>
                            </div>
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.25rem;">
                            Adelantado: Bs. <span id="ncContrSuma" style="color:var(--text-dark);font-weight:400;">0.00</span>
                            · Pendiente: Bs. <span id="ncContrTotal">0.00</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="font-size:0.72rem;">Notas del contrato</label>
                        <textarea name="contrato_notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea>
                    </div>
                </div>

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar cita</button>
                    <button type="button" class="btn btn-outline" onclick="cerrarModalNuevaCita()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.estado-select').forEach(sel => {
    sel.addEventListener('change', function () {
        this.className = 'estado-select ' + this.value;
    });
});

function abrirModalEditar(actionUrl) {
    const modal = document.getElementById('modalEditar');
    document.getElementById('formVerificar').action = actionUrl;
    document.getElementById('modalPassword').value = '';
    document.getElementById('modalError').textContent = '';
    modal.classList.add('active');
    setTimeout(() => document.getElementById('modalPassword').focus(), 50);
}

function cerrarModal() {
    document.getElementById('modalEditar').classList.remove('active');
}

document.getElementById('modalEditar').addEventListener('click', function (e) {
    if (e.target === this) cerrarModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModal();
        cerrarModalEliminarCita();
        cerrarModalNuevaCita();
    }
});

@if(session('verificar_cita_id'))
    // Re-abrir el modal si la verificación falló (el backend devolvió error)
    document.addEventListener('DOMContentLoaded', function () {
        const url = '{{ route('admin.citas.verificarEdicion', session('verificar_cita_id')) }}';
        abrirModalEditar(url);
        document.getElementById('modalError').textContent = '{{ $errors->first('password') }}';
    });
@endif

function abrirModalEliminarCita(actionUrl) {
    const modal = document.getElementById('modalEliminarCita');
    document.getElementById('formEliminarCita').action = actionUrl;
    document.getElementById('modalEliminarCitaPassword').value = '';
    document.getElementById('modalEliminarCitaError').textContent = '';
    modal.classList.add('active');
    setTimeout(() => document.getElementById('modalEliminarCitaPassword').focus(), 50);
}

function cerrarModalEliminarCita() {
    document.getElementById('modalEliminarCita').classList.remove('active');
}

document.getElementById('modalEliminarCita')?.addEventListener('click', function (e) {
    if (e.target === this) cerrarModalEliminarCita();
});

@if(session('confirmar_eliminar_cita_id'))
    document.addEventListener('DOMContentLoaded', function () {
        const url = '{{ route('admin.citas.destroy', session('confirmar_eliminar_cita_id')) }}';
        abrirModalEliminarCita(url);
        document.getElementById('modalEliminarCitaError').textContent = '{{ $errors->first('password') }}';
    });
@endif

// ══════════════════════════════════════════════════════════════════════════
// Modal: Nueva cita
// ══════════════════════════════════════════════════════════════════════════
const NC_CLIENTES  = @json($clientesJson);
const NC_SERVICIOS = @json($serviciosJson);
const NC_EMPLEADOS = @json($empleadosJson);
const NC_PAQUETES  = @json($paquetesJson);
const NC_PRODUCTOS = @json($productosJson);

function abrirModalNuevaCita() {
    document.getElementById('modalNuevaCita').classList.add('open');
}

function cerrarModalNuevaCita() {
    document.getElementById('modalNuevaCita')?.classList.remove('open');
}

// ── Toggle cliente ───────────────────────────────────────────────────────────
function ncSwitchCliente(tipo) {
    document.getElementById('ncClienteTipo').value = tipo;
    document.getElementById('ncBtnExistente').classList.toggle('active', tipo === 'existente');
    document.getElementById('ncBtnNuevo').classList.toggle('active', tipo === 'nuevo');
    document.getElementById('ncSectionExistente').style.display = tipo === 'existente' ? '' : 'none';
    document.getElementById('ncSectionNuevo').style.display    = tipo === 'nuevo'      ? '' : 'none';
}

// ── Buscador de cliente ───────────────────────────────────────────────────────
const ncSearchInput   = document.getElementById('ncSearchInput');
const ncClienteId     = document.getElementById('ncClienteIdInput');
const ncDropdown      = document.getElementById('ncSearchDropdown');
const ncSelectedBadge = document.getElementById('ncSelectedBadge');
const ncSelectedName  = document.getElementById('ncSelectedName');

function ncRenderDropdown(term) {
    const q = term.toLowerCase().trim();
    if (q.length < 1) { ncDropdown.classList.remove('visible'); return; }
    const matches = NC_CLIENTES.filter(c => c.nombre.toLowerCase().includes(q) || c.tel.includes(q)).slice(0, 12);
    if (matches.length === 0) {
        ncDropdown.innerHTML = `<div class="search-option-empty">No se encontró ningún cliente con "${term}"</div>`;
    } else {
        ncDropdown.innerHTML = matches.map(c => `
            <div class="search-option" onclick="ncSelectCliente(${c.id}, '${c.nombre.replace(/'/g,"\\'")}')">
                <span class="opt-name">${c.nombre}</span>
                <span class="opt-phone">${c.tel}</span>
            </div>`).join('');
    }
    ncDropdown.classList.add('visible');
}

function ncSelectCliente(id, nombre) {
    ncClienteId.value = id;
    ncSearchInput.value = nombre;
    ncSearchInput.style.display = 'none';
    ncDropdown.classList.remove('visible');
    ncSelectedName.textContent = nombre;
    ncSelectedBadge.classList.add('visible');
}

function ncClearCliente() {
    ncClienteId.value = '';
    ncSearchInput.value = '';
    ncSearchInput.style.display = '';
    ncSearchInput.focus();
    ncSelectedBadge.classList.remove('visible');
}

ncSearchInput?.addEventListener('input', () => ncRenderDropdown(ncSearchInput.value));
ncSearchInput?.addEventListener('focus', () => { if (ncSearchInput.value) ncRenderDropdown(ncSearchInput.value); });
document.addEventListener('click', e => {
    if (!e.target.closest('.search-cliente-wrapper')) ncDropdown?.classList.remove('visible');
});

// ── Pestañas Servicios / Paquetes ─────────────────────────────────────────────
let ncPkgCat      = 'todos';
let ncPkgAplicado = null;

function ncSwitchTab(tab) {
    document.getElementById('ncPanelServicios').style.display = tab === 'servicios' ? '' : 'none';
    document.getElementById('ncPanelPaquetes').style.display  = tab === 'paquetes'  ? '' : 'none';
    document.getElementById('ncPanelProductos').style.display = tab === 'productos' ? '' : 'none';
    document.getElementById('ncTabBtnServicios').classList.toggle('active', tab === 'servicios');
    document.getElementById('ncTabBtnPaquetes').classList.toggle('active', tab === 'paquetes');
    document.getElementById('ncTabBtnProductos').classList.toggle('active', tab === 'productos');
    if (tab === 'paquetes' && document.getElementById('ncPkgCards').children.length === 0) {
        ncRenderPkgCards();
    }
    if (tab === 'productos' && document.getElementById('ncProductoCardsGrid').children.length === 0) {
        ncRenderProductoCards();
    }
}

function ncFilterPkgCat(cat) {
    ncPkgCat = cat;
    document.querySelectorAll('#ncPanelPaquetes .modal-pkg-cat-btn').forEach(t => t.classList.toggle('active', t.dataset.cat === cat));
    ncRenderPkgCards();
}

function ncRenderPkgCards() {
    const wrap = document.getElementById('ncPkgCards');
    if (!wrap) return;
    wrap.innerHTML = '';
    const list = ncPkgCat === 'todos' ? NC_PAQUETES : NC_PAQUETES.filter(p => p.categoria === ncPkgCat);
    if (list.length === 0) {
        wrap.innerHTML = '<p style="font-size:.78rem;color:var(--text-light);font-style:italic;">Sin paquetes en esta categoría.</p>';
        return;
    }
    list.forEach(pkg => {
        const card = document.createElement('div');
        card.className = 'modal-pkg-card' + (pkg.id === ncPkgAplicado ? ' applied' : '');
        const badge = pkg.nivel
            ? `<span style="background:${pkg.nivel.color};color:#fff;font-size:.58rem;font-weight:700;padding:.05rem .35rem;border-radius:2px;letter-spacing:.8px;text-transform:uppercase;display:inline-block;margin-bottom:.25rem;">${pkg.nivel.nombre}</span><br>`
            : '';
        const total  = pkg.precio_total ? `Bs. ${parseFloat(pkg.precio_total).toFixed(0)}` : '';
        const sNames = pkg.servicios.map(s => s.nombre).join(' · ');
        card.innerHTML = `${badge}
            <div style="font-size:.82rem;color:var(--text-dark);font-weight:400;">${pkg.nombre}</div>
            <div style="font-size:.73rem;color:var(--accent-color);font-weight:500;">${total}</div>
            <div style="font-size:.66rem;color:var(--text-light);font-style:italic;line-height:1.3;margin-top:.15rem;">${sNames}</div>`;
        card.addEventListener('click', () => ncApplyPaquete(pkg));
        wrap.appendChild(card);
    });
}

function ncApplyPaquete(pkg) {
    ncScSelected.clear();
    document.querySelectorAll('#ncServicioCardsGrid .servicio-card.sel').forEach(c => c.classList.remove('sel'));

    pkg.servicios.forEach(ps => {
        const id = String(ps.servicio_id);
        ncScSelected.set(id, {
            nombre:    ps.nombre,
            precio:    ps.precio.toFixed(2),
            descuento: ps.descuento > 0 ? String(ps.descuento) : '',
        });
        const card = document.querySelector(`#ncServicioCardsGrid .servicio-card[data-id="${id}"]`);
        if (card) card.classList.add('sel');
    });

    ncPkgAplicado = pkg.id;
    document.getElementById('ncPkgChipText').textContent = pkg.nombre + ' · ' + pkg.servicios.length + ' servicios';
    document.getElementById('ncPkgChip').classList.add('show');
    ncRenderPkgCards();
    ncRenderPreciosPanel();
    ncRebuildProfServDropdowns();
    ncUpdateScCountBadge();
    document.getElementById('ncErrorMsg').style.display = 'none';
    ncContrShowSection(pkg);
    ncSwitchTab('servicios');
}

function ncClearPaquete() {
    ncPkgAplicado = null;
    document.getElementById('ncPkgChip').classList.remove('show');
    ncRenderPkgCards();
    ncContrHideSection();
}

// ── Sección contrato ──────────────────────────────────────────────────────────
let ncContrPagoIdx = 1;

function ncContrShowSection(pkg) {
    document.getElementById('ncContratoSection').style.display = '';
    document.getElementById('ncContrCrear').value     = '1';
    document.getElementById('ncContrPaqueteId').value = pkg.id;
    const precio = pkg.precio_total ? parseFloat(pkg.precio_total).toFixed(2) : '';
    document.getElementById('ncContrPrecio').value = precio;
    const container = document.getElementById('ncContrPagosContainer');
    [...container.children].slice(1).forEach(r => r.remove());
    ncContrPagoIdx = 1;
    ncUpdateContrSuma();
}

function ncContrHideSection() {
    document.getElementById('ncContratoSection').style.display = 'none';
    document.getElementById('ncContrCrear').value     = '0';
    document.getElementById('ncContrPaqueteId').value = '';
}

function ncAddContrPago() {
    const idx = ncContrPagoIdx++;
    const today = new Date().toISOString().split('T')[0];
    const div = document.createElement('div');
    div.style.cssText = 'display:grid;grid-template-columns:1fr 100px 1fr auto;gap:0.35rem;align-items:center;margin-bottom:0.3rem;';
    div.innerHTML = `
        <input type="number" name="contrato_pagos[${idx}][monto]" class="form-control ncContrMonto"
               step="0.01" min="0" placeholder="Monto (Bs.)" oninput="ncUpdateContrSuma()">
        <input type="date" name="contrato_pagos[${idx}][fecha_pago]" class="form-control" value="${today}">
        <select name="contrato_pagos[${idx}][metodo_pago]" class="form-control" style="font-size:.78rem;">
            <option value="">— Método —</option>
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="transferencia">Transferencia</option>
            <option value="qr">QR</option>
        </select>
        <button type="button" class="btn-remove-linea-modal" onclick="this.closest('div').remove();ncUpdateContrSuma();" title="Quitar">✕</button>`;
    document.getElementById('ncContrPagosContainer').appendChild(div);
}

function ncUpdateContrSuma() {
    let suma = 0;
    document.querySelectorAll('.ncContrMonto').forEach(inp => suma += parseFloat(inp.value) || 0);
    document.getElementById('ncContrSuma').textContent = suma.toFixed(2);
    const precioVal = parseFloat(document.getElementById('ncContrPrecio').value) || 0;
    const pend = Math.max(0, precioVal - suma);
    document.getElementById('ncContrTotal').textContent = pend.toFixed(2);
}

// ── Icon map por palabra clave ────────────────────────────────────────────────
const NC_SC_ICONS = [
    { keys: ['corte','cabello','pelo','haircut'],            icon: '✂️' },
    { keys: ['tinte','color','balayage','mechas','rubio'],   icon: '🎨' },
    { keys: ['novia','bridal','boda','quinceañ'],            icon: '👰' },
    { keys: ['manicure','manicura','uña','nail'],            icon: '💅' },
    { keys: ['pedicure','pedicura','pies','foot'],           icon: '🦶' },
    { keys: ['masaje','massage','relajante','corporal'],     icon: '🧖' },
    { keys: ['facial','limpieza','hidra','rostro','face'],   icon: '✨' },
    { keys: ['depilac','cera','laser'],                      icon: '🪷' },
    { keys: ['maquillaje','makeup','look'],                  icon: '💄' },
    { keys: ['tratamiento','keratina','proteína','repair'],  icon: '💆' },
    { keys: ['cejas','barba','bigote','brow'],               icon: '🪮' },
    { keys: ['spa','relajación','hammam','vapor'],           icon: '🛁' },
];

function ncGetServicioIcon(nombre) {
    const n = nombre.toLowerCase();
    for (const entry of NC_SC_ICONS) {
        if (entry.keys.some(k => n.includes(k))) return entry.icon;
    }
    return '⭐';
}

// ── State ─────────────────────────────────────────────────────────────────────
// Map<servicio_id (string) → { nombre, precio, descuento }>
const ncScSelected = new Map();
let ncScCatFiltro = '';

function ncFilterCat(btn, cat) {
    ncScCatFiltro = cat;
    document.querySelectorAll('#ncCatStrip .modal-pkg-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ncRenderServicioCards();
}

function ncRenderServicioCards() {
    const grid = document.getElementById('ncServicioCardsGrid');
    grid.innerHTML = '';
    const lista = ncScCatFiltro ? NC_SERVICIOS.filter(s => s.categoria === ncScCatFiltro) : NC_SERVICIOS;
    lista.forEach(s => {
        const card = document.createElement('div');
        card.className = 'servicio-card' + (ncScSelected.has(String(s.id)) ? ' sel' : '');
        card.dataset.id        = s.id;
        card.dataset.nombre    = s.nombre;
        card.dataset.precio    = s.precio;
        card.dataset.descuento = s.descuento ?? 0;

        const desc        = parseFloat(s.descuento ?? 0);
        const precioBase  = parseFloat(s.precio);
        const precioFmt   = desc > 0
            ? `<span style="text-decoration:line-through;opacity:.55;font-size:.58rem;">Bs.${precioBase.toFixed(0)}</span> Bs.${Math.round(precioBase*(1-desc/100))}`
            : `Bs. ${precioBase.toFixed(0)}`;
        const descLabel = desc > 0
            ? `<div style="font-size:.6rem;color:#fff;background:var(--success-color);padding:.05rem .3rem;border-radius:2px;margin-top:.2rem;display:inline-block;">-${desc}%</div>`
            : '';

        card.innerHTML = `
            <span class="sc-check">✓</span>
            <span class="sc-icon">${ncGetServicioIcon(s.nombre)}</span>
            <div class="sc-name">${s.nombre}</div>
            <div class="sc-price">${precioFmt}</div>
            ${descLabel}`;
        card.addEventListener('click', () => ncToggleServicioCard(card));
        grid.appendChild(card);
    });
}

function ncToggleServicioCard(card) {
    const id        = String(card.dataset.id);
    const nombre    = card.dataset.nombre;
    const precio    = parseFloat(card.dataset.precio || 0).toFixed(2);
    const descuento = parseFloat(card.dataset.descuento || 0);

    if (ncScSelected.has(id)) {
        ncScSelected.delete(id);
        card.classList.remove('sel');
    } else {
        ncScSelected.set(id, { nombre, precio, descuento: descuento > 0 ? String(descuento) : '' });
        card.classList.add('sel');
    }
    document.getElementById('ncErrorMsg').style.display = 'none';
    ncRenderPreciosPanel();
    ncRebuildProfServDropdowns();
    ncUpdateScCountBadge();
}

function ncUpdateScCountBadge() {
    const badge = document.getElementById('ncScCountBadge');
    const n = ncScSelected.size;
    if (n > 0) {
        badge.textContent = `${n} seleccionado${n > 1 ? 's' : ''}`;
        badge.style.display = 'inline';
    } else {
        badge.style.display = 'none';
    }
}

// ── Panel de precios ──────────────────────────────────────────────────────────
function ncCalcNeto(precio, descuento) {
    const p = parseFloat(precio) || 0;
    const d = parseFloat(descuento) || 0;
    return Math.round(p * (1 - d / 100) * 100) / 100;
}

function ncUpdateNetoLabel(id) {
    const row = document.querySelector(`#ncPreciosContainer .sc-precio-row[data-id="${id}"]`);
    if (!row) return;
    const state = ncScSelected.get(id);
    const neto  = ncCalcNeto(state.precio, state.descuento);
    const label = row.querySelector('.sc-neto');
    const hasDiscount = parseFloat(state.descuento) > 0;
    label.textContent = `Bs. ${neto.toFixed(2)}`;
    label.className   = 'sc-neto' + (hasDiscount ? ' has-desc' : '');
}

function ncRenderPreciosPanel() {
    const panel     = document.getElementById('ncPreciosPanel');
    const container = document.getElementById('ncPreciosContainer');

    if (ncScSelected.size === 0) {
        panel.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    panel.style.display = 'block';
    const existing = new Set([...container.querySelectorAll('.sc-precio-row')].map(r => r.dataset.id));

    ncScSelected.forEach(({ nombre, precio, descuento }, id) => {
        if (!existing.has(id)) {
            const row = document.createElement('div');
            row.className = 'sc-precio-row';
            row.dataset.id = id;
            row.innerHTML = `
                <span class="sc-precio-name">${nombre}</span>
                <div class="sc-precio-wrap">
                    <span class="sc-precio-prefix">Bs.</span>
                    <input type="number" class="form-control sc-precio-input"
                           data-id="${id}" step="0.01" min="0" value="${precio}" placeholder="0.00">
                </div>
                <div class="sc-desc-wrap">
                    <input type="number" class="form-control sc-desc-input"
                           data-id="${id}" step="1" min="0" max="100" value="${descuento || ''}" placeholder="0">
                    <span class="sc-desc-suffix">%</span>
                </div>
                <span class="sc-neto">Bs. ${parseFloat(precio).toFixed(2)}</span>`;

            row.querySelector('.sc-precio-input').addEventListener('input', e => {
                ncScSelected.get(id).precio = e.target.value;
                ncUpdateNetoLabel(id);
            });
            row.querySelector('.sc-desc-input').addEventListener('input', e => {
                ncScSelected.get(id).descuento = e.target.value;
                ncUpdateNetoLabel(id);
            });
            container.appendChild(row);
            ncUpdateNetoLabel(id);
        }
    });

    container.querySelectorAll('.sc-precio-row').forEach(row => {
        if (!ncScSelected.has(row.dataset.id)) row.remove();
    });
}

// ── Hidden inputs (inyectados antes de enviar) ────────────────────────────────
function ncInjectHiddenInputs() {
    const wrap = document.getElementById('ncHiddenInputs');
    wrap.innerHTML = '';
    let i = 0;
    ncScSelected.forEach(({ precio, descuento }, id) => {
        const d = parseFloat(descuento) || 0;
        wrap.innerHTML += `
            <input type="hidden" name="servicios[${i}][servicio_id]" value="${id}">
            <input type="hidden" name="servicios[${i}][precio]" value="${precio || 0}">
            <input type="hidden" name="servicios[${i}][descuento]" value="${d}">`;
        i++;
    });
}

// ══════════════════════════════════════════════════════════════════════════
// Modal: Nueva cita — Productos
// ══════════════════════════════════════════════════════════════════════════
// Map<producto_id (string) → { nombre, precio, descuento, cantidad }>
const ncPcSelected = new Map();

function ncRenderProductoCards() {
    const grid = document.getElementById('ncProductoCardsGrid');
    grid.innerHTML = '';
    NC_PRODUCTOS.forEach(p => {
        const card = document.createElement('div');
        card.className = 'servicio-card' + (ncPcSelected.has(String(p.id)) ? ' sel' : '');
        card.dataset.id     = p.id;
        card.dataset.nombre = p.nombre;
        card.dataset.precio = p.precio;

        const precioFmt = `Bs. ${parseFloat(p.precio || 0).toFixed(0)}`;
        card.innerHTML = `
            <span class="sc-check">✓</span>
            <span class="sc-icon">🛍️</span>
            <div class="sc-name">${p.nombre}</div>
            <div class="sc-price">${precioFmt}</div>`;
        card.addEventListener('click', () => ncToggleProductoCard(card));
        grid.appendChild(card);
    });
}

function ncToggleProductoCard(card) {
    const id     = String(card.dataset.id);
    const nombre = card.dataset.nombre;
    const precio = parseFloat(card.dataset.precio || 0).toFixed(2);

    if (ncPcSelected.has(id)) {
        ncPcSelected.delete(id);
        card.classList.remove('sel');
    } else {
        ncPcSelected.set(id, { nombre, precio, descuento: '', cantidad: 1 });
        card.classList.add('sel');
    }
    ncRenderProductosPreciosPanel();
    ncUpdatePcCountBadge();
}

function ncUpdatePcCountBadge() {
    const badge = document.getElementById('ncPcCountBadge');
    const n = ncPcSelected.size;
    if (n > 0) {
        badge.textContent = `${n} producto${n > 1 ? 's' : ''} seleccionado${n > 1 ? 's' : ''}`;
        badge.style.display = '';
    } else {
        badge.style.display = 'none';
    }
}

function ncCalcProductoNeto(precio, descuento, cantidad) {
    const p = parseFloat(precio) || 0;
    const d = parseFloat(descuento) || 0;
    const c = parseInt(cantidad) || 1;
    return Math.round(p * (1 - d / 100) * c * 100) / 100;
}

function ncUpdateProductoNetoLabel(id) {
    const row = document.querySelector(`#ncProductosPreciosContainer .pc-precio-row[data-id="${id}"]`);
    if (!row) return;
    const state = ncPcSelected.get(id);
    const neto  = ncCalcProductoNeto(state.precio, state.descuento, state.cantidad);
    row.querySelector('.pc-neto').textContent = `Bs. ${neto.toFixed(2)}`;
}

function ncRenderProductosPreciosPanel() {
    const panel     = document.getElementById('ncProductosPreciosPanel');
    const container = document.getElementById('ncProductosPreciosContainer');

    if (ncPcSelected.size === 0) {
        panel.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    panel.style.display = 'block';
    const existing = new Set([...container.querySelectorAll('.pc-precio-row')].map(r => r.dataset.id));

    ncPcSelected.forEach(({ nombre, precio, descuento, cantidad }, id) => {
        if (!existing.has(id)) {
            const row = document.createElement('div');
            row.className = 'pc-precio-row';
            row.dataset.id = id;
            row.style.cssText = 'display:grid;grid-template-columns:1fr 80px 100px 88px auto;gap:0.5rem;align-items:center;padding:0.35rem 0.7rem;';
            row.innerHTML = `
                <span style="font-size:0.85rem;color:var(--text-dark);">${nombre}</span>
                <input type="number" class="form-control pc-cantidad-input" data-id="${id}" min="1" step="1" value="${cantidad}">
                <div class="sc-precio-wrap">
                    <span class="sc-precio-prefix">Bs.</span>
                    <input type="number" class="form-control pc-precio-input" data-id="${id}" step="0.01" min="0" value="${precio}" placeholder="0.00">
                </div>
                <div class="sc-desc-wrap">
                    <input type="number" class="form-control pc-desc-input" data-id="${id}" step="1" min="0" max="100" value="${descuento || ''}" placeholder="0">
                    <span class="sc-desc-suffix">%</span>
                </div>
                <span class="pc-neto" style="text-align:right;font-size:0.85rem;">Bs. ${parseFloat(precio).toFixed(2)}</span>`;

            row.querySelector('.pc-cantidad-input').addEventListener('input', e => {
                ncPcSelected.get(id).cantidad = e.target.value || 1;
                ncUpdateProductoNetoLabel(id);
            });
            row.querySelector('.pc-precio-input').addEventListener('input', e => {
                ncPcSelected.get(id).precio = e.target.value;
                ncUpdateProductoNetoLabel(id);
            });
            row.querySelector('.pc-desc-input').addEventListener('input', e => {
                ncPcSelected.get(id).descuento = e.target.value;
                ncUpdateProductoNetoLabel(id);
            });
            container.appendChild(row);
            ncUpdateProductoNetoLabel(id);
        }
    });

    container.querySelectorAll('.pc-precio-row').forEach(row => {
        if (!ncPcSelected.has(row.dataset.id)) row.remove();
    });
}

function ncInjectProductosHiddenInputs() {
    const wrap = document.getElementById('ncProductosHiddenInputs');
    wrap.innerHTML = '';
    let i = 0;
    ncPcSelected.forEach(({ precio, descuento, cantidad }, id) => {
        const d = parseFloat(descuento) || 0;
        wrap.innerHTML += `
            <input type="hidden" name="productos[${i}][producto_catalogo_id]" value="${id}">
            <input type="hidden" name="productos[${i}][cantidad]" value="${cantidad || 1}">
            <input type="hidden" name="productos[${i}][precio]" value="${precio || 0}">
            <input type="hidden" name="productos[${i}][descuento]" value="${d}">`;
        i++;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    ncRenderServicioCards();
    ncRenderPkgCards();

    document.getElementById('formNuevaCita')?.addEventListener('submit', function (e) {
        if (ncScSelected.size === 0) {
            e.preventDefault();
            document.getElementById('ncErrorMsg').style.display = 'block';
            document.getElementById('ncServicioCardsGrid').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        ncInjectHiddenInputs();
        ncInjectProductosHiddenInputs();
    });

    @if(old('_form') === 'nueva_cita')
        abrirModalNuevaCita();
    @endif
});

// ── Profesionales ─────────────────────────────────────────────────────────────
let ncProfIdx = 0;

function ncBuildEmpleadoOptions() {
    return '<option value="">— Seleccionar —</option>' +
        NC_EMPLEADOS.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
}

function ncBuildProfServicioOptions() {
    if (ncScSelected.size === 0) return '<option value="">— Elige servicios primero —</option>';
    return '<option value="">— Qué realizó —</option>' +
        [...ncScSelected.entries()].map(([id, { nombre }]) =>
            `<option value="${id}">${nombre}</option>`).join('');
}

function ncRebuildProfServDropdowns() {
    document.querySelectorAll('.nc-select-prof-serv').forEach(sel => {
        const current = sel.value;
        sel.innerHTML = '<option value="">— Qué realizó —</option>' +
            [...ncScSelected.entries()].map(([id, { nombre }]) =>
                `<option value="${id}"${id == current ? ' selected' : ''}>${nombre}</option>`).join('');
    });
}

function ncAddProfesional() {
    const idx = ncProfIdx++;
    document.getElementById('ncProfHeadersRow').style.display = 'grid';
    const div = document.createElement('div');
    div.className = 'linea-prof-modal';
    div.dataset.index = idx;
    div.innerHTML = `
        <select name="profesionales[${idx}][empleado_id]" class="form-control">
            ${ncBuildEmpleadoOptions()}
        </select>
        <select name="profesionales[${idx}][servicio_id]" class="form-control nc-select-prof-serv">
            ${ncBuildProfServicioOptions()}
        </select>
        <button type="button" class="btn-remove-linea-modal" onclick="ncRemoveProfesional(this)" title="Quitar">✕</button>`;
    document.getElementById('ncProfesionalesContainer').appendChild(div);
}

function ncRemoveProfesional(btn) {
    btn.closest('.linea-prof-modal').remove();
    if (document.querySelectorAll('.linea-prof-modal').length === 0) {
        document.getElementById('ncProfHeadersRow').style.display = 'none';
    }
}

// ── Campaña ───────────────────────────────────────────────────────────────────
function ncSelectCampana(card) {
    document.querySelectorAll('.campana-card-modal').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('ncCampanaId').value = card.dataset.id;
}
</script>
@endpush
