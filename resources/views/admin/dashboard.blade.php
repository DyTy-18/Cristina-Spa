@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-value">{{ $citasHoy ?? 0 }}</div>
            <div class="stat-label">Citas Hoy</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value">{{ $totalClientes ?? 0 }}</div>
            <div class="stat-label">Total Clientes</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✂️</div>
            <div class="stat-value">{{ $totalServicios ?? 0 }}</div>
            <div class="stat-label">Servicios</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">${{ number_format($ingresosMes ?? 0, 0) }}</div>
            <div class="stat-label">Ingresos del Mes</div>
        </div>

        @can('ver inventario')
            <a href="{{ route('admin.inventario.index') }}" class="stat-card" style="text-decoration:none; color:inherit;">
                <div class="stat-icon">📦</div>
                <div class="stat-value">{{ $totalProductos ?? 0 }}</div>
                <div class="stat-label">Productos en inventario</div>
            </a>

            <a href="{{ route('admin.inventario.index') }}" class="stat-card" style="text-decoration:none; color:inherit;">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value">
                    {{ $productosStockBajo ?? 0 }}
                    @if (($productosStockBajo ?? 0) > 0)
                        <span class="badge badge-danger" style="font-size:0.7rem; vertical-align:middle;">BAJO</span>
                    @endif
                </div>
                <div class="stat-label">Stock bajo</div>
            </a>
        @endcan
    </div>

    <!-- Recent Appointments -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Próximas Citas</h3>
            <a href="{{ route('admin.citas.index') }}" class="btn btn-outline btn-sm">Ver todas</a>
        </div>

        @if (isset($proximasCitas) && $proximasCitas->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proximasCitas as $cita)
                        <tr>
                            <td>{{ $cita->cliente->nombre ?? 'N/A' }}</td>
                            <td>{{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' + ') ?: 'N/A' }}</td>
                            <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                            <td>{{ $cita->hora }}</td>
                            <td>
                                @switch($cita->estado)
                                    @case('pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @break

                                    @case('confirmada')
                                        <span class="badge badge-success">Confirmada</span>
                                    @break

                                    @case('cancelada')
                                        <span class="badge badge-danger">Cancelada</span>
                                    @break

                                    @default
                                        <span class="badge badge-info">{{ $cita->estado }}</span>
                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📅</div>
                <p class="empty-state-text">No hay citas próximas</p>
                <a href="{{ route('admin.citas.create') }}" class="btn btn-primary">Crear Nueva Cita</a>
            </div>
        @endif
    </div>

    @hasrole('admin')
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Actividad API WhatsApp</h3>
                <a href="{{ route('admin.wpp-logs.index') }}" class="btn btn-outline btn-sm">Ver todo</a>
            </div>

            @if ($wppLogsRecientes->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Dirección</th>
                            <th>Tipo</th>
                            <th>Cita</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wppLogsRecientes as $log)
                            <tr>
                                <td style="font-size:.82rem;color:var(--text-light);white-space:nowrap;">
                                    {{ $log->created_at->format('d/m H:i') }}
                                </td>
                                <td>
                                    <span class="badge {{ $log->direccion === 'saliente' ? 'badge-info' : 'badge-warning' }}">
                                        {{ $log->direccion === 'saliente' ? '→ A WPP' : '← Desde WPP' }}
                                    </span>
                                </td>
                                <td style="font-size:.85rem">{{ $log->tipo }}</td>
                                <td style="font-size:.85rem">
                                    @if ($log->cita)
                                        <a href="{{ route('admin.citas.show', $log->cita) }}">#{{ $log->cita->id }}</a>
                                    @else
                                        <span style="color:var(--text-light)">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $log->resultado === 'ok' ? 'badge-success' : 'badge-danger' }}">
                                        {{ strtoupper($log->resultado) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📱</div>
                    <p class="empty-state-text">Todavía no hay actividad registrada con la API de WhatsApp.</p>
                </div>
            @endif
        </div>
    @endhasrole
@endsection
