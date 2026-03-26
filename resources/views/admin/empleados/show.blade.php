@extends('admin.layouts.app')

@section('title', $empleado->nombre_completo . ' — Perfil')
@section('page-title', 'Perfil del Empleado')

@push('styles')
<style>
    .employee-profile {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 2rem;
        display: flex;
        align-items: flex-start;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .employee-avatar-lg {
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

    .employee-meta { flex: 1; }

    .employee-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 1px;
        line-height: 1.1;
    }

    .employee-details {
        display: flex;
        gap: 1.5rem;
        margin-top: 0.6rem;
        flex-wrap: wrap;
    }

    .employee-detail-item {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .employee-detail-item strong {
        color: var(--text-dark);
        font-weight: 400;
    }
</style>
@endpush

@section('content')

    {{-- Back + actions --}}
    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.empleados.index') }}" class="btn btn-sm btn-outline">← Todos los empleados</a>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            @can('ver comisiones')
                <a href="{{ route('admin.empleados.comisiones.index', $empleado) }}"
                   class="btn btn-sm btn-outline">⚙️ Comisiones</a>
                <a href="{{ route('admin.reportes.comisiones', ['empleado_id' => $empleado->id]) }}"
                   class="btn btn-sm btn-outline">📈 Ver reporte</a>
            @endcan
            <a href="{{ route('admin.empleados.edit', $empleado) }}" class="btn btn-sm btn-accent">Editar datos</a>
        </div>
    </div>

    {{-- Profile card --}}
    <div class="employee-profile">
        <div class="employee-avatar-lg">{{ mb_substr($empleado->nombre, 0, 1) }}</div>
        <div class="employee-meta">
            <div class="employee-name">{{ $empleado->nombre_completo }}</div>
            <div class="employee-details">
                <span class="employee-detail-item">
                    <strong>Cargo</strong> {{ ucfirst($empleado->cargo) }}
                </span>
                @if($empleado->especialidad)
                    <span class="employee-detail-item">
                        <strong>Especialidad</strong> {{ $empleado->especialidad }}
                    </span>
                @endif
                @if($empleado->telefono)
                    <span class="employee-detail-item">
                        <strong>Tel.</strong> {{ $empleado->telefono }}
                    </span>
                @endif
                @if($empleado->fecha_contratacion)
                    <span class="employee-detail-item">
                        <strong>Desde</strong> {{ $empleado->fecha_contratacion->format('d/m/Y') }}
                    </span>
                @endif
                <span class="employee-detail-item" style="color:{{ $empleado->activo ? 'var(--success-color)' : 'var(--text-light)' }}">
                    ● {{ $empleado->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="stats-grid" style="margin-bottom:2rem;">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-value">{{ $stats['total_citas'] }}</div>
            <div class="stat-label">Total citas</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $stats['citas_completadas'] }}</div>
            <div class="stat-label">Completadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value" style="font-size:1.4rem;">Bs. {{ number_format($stats['ingresos_generados'], 0) }}</div>
            <div class="stat-label">Ingresos generados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🗓️</div>
            <div class="stat-value">{{ $stats['proximas_citas'] }}</div>
            <div class="stat-label">Próximas citas</div>
        </div>
    </div>

    {{-- Últimas citas --}}
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Últimas Citas</h3>
        </div>

        @if($citas->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p class="empty-state-text">Sin citas asignadas aún</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($citas as $cita)
                        <tr>
                            <td style="font-size:0.85rem;">
                                {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                                <span style="color:var(--text-light);"> {{ substr($cita->hora, 0, 5) }}</span>
                            </td>
                            <td>{{ $cita->cliente?->nombre_completo ?? '—' }}</td>
                            <td style="font-size:0.85rem;">
                                {{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' + ') ?: '—' }}
                            </td>
                            <td>
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
                            </td>
                            <td style="font-size:0.85rem;">
                                @if($cita->precio_final)
                                    <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--secondary-color);">
                                        Bs. {{ number_format($cita->precio_final, 2) }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
