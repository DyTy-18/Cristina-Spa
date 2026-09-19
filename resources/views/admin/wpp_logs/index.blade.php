@extends('admin.layouts.app')

@section('title', 'Actividad API WhatsApp')
@section('page-title', 'Actividad API WhatsApp')

@section('content')
    <div class="table-container">
        {{-- Filtros --}}
        <div class="table-filter">
            <form method="GET" action="{{ route('admin.wpp-logs.index') }}"
                style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;width:100%">

                <div class="filter-group">
                    <label>Dirección</label>
                    <select name="direccion" class="form-control" style="padding:.45rem .8rem;font-size:.85rem">
                        <option value="">Todas</option>
                        <option value="saliente" {{ request('direccion') === 'saliente' ? 'selected' : '' }}>Saliente (a WPP)</option>
                        <option value="entrante" {{ request('direccion') === 'entrante' ? 'selected' : '' }}>Entrante (desde WPP)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Resultado</label>
                    <select name="resultado" class="form-control" style="padding:.45rem .8rem;font-size:.85rem">
                        <option value="">Todos</option>
                        <option value="ok" {{ request('resultado') === 'ok' ? 'selected' : '' }}>OK</option>
                        <option value="error" {{ request('resultado') === 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control" style="padding:.45rem .8rem;font-size:.85rem">
                        <option value="">Todos</option>
                        @foreach ($tipos as $t)
                            <option value="{{ $t }}" {{ request('tipo') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Desde</label>
                    <input type="date" name="fecha_desde" class="filter-date" value="{{ request('fecha_desde') }}">
                </div>

                <div class="filter-group">
                    <label>Hasta</label>
                    <input type="date" name="fecha_hasta" class="filter-date" value="{{ request('fecha_hasta') }}">
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                @if (request()->hasAny(['direccion', 'resultado', 'tipo', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('admin.wpp-logs.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="table-header" style="padding:.8rem 1.5rem">
            <h3 class="table-title" style="font-size:1rem">
                {{ $logs->total() }} {{ $logs->total() === 1 ? 'registro' : 'registros' }}
            </h3>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Dirección</th>
                    <th>Tipo</th>
                    <th>Cita</th>
                    <th>Resultado</th>
                    <th>Mensaje</th>
                    <th>Payload</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="font-size:.82rem;white-space:nowrap;color:var(--text-light)">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <span style="font-size:.75rem">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $log->direccion === 'saliente' ? 'badge-info' : 'badge-warning' }}">
                                {{ $log->direccion === 'saliente' ? '→ A WPP' : '← Desde WPP' }}
                            </span>
                        </td>
                        <td style="font-size:.85rem">{{ $log->tipo }}</td>
                        <td style="font-size:.85rem">
                            @if ($log->cita)
                                <a href="{{ route('admin.citas.show', $log->cita) }}">
                                    #{{ $log->cita->id }} — {{ $log->cita->cliente?->nombre_completo }}
                                </a>
                            @else
                                <span style="color:var(--text-light)">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $log->resultado === 'ok' ? 'badge-success' : 'badge-danger' }}">
                                {{ strtoupper($log->resultado) }}
                                @if ($log->status_code)
                                    ({{ $log->status_code }})
                                @endif
                            </span>
                        </td>
                        <td style="font-size:.8rem;max-width:220px;color:var(--text-light)">
                            {{ $log->mensaje ?: '—' }}
                        </td>
                        <td style="font-size:.8rem">
                            @if ($log->payload)
                                <details>
                                    <summary style="cursor:pointer;color:var(--primary-color)">Ver</summary>
                                    <pre style="white-space:pre-wrap;max-width:320px;font-size:.72rem;background:var(--light-bg);padding:.5rem;margin-top:.4rem;border-radius:4px;">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @else
                                <span style="color:var(--text-light)">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">📱</div>
                                <p class="empty-state-text">Todavía no hay actividad registrada con la API de WhatsApp.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($logs->hasPages())
            <div style="padding:1rem 1.5rem;border-top:1px solid rgba(0,0,0,.05)">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
