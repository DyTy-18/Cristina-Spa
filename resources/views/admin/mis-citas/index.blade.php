@extends('admin.layouts.app')

@section('title', 'Mis Citas')
@section('page-title', 'Mis Citas')

@push('styles')
<style>
    .emp-header {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .emp-avatar {
        width: 52px;
        height: 52px;
        background: var(--accent-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        color: var(--white);
        flex-shrink: 0;
    }

    .emp-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        color: var(--primary-color);
        font-weight: 400;
        line-height: 1.2;
    }

    .emp-sub {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
        text-transform: capitalize;
        letter-spacing: 0.5px;
    }

    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
        text-align: center;
    }

    .kpi-value {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.2rem;
        color: var(--primary-color);
        font-weight: 400;
        line-height: 1;
    }

    .kpi-label {
        font-size: 0.72rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0.3rem;
        font-weight: 300;
    }

    .section-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem;
        color: var(--primary-color);
        font-weight: 400;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cita-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 0.6rem;
        transition: box-shadow 0.2s ease;
    }

    .cita-card:hover {
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .cita-hora {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--accent-color);
        font-weight: 400;
        min-width: 52px;
        text-align: center;
        flex-shrink: 0;
    }

    .cita-fecha-badge {
        font-size: 0.7rem;
        color: var(--text-light);
        text-align: center;
        margin-top: -2px;
    }

    .cita-info { flex: 1; min-width: 0; }

    .cita-cliente {
        font-weight: 500;
        color: var(--text-dark);
        font-size: 0.9rem;
        margin-bottom: 0.2rem;
    }

    .cita-servicios {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.65rem;
        flex-shrink: 0;
    }

    .empty-section {
        color: var(--text-light);
        font-size: 0.85rem;
        font-weight: 300;
        padding: 1.25rem 0;
        text-align: center;
        border: 1px dashed var(--border-color);
        margin-bottom: 1rem;
    }

    @media (max-width: 600px) {
        .kpi-row { grid-template-columns: repeat(3, 1fr); }
        .emp-header { flex-wrap: wrap; }
        .cita-card { flex-wrap: wrap; }
    }
</style>
@endpush

@section('content')

    {{-- Encabezado del empleado --}}
    <div class="emp-header">
        <div class="emp-avatar">{{ mb_substr($empleado->nombre, 0, 1) }}</div>
        <div>
            <div class="emp-name">{{ $empleado->nombre_completo }}</div>
            <div class="emp-sub">{{ ucfirst($empleado->cargo) }}{{ $empleado->especialidad ? ' · ' . $empleado->especialidad : '' }}</div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-value">{{ $stats['hoy'] }}</div>
            <div class="kpi-label">Citas hoy</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $stats['proximas'] }}</div>
            <div class="kpi-label">Próximas 2 sem.</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ $stats['mes'] }}</div>
            <div class="kpi-label">Completadas este mes</div>
        </div>
    </div>

    {{-- Citas de hoy --}}
    <div class="section-title">
        📅 Hoy — {{ now()->isoFormat('dddd D [de] MMMM') }}
    </div>

    @if($citasHoy->isEmpty())
        <div class="empty-section">Sin citas asignadas para hoy.</div>
    @else
        @foreach($citasHoy as $item)
            @php
                $cita = $item['cita'];
                $badgeColor = match($cita->estado) {
                    'completada' => '#10b981',
                    'confirmada' => '#3b82f6',
                    'pendiente'  => '#f59e0b',
                    'cancelada'  => '#ef4444',
                    default      => '#6b7280',
                };
            @endphp
            <div class="cita-card">
                <div>
                    <div class="cita-hora">{{ substr($cita->getRawOriginal('hora'), 0, 5) }}</div>
                </div>
                <div class="cita-info">
                    <div class="cita-cliente">{{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}</div>
                    <div class="cita-servicios">
                        {{ $item['servicios']->pluck('nombre')->join(', ') }}
                    </div>
                </div>
                <span class="badge" style="background:{{ $badgeColor }};color:#fff;">
                    {{ ucfirst($cita->estado) }}
                </span>
                @if($cita->estado === 'completada')
                    <a href="{{ route('admin.mis-citas.consumo', $cita) }}"
                       style="background:var(--accent-color);color:var(--white);border-radius:4px;padding:.3rem .8rem;font-size:.78rem;text-decoration:none;font-weight:500;flex-shrink:0;white-space:nowrap;">
                        Registrar consumo
                    </a>
                @endif
            </div>
        @endforeach
    @endif

    {{-- Próximas citas --}}
    <div class="section-title" style="margin-top:2rem;">
        🗓️ Próximas citas <span style="font-size:0.75rem;color:var(--text-light);font-family:'Montserrat',sans-serif;font-weight:300;">(siguientes 14 días)</span>
    </div>

    @if($citasProximas->isEmpty())
        <div class="empty-section">No tenés citas programadas en los próximos 14 días.</div>
    @else
        @foreach($citasProximas as $item)
            @php
                $cita = $item['cita'];
                $badgeColor = match($cita->estado) {
                    'confirmada' => '#3b82f6',
                    'pendiente'  => '#f59e0b',
                    default      => '#6b7280',
                };
            @endphp
            <div class="cita-card">
                <div>
                    <div class="cita-hora">{{ substr($cita->getRawOriginal('hora'), 0, 5) }}</div>
                    <div class="cita-fecha-badge">{{ $cita->fecha->format('d/m') }}</div>
                </div>
                <div class="cita-info">
                    <div class="cita-cliente">{{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}</div>
                    <div class="cita-servicios">
                        {{ $item['servicios']->pluck('nombre')->join(', ') }}
                    </div>
                </div>
                <span class="badge" style="background:{{ $badgeColor }};color:#fff;">
                    {{ ucfirst($cita->estado) }}
                </span>
            </div>
        @endforeach
    @endif

@endsection
