@extends('admin.layouts.app')

@section('title', 'Detalle de Backup')
@section('page-title', 'Detalle de Backup')

@section('content')
    <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('admin.inventario.backups') }}" class="btn btn-outline btn-sm">&larr; Volver a Backups</a>
    </div>

    <div style="padding:1rem 1.25rem; background:#f8f6f4; border:1px solid #e0d9d0; margin-bottom:1.5rem;">
        <p style="margin:0; font-size:0.9rem;">
            <strong>Eliminado el:</strong> {{ $backup->created_at->format('d/m/Y H:i') }}
            &nbsp;·&nbsp;
            <strong>Por:</strong> {{ $backup->user->name ?? 'Usuario eliminado' }}
            &nbsp;·&nbsp;
            <strong>{{ $backup->productos_count }}</strong> productos,
            <strong>{{ $backup->entradas_count }}</strong> entradas,
            <strong>{{ $backup->salidas_count }}</strong> salidas
        </p>
    </div>

    <div class="table-container" style="margin-bottom:1.5rem;">
        <div class="table-header">
            <h3 class="table-title">Productos ({{ count($backup->datos['productos'] ?? []) }})</h3>
        </div>
        @if (!empty($backup->datos['productos']))
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        <th>Uso</th>
                        <th style="text-align:right;">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backup->datos['productos'] as $p)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $p['codigo_barras'] }}</code></td>
                            <td>{{ $p['nombre'] }}</td>
                            <td>{{ $p['marca'] ?? '—' }}</td>
                            <td>{{ $p['linea'] ?? '—' }}</td>
                            <td>{{ $p['uso'] ?? '—' }}</td>
                            <td style="text-align:right;">{{ $p['costo'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><p class="empty-state-text">Sin productos en este backup.</p></div>
        @endif
    </div>

    <div class="table-container" style="margin-bottom:1.5rem;">
        <div class="table-header">
            <h3 class="table-title">Entradas ({{ count($backup->datos['entradas'] ?? []) }})</h3>
        </div>
        @if (!empty($backup->datos['entradas']))
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th style="text-align:right;">Unidades</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backup->datos['entradas'] as $e)
                        <tr>
                            <td>{{ $e['fecha'] ? \Illuminate\Support\Carbon::parse($e['fecha'])->format('d/m/Y') : 'Sin fecha de movimiento' }}</td>
                            <td><code style="font-size:0.8rem;">{{ $e['codigo_barras'] }}</code></td>
                            <td style="text-align:right;">
                                <span class="badge badge-success">+{{ $e['unidades'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><p class="empty-state-text">Sin entradas en este backup.</p></div>
        @endif
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Salidas ({{ count($backup->datos['salidas'] ?? []) }})</h3>
        </div>
        @if (!empty($backup->datos['salidas']))
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th style="text-align:right;">Unidades</th>
                        <th>Destino</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backup->datos['salidas'] as $s)
                        <tr>
                            <td>{{ $s['fecha'] ? \Illuminate\Support\Carbon::parse($s['fecha'])->format('d/m/Y') : 'Sin fecha de movimiento' }}</td>
                            <td><code style="font-size:0.8rem;">{{ $s['codigo_barras'] }}</code></td>
                            <td style="text-align:right;">
                                <span class="badge badge-danger">&minus;{{ $s['unidades'] }}</span>
                            </td>
                            <td>{{ $s['destino'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><p class="empty-state-text">Sin salidas en este backup.</p></div>
        @endif
    </div>
@endsection
