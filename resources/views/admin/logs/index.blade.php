@extends('admin.layouts.app')

@section('title', 'Registro de Actividad')
@section('page-title', 'Registro de Actividad')

@section('content')
    <div class="table-container">
        {{-- Filtros --}}
        <div class="table-filter">
            <form method="GET" action="{{ route('admin.logs.index') }}"
                style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;width:100%">

                <div class="filter-group">
                    <label>Módulo</label>
                    <select name="log_name" class="form-control" style="padding:.45rem .8rem;font-size:.85rem">
                        <option value="">Todos</option>
                        @foreach ($logNames as $name)
                            <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>
                                {{ ucfirst($name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Acción</label>
                    <select name="event" class="form-control" style="padding:.45rem .8rem;font-size:.85rem">
                        <option value="">Todas</option>
                        <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Creado</option>
                        <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Editado</option>
                        <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Eliminado</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Usuario</label>
                    <select name="causer_id" class="form-control" style="padding:.45rem .8rem;font-size:.85rem">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id }}" {{ request('causer_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
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
                @if (request()->hasAny(['log_name', 'event', 'causer_id', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="table-header" style="padding:.8rem 1.5rem">
            <h3 class="table-title" style="font-size:1rem">
                {{ $logs->total() }} {{ $logs->total() === 1 ? 'registro' : 'registros' }}
            </h3>
            <a href="{{ route('admin.logs.export-pdf', request()->query()) }}"
               class="btn btn-outline btn-sm"
               style="display:flex;align-items:center;gap:.4rem"
               target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17h6M9 13h6M9 9h1"/>
                </svg>
                Exportar PDF
            </a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Usuario</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th>Cambios</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td style="font-size:.82rem;white-space:nowrap;color:var(--text-light)">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <span style="font-size:.75rem">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td style="font-size:.88rem">
                            {{ $log->causer?->name ?? '—' }}
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $log->log_name }}</span>
                        </td>
                        <td>
                            @php
                                $eventColors = [
                                    'created' => 'badge-success',
                                    'updated' => 'badge-warning',
                                    'deleted' => 'badge-danger',
                                ];
                                $eventLabels = [
                                    'created' => 'Creado',
                                    'updated' => 'Editado',
                                    'deleted' => 'Eliminado',
                                ];
                            @endphp
                            <span class="badge {{ $eventColors[$log->event] ?? 'badge-info' }}">
                                {{ $eventLabels[$log->event] ?? $log->event }}
                            </span>
                        </td>
                        <td style="font-size:.88rem;max-width:200px">
                            {{ $log->description }}
                            @if ($log->subject)
                                <span style="color:var(--text-light);font-size:.78rem">
                                    #{{ $log->subject_id }}
                                </span>
                            @endif
                        </td>
                        <td style="font-size:.8rem;max-width:280px">
                            @if ($log->event === 'updated' && $log->properties->has('old'))
                                @foreach ($log->properties['attributes'] as $campo => $nuevo)
                                    @php $viejo = $log->properties['old'][$campo] ?? null; @endphp
                                    @if ($viejo !== $nuevo)
                                        <div style="margin-bottom:.25rem">
                                            <span style="color:var(--text-light)">{{ $campo }}:</span>
                                            <span style="color:var(--error-color);text-decoration:line-through">{{ $viejo }}</span>
                                            →
                                            <span style="color:var(--success-color)">{{ $nuevo }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            @elseif ($log->event === 'created' && $log->properties->has('attributes'))
                                @foreach ($log->properties['attributes'] as $campo => $valor)
                                    <div><span style="color:var(--text-light)">{{ $campo }}:</span> {{ $valor }}</div>
                                @endforeach
                            @else
                                <span style="color:var(--text-light)">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">📋</div>
                                <p class="empty-state-text">No hay registros de actividad.</p>
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
