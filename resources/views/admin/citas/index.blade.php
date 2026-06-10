@extends('admin.layouts.app')

@section('title', 'Citas')
@section('page-title', 'Gestión de Citas')

@push('styles')
<style>
    .filters-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: flex-end;
        margin-bottom: 1.25rem;
    }
    .filters-bar input,
    .filters-bar select {
        height: 38px;
        padding: 0 0.75rem;
        border: 1px solid rgba(0,0,0,0.15);
        border-radius: 4px;
        font-size: 0.875rem;
        color: var(--text-dark);
        background: var(--white);
        outline: none;
    }
    .filters-bar input { min-width: 200px; }
    .filters-bar select { min-width: 150px; }
    .filters-bar input:focus,
    .filters-bar select:focus { border-color: var(--secondary-color); }

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

    /* Modal */
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
</style>
@endpush

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3 class="table-title">Todas las Citas</h3>
        <a href="{{ route('admin.citas.create') }}" class="btn btn-primary">+ Nueva Cita</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.citas.index') }}" class="filters-bar">
        <input type="text" name="buscar" placeholder="Buscar cliente..." value="{{ request('buscar') }}">
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
            <a href="{{ route('admin.citas.create') }}" class="btn btn-primary">Crear Primera Cita</a>
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
    if (e.key === 'Escape') cerrarModal();
});

@if(session('verificar_cita_id'))
    // Re-abrir el modal si la verificación falló (el backend devolvió error)
    document.addEventListener('DOMContentLoaded', function () {
        const url = '{{ route('admin.citas.verificarEdicion', session('verificar_cita_id')) }}';
        abrirModalEditar(url);
        document.getElementById('modalError').textContent = '{{ $errors->first('password') }}';
    });
@endif
</script>
@endpush
