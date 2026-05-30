@extends('admin.layouts.app')

@section('title', 'Clientes Ocultos')
@section('page-title', 'Clientes Ocultos')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <a href="{{ route('admin.clientes.index') }}" style="color:var(--text-light);font-size:0.85rem;">← Volver a clientes</a>
                <h3 class="table-title" style="margin:0;">Clientes Ocultos</h3>
            </div>
            <span class="stat-label">{{ $clientes->count() }} ocultos</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
        @endif

        @if($clientes->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">👁</div>
                <p class="empty-state-text">No hay clientes ocultos</p>
            </div>
        @else
            <table class="data-table" id="clientesOcultosTable">
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
                        <tr>
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
                                    <form method="POST" action="{{ route('admin.clientes.toggleOculto', $cliente) }}" style="display:inline;">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-accent"
                                        >
                                            Restaurar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
