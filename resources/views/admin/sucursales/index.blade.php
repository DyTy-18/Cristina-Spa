@extends('admin.layouts.app')

@section('title', 'Sucursales')
@section('page-title', 'Gestión de Sucursales')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todas las Sucursales</h3>
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span class="stat-label">{{ $sucursales->count() }} registradas</span>
                <a href="{{ route('admin.sucursales.migracion') }}" class="btn btn-outline btn-sm">🔄 Migrar datos</a>
                <a href="{{ route('admin.sucursales.create') }}" class="btn btn-primary btn-sm">+ Nueva Sucursal</a>
            </div>
        </div>

        @if($sucursales->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🏢</div>
                <p class="empty-state-text">Aún no hay sucursales registradas</p>
                <a href="{{ route('admin.sucursales.create') }}" class="btn btn-primary">Registrar Primera Sucursal</a>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sucursales as $sucursal)
                        <tr style="{{ !$sucursal->activo ? 'opacity:0.55;' : '' }}">
                            <td>
                                <div style="font-weight:400;">{{ $sucursal->nombre }}</div>
                                @if($sucursal->descripcion)
                                    <div style="font-size:0.78rem;color:var(--text-light);margin-top:0.15rem;">
                                        {{ Str::limit($sucursal->descripcion, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $sucursal->direccion ?? '—' }}
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $sucursal->telefono ?? '—' }}
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $sucursal->email ?? '—' }}
                            </td>
                            <td>
                                @if($sucursal->es_principal)
                                    <span style="background:#fef3c7;color:#92400e;border-radius:20px;font-size:0.72rem;font-weight:600;padding:0.2rem 0.65rem;letter-spacing:0.03em;">
                                        ★ Principal
                                    </span>
                                @else
                                    <span style="background:#f3f4f6;color:#6b7280;border-radius:20px;font-size:0.72rem;padding:0.2rem 0.65rem;">
                                        Sucursal
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sucursal->activo)
                                    <span style="color:#4caf50;font-size:0.8rem;">● Activa</span>
                                @else
                                    <span style="color:var(--text-light);font-size:0.8rem;">● Inactiva</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.sucursales.edit', $sucursal) }}" class="btn btn-sm btn-accent">
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
