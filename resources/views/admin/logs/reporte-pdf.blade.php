<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Actividad</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e1e2e; background: #fff; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start;
                  border-bottom: 2px solid #c9a96e; padding-bottom: 10px; margin-bottom: 14px; }
        .header-left .brand { font-size: 18px; font-weight: 700; color: #c9a96e; letter-spacing: 1px; }
        .header-left .subtitle { font-size: 11px; color: #666; margin-top: 2px; }
        .header-right { text-align: right; }
        .header-right .report-title { font-size: 13px; font-weight: 700; color: #333; }
        .header-right .report-date { font-size: 9px; color: #888; margin-top: 3px; }

        /* Filtros aplicados */
        .filters-bar { background: #f8f6f1; border: 1px solid #e8e0d0; border-radius: 4px;
                       padding: 6px 10px; margin-bottom: 12px; }
        .filters-bar .filters-title { font-weight: 700; font-size: 9px; color: #999;
                                      text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
        .filters-chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip { background: #fff; border: 1px solid #d8cfc0; border-radius: 3px;
                padding: 2px 7px; font-size: 9px; color: #555; }
        .chip span { font-weight: 700; color: #333; }

        /* Stats */
        .stats-row { display: flex; gap: 10px; margin-bottom: 14px; }
        .stat-box { flex: 1; border: 1px solid #e0dbd2; border-radius: 4px; padding: 8px 10px; text-align: center; }
        .stat-box .stat-num { font-size: 18px; font-weight: 700; }
        .stat-box .stat-label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }
        .stat-created { color: #2d9e6b; }
        .stat-updated { color: #d4a017; }
        .stat-deleted { color: #e05252; }
        .stat-total   { color: #c9a96e; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #c9a96e; color: #fff; }
        thead th { padding: 6px 7px; text-align: left; font-size: 9px;
                   font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
        tbody tr:nth-child(even) { background: #faf8f4; }
        tbody tr { border-bottom: 1px solid #ede8df; }
        tbody td { padding: 5px 7px; vertical-align: top; font-size: 9px; line-height: 1.4; }

        .badge { display: inline-block; border-radius: 3px; padding: 1px 6px; font-size: 8px; font-weight: 700; }
        .badge-success { background: #d4f4e5; color: #1a7a4a; }
        .badge-warning { background: #fef3cd; color: #856404; }
        .badge-danger  { background: #fde8e8; color: #9b1c1c; }
        .badge-info    { background: #e0edff; color: #1e4d9b; }

        .text-muted { color: #999; }
        .del { text-decoration: line-through; color: #e05252; }
        .new { color: #2d9e6b; }

        /* Footer */
        .footer { margin-top: 16px; border-top: 1px solid #e0dbd2; padding-top: 8px;
                  display: flex; justify-content: space-between; color: #aaa; font-size: 8px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="brand">Cristina Spa</div>
            <div class="subtitle">Sistema de Gestión Integral</div>
        </div>
        <div class="header-right">
            <div class="report-title">Reporte de Registro de Actividad</div>
            <div class="report-date">Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</div>
        </div>
    </div>

    {{-- Filtros aplicados --}}
    @php
        $eventLabels = ['created' => 'Creado', 'updated' => 'Editado', 'deleted' => 'Eliminado'];
        $tieneAlgunFiltro = $filtros['log_name'] || $filtros['event'] || $filtros['causer_id']
                         || $filtros['fecha_desde'] || $filtros['fecha_hasta'];
    @endphp

    @if ($tieneAlgunFiltro)
    <div class="filters-bar">
        <div class="filters-title">Filtros aplicados</div>
        <div class="filters-chips">
            @if ($filtros['fecha_desde'] || $filtros['fecha_hasta'])
                <div class="chip">
                    Período:
                    <span>
                        @if ($filtros['fecha_desde'] && $filtros['fecha_hasta'])
                            {{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}
                            &nbsp;→&nbsp;
                            {{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}
                        @elseif ($filtros['fecha_desde'])
                            Desde {{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}
                        @else
                            Hasta {{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}
                        @endif
                    </span>
                </div>
            @endif
            @if ($filtros['log_name'])
                <div class="chip">Módulo: <span>{{ ucfirst($filtros['log_name']) }}</span></div>
            @endif
            @if ($filtros['event'])
                <div class="chip">Acción: <span>{{ $eventLabels[$filtros['event']] ?? $filtros['event'] }}</span></div>
            @endif
            @if ($usuarioNombre)
                <div class="chip">Usuario: <span>{{ $usuarioNombre }}</span></div>
            @endif
        </div>
    </div>
    @endif

    {{-- Estadísticas --}}
    @php
        $total    = $logs->count();
        $creados  = $logs->where('event', 'created')->count();
        $editados = $logs->where('event', 'updated')->count();
        $borrados = $logs->where('event', 'deleted')->count();
    @endphp
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-num stat-total">{{ $total }}</div>
            <div class="stat-label">Total registros</div>
        </div>
        <div class="stat-box">
            <div class="stat-num stat-created">{{ $creados }}</div>
            <div class="stat-label">Creaciones</div>
        </div>
        <div class="stat-box">
            <div class="stat-num stat-updated">{{ $editados }}</div>
            <div class="stat-label">Ediciones</div>
        </div>
        <div class="stat-box">
            <div class="stat-num stat-deleted">{{ $borrados }}</div>
            <div class="stat-label">Eliminaciones</div>
        </div>
    </div>

    {{-- Tabla de registros --}}
    <table>
        <thead>
            <tr>
                <th style="width:90px">Fecha / Hora</th>
                <th style="width:90px">Usuario</th>
                <th style="width:70px">Módulo</th>
                <th style="width:60px">Acción</th>
                <th style="width:160px">Descripción</th>
                <th>Cambios</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                @php
                    $eventColors = ['created' => 'badge-success', 'updated' => 'badge-warning', 'deleted' => 'badge-danger'];
                    $eventLabelsRow = ['created' => 'Creado', 'updated' => 'Editado', 'deleted' => 'Eliminado'];
                @endphp
                <tr>
                    <td>
                        {{ $log->created_at->format('d/m/Y') }}<br>
                        <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>{{ $log->causer?->name ?? '—' }}</td>
                    <td><span class="badge badge-info">{{ $log->log_name }}</span></td>
                    <td>
                        <span class="badge {{ $eventColors[$log->event] ?? 'badge-info' }}">
                            {{ $eventLabelsRow[$log->event] ?? $log->event }}
                        </span>
                    </td>
                    <td>
                        {{ $log->description }}
                        @if ($log->subject)
                            <span class="text-muted">#{{ $log->subject_id }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($log->event === 'updated' && $log->properties->has('old'))
                            @foreach ($log->properties['attributes'] as $campo => $nuevo)
                                @php $viejo = $log->properties['old'][$campo] ?? null; @endphp
                                @if ($viejo !== $nuevo)
                                    <div style="margin-bottom:2px">
                                        <span class="text-muted">{{ $campo }}:</span>
                                        <span class="del">{{ $viejo }}</span>
                                        →
                                        <span class="new">{{ $nuevo }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @elseif ($log->event === 'created' && $log->properties->has('attributes'))
                            @foreach ($log->properties['attributes'] as $campo => $valor)
                                <div>
                                    <span class="text-muted">{{ $campo }}:</span>
                                    {{ is_array($valor) ? json_encode($valor) : $valor }}
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#aaa;padding:16px">
                        No hay registros para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div>Cristina Spa &mdash; Reporte generado automáticamente</div>
        <div>Total: {{ $total }} {{ $total === 1 ? 'registro' : 'registros' }}</div>
    </div>

</body>
</html>
