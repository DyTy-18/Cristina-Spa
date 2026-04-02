@extends('admin.layouts.app')

@section('title', 'Mis Citas')
@section('page-title', 'Mis Citas de Hoy')

@section('content')
<div class="table-container">
    <div class="table-header">
        <h3 class="table-title">Citas asignadas — {{ now()->format('d/m/Y') }}</h3>
    </div>

    @if($citasHoy->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p class="empty-state-text">No tenés citas asignadas para hoy.</p>
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Servicio(s)</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($citasHoy as $item)
                    @php $cita = $item['cita']; @endphp
                    <tr>
                        <td>{{ substr($cita->getRawOriginal('hora'), 0, 5) }}</td>
                        <td>{{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}</td>
                        <td>
                            @foreach($item['servicios'] as $servicio)
                                {{ $servicio->nombre }}@unless($loop->last), @endunless
                            @endforeach
                        </td>
                        <td>
                            @php
                                $badgeColor = match($cita->estado) {
                                    'completada' => '#10b981',
                                    'confirmada' => '#3b82f6',
                                    'pendiente'  => '#f59e0b',
                                    'cancelada'  => '#ef4444',
                                    default      => '#6b7280',
                                };
                            @endphp
                            <span style="background:{{ $badgeColor }};color:#fff;border-radius:9999px;font-size:.75rem;padding:.2rem .6rem;font-weight:600;">
                                {{ ucfirst($cita->estado) }}
                            </span>
                        </td>
                        <td>
                            @if($cita->estado === 'completada')
                                <a href="{{ route('admin.mis-citas.consumo', $cita) }}"
                                   style="background:#3b82f6;color:#fff;border-radius:6px;padding:.3rem .75rem;font-size:.8rem;text-decoration:none;font-weight:600;">
                                    Registrar consumo
                                </a>
                            @else
                                <span style="color:#aaa;font-size:.85rem;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
