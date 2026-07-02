@extends('admin.layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@push('styles')
<style>
    /* Modal confirmación eliminar */
    .del-overlay {
        display: none; position: fixed; inset: 0; z-index: 9000;
        background: rgba(0,0,0,0.45); align-items: center; justify-content: center;
    }
    .del-overlay.open { display: flex; }
    .del-modal {
        background: var(--white); padding: 2rem 2.25rem; max-width: 420px; width: 90%;
        border-top: 4px solid var(--error-color);
    }
    .del-modal h4 {
        font-family: 'Cormorant Garamond', serif; font-size: 1.3rem;
        color: var(--primary-color); margin-bottom: 0.6rem; letter-spacing: 1px;
    }
    .del-modal p { font-size: 0.85rem; color: var(--text-light); margin-bottom: 1.5rem; line-height: 1.6; }
    .del-modal strong { color: var(--primary-color); }
    .del-modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }
</style>
@endpush

@section('content')
    <div class="table-container">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                <h3 class="table-title">
                    @if($ordenar === 'frecuentes') Clientes más frecuentes
                    @elseif($ordenar === 'gasto') Clientes por gasto
                    @elseif($ordenar === 'alfabetico') Clientes (A–Z)
                    @else Últimos clientes
                    @endif
                </h3>
                @include('admin.partials.sucursal_badge')
            </div>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="stat-label">{{ $clientes->count() }} registrados</span>
                @if($esAdmin && $totalOcultos > 0)
                    <a href="{{ route('admin.clientes.ocultos') }}" class="btn btn-sm btn-outline" style="color:var(--text-light);">
                        Ocultos ({{ $totalOcultos }})
                    </a>
                @endif
                <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary btn-sm">+ Nuevo Cliente</a>
            </div>
        </div>

        {{-- Filtros de orden --}}
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
            @php
                $filtros = [
                    'recientes'  => 'Últimos agregados',
                    'frecuentes' => 'Más frecuentes (3 meses)',
                    'gasto'      => 'Mayor gasto',
                    'alfabetico' => 'A – Z',
                ];
            @endphp
            @foreach($filtros as $valor => $etiqueta)
                <a href="{{ route('admin.clientes.index', ['ordenar' => $valor]) }}"
                   class="btn btn-sm {{ $ordenar === $valor ? 'btn-primary' : 'btn-outline' }}"
                   style="{{ $ordenar !== $valor ? 'color:var(--text-light);' : '' }}">
                    {{ $etiqueta }}
                </a>
            @endforeach
        </div>

        @if($clientes->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <p class="empty-state-text">Aún no hay clientes registrados</p>
            </div>
        @else
            <div class="table-filter">
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Buscar por nombre, email o teléfono..."
                    autocomplete="off"
                >
            </div>

            <table class="data-table" id="clientesTable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Registrado en</th>
                        <th>Visitas completadas</th>
                        @if($ordenar === 'frecuentes')
                            <th>Últimos 3 meses</th>
                        @endif
                        <th>Total gastado</th>
                        <th>Primera visita</th>
                        <th>Última visita</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                        <tr class="cliente-row">
                            <td>
                                <div style="font-weight:400;">{{ $cliente->nombre_completo }}</div>
                                @if($cliente->fecha_nacimiento)
                                    <div style="font-size:0.75rem;color:var(--text-light);">
                                        {{ $cliente->fecha_nacimiento->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $cliente->email ?? '—' }}</div>
                                <div style="font-size:0.8rem;color:var(--text-light);">{{ $cliente->telefono ?? '' }}</div>
                            </td>
                            <td style="font-size:0.82rem;">
                                @if($cliente->sucursal)
                                    <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.6rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:20px;color:#0369a1;font-size:0.72rem;white-space:nowrap;">
                                        🏢 {{ $cliente->sucursal->nombre }}
                                    </span>
                                @else
                                    <span style="color:var(--text-light);">—</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;">
                                    {{ $cliente->citas_completadas_count }}
                                </span>
                                @if($cliente->citas_count > $cliente->citas_completadas_count)
                                    <span style="font-size:0.75rem;color:var(--text-light);">
                                        / {{ $cliente->citas_count }} total
                                    </span>
                                @endif
                            </td>
                            @if($ordenar === 'frecuentes')
                                <td>
                                    @if($cliente->citas_ultimos_3meses > 0)
                                        <span style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--accent-color);">
                                            {{ $cliente->citas_ultimos_3meses }}
                                        </span>
                                        <span style="font-size:0.75rem;color:var(--text-light);"> visitas</span>
                                    @else
                                        <span style="color:var(--text-light);font-size:0.85rem;">—</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--secondary-color);">
                                    Bs. {{ number_format($cliente->total_gastado ?? 0, 2) }}
                                </span>
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $cliente->primera_visita ? \Carbon\Carbon::parse($cliente->primera_visita)->format('d/m/Y') : '—' }}
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $cliente->ultima_visita ? \Carbon\Carbon::parse($cliente->ultima_visita)->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-sm btn-outline">
                                        Historial
                                    </a>
                                    <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-sm btn-accent">
                                        Editar
                                    </a>
                                    @if($esAdmin)
                                        <form method="POST" action="{{ route('admin.clientes.toggleOculto', $cliente) }}" style="display:inline;">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline"
                                                style="color:var(--text-light);"
                                                >
                                                Ocultar
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->hasRole('developer'))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            onclick="abrirConfirmDelete({{ $cliente->id }}, '{{ addslashes($cliente->nombre_completo) }}')"
                                        >
                                            Eliminar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

@if(auth()->user()->hasRole('developer'))
{{-- Modal de confirmación eliminar cliente --}}
<div class="del-overlay" id="delOverlay">
    <div class="del-modal">
        <h4>Eliminar cliente</h4>
        <p>
            ¿Estás seguro de que deseas eliminar permanentemente a
            <strong id="delNombre"></strong>?
            <br>
            Esta acción borrará también todas sus citas y no se puede deshacer.
        </p>
        <div class="del-modal-actions">
            <button type="button" class="btn btn-outline" onclick="cerrarConfirmDelete()">Cancelar</button>
            <form id="delForm" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#clientesTable .cliente-row').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    function abrirConfirmDelete(id, nombre) {
        document.getElementById('delNombre').textContent = nombre;
        document.getElementById('delForm').action = '/admin/clientes/' + id;
        document.getElementById('delOverlay').classList.add('open');
    }

    function cerrarConfirmDelete() {
        document.getElementById('delOverlay').classList.remove('open');
    }

    document.getElementById('delOverlay')?.addEventListener('click', function (e) {
        if (e.target === this) cerrarConfirmDelete();
    });
</script>
@endpush
