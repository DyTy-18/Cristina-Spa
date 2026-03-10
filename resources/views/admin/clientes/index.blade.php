@extends('admin.layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todos los Clientes</h3>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="stat-label">{{ $clientes->count() }} registrados</span>
                <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary btn-sm">+ Nuevo Cliente</a>
            </div>
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
                        <th>Visitas completadas</th>
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
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#clientesTable .cliente-row').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
