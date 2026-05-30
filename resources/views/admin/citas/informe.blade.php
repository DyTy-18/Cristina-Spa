@extends('admin.layouts.app')

@section('title', 'Informe de Cita')
@section('page-title', 'Informe de Cita')

@push('styles')
<style>
    .informe-wrap { max-width: 700px; }

    .informe-header {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 2rem;
        margin-bottom: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .informe-cliente-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.9rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 0.5px;
        line-height: 1.15;
        margin-bottom: 0.4rem;
    }

    .informe-meta {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 300;
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem 1.25rem;
    }

    .informe-meta span strong {
        color: var(--text-dark);
        font-weight: 400;
    }

    .informe-estado {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.9rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        flex-shrink: 0;
    }

    .informe-section {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1.25rem;
    }

    .informe-section-title {
        padding: 0.75rem 1.5rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text-light);
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background: var(--bg-color, #fafaf8);
    }

    .informe-row {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }

    .informe-row:last-child { border-bottom: none; }

    .informe-row-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(var(--accent-rgb, 160,120,80), 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .informe-row-main { flex: 1; min-width: 0; }

    .informe-row-nombre {
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--text-dark);
    }

    .informe-row-sub {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
        margin-top: 0.15rem;
    }

    .informe-row-right {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        color: var(--primary-color);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .material-usos-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #f3f4f6;
        border-radius: 9999px;
        padding: 0.15rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
    }

    .material-usos-badge.exceso {
        background: #fef3c7;
        color: #92400e;
    }

    .informe-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: var(--bg-color, #fafaf8);
        border-top: 1px solid rgba(0,0,0,0.07);
    }

    .informe-total-label {
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--text-light);
    }

    .informe-total-value {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--secondary-color, #8b7355);
        font-weight: 400;
    }

    .informe-empty {
        padding: 1.25rem 1.5rem;
        font-size: 0.85rem;
        color: var(--text-light);
        font-style: italic;
    }

    @media print {
        .no-print { display: none !important; }
        .informe-wrap { max-width: 100%; }
        body { background: #fff; }
        .informe-section { break-inside: avoid; }
    }
</style>
@endpush

@section('content')
<div class="informe-wrap">

    {{-- Acciones --}}
    <div class="no-print" style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
        <a href="{{ route('admin.citas.show', $cita) }}" class="btn btn-outline btn-sm">
            ← Volver a la cita
        </a>
        <button onclick="window.print()" class="btn btn-outline btn-sm">
            🖨 Imprimir
        </button>
    </div>

    {{-- Encabezado --}}
    <div class="informe-header">
        <div>
            <div class="informe-cliente-name">
                {{ $cita->cliente?->nombre_completo ?? 'Sin cliente' }}
            </div>
            <div class="informe-meta">
                <span><strong>Fecha:</strong> {{ $cita->fecha->isoFormat('dddd D [de] MMMM [de] YYYY') }}</span>
                <span><strong>Hora:</strong> {{ substr($cita->getRawOriginal('hora'), 0, 5) }}</span>
                @if($cita->cliente?->telefono)
                    <span><strong>Tel:</strong> {{ $cita->cliente->telefono }}</span>
                @endif
            </div>
        </div>
        @php
            $estadoColors = [
                'completada' => ['bg'=>'#d1fae5','color'=>'#065f46'],
                'confirmada' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                'pendiente'  => ['bg'=>'#fef3c7','color'=>'#92400e'],
                'cancelada'  => ['bg'=>'#fee2e2','color'=>'#991b1b'],
            ];
            $ec = $estadoColors[$cita->estado] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
        @endphp
        <span class="informe-estado" style="background:{{ $ec['bg'] }};color:{{ $ec['color'] }};">
            {{ ucfirst($cita->estado) }}
        </span>
    </div>

    {{-- Servicios y empleados --}}
    <div class="informe-section">
        <div class="informe-section-title">Servicios Realizados</div>

        @forelse($cita->citaServicios as $cs)
            <div class="informe-row">
                <div class="informe-row-icon">✂</div>
                <div class="informe-row-main">
                    <div class="informe-row-nombre">
                        {{ $cs->servicio?->nombre ?? 'Servicio eliminado' }}
                    </div>
                    @if($cs->empleado)
                        <div class="informe-row-sub">
                            Atendido por <strong style="color:var(--text-dark);font-weight:500;">{{ $cs->empleado->nombre_completo }}</strong>
                            @if($cs->empleado->cargo)
                                · {{ ucfirst($cs->empleado->cargo) }}
                            @endif
                        </div>
                    @else
                        <div class="informe-row-sub" style="font-style:italic;">Sin empleado asignado</div>
                    @endif
                    @if($cs->servicio?->duracion_minutos)
                        <div class="informe-row-sub">Duración: {{ $cs->servicio->duracion_formateada }}</div>
                    @endif
                </div>
                @if($cs->precio_unitario !== null)
                    <div class="informe-row-right">
                        @if($cs->descuento_porcentaje > 0)
                            <span style="font-size:0.8rem;text-decoration:line-through;color:var(--text-light);">
                                Bs. {{ number_format($cs->precio_unitario, 2) }}
                            </span>
                            <br>
                        @endif
                        Bs. {{ number_format($cs->precio_neto ?? $cs->precio_unitario, 2) }}
                    </div>
                @endif
            </div>
        @empty
            <div class="informe-empty">Sin servicios registrados.</div>
        @endforelse

        @if($cita->precio_final)
            <div class="informe-total-row">
                <span class="informe-total-label">Total</span>
                <span class="informe-total-value">Bs. {{ number_format($cita->precio_final, 2) }}</span>
            </div>
        @endif
    </div>

    {{-- Materiales utilizados --}}
    @php
        $hayMateriales = false;
        foreach ($cita->citaServicios as $cs) {
            if ($cs->servicio && $cs->servicio->materiales->isNotEmpty()) {
                $hayMateriales = true;
                break;
            }
        }
    @endphp

    <div class="informe-section">
        <div class="informe-section-title">Materiales Utilizados</div>

        @if($hayMateriales)
            @foreach($cita->citaServicios as $cs)
                @if($cs->servicio && $cs->servicio->materiales->isNotEmpty())
                    @foreach($cs->servicio->materiales as $material)
                        @php
                            $consumo     = $consumos->get($material->id);
                            $usosReales  = $consumo?->usos_reales ?? 1;
                            $esExceso    = $usosReales > 1;
                        @endphp
                        <div class="informe-row">
                            <div class="informe-row-icon">🧴</div>
                            <div class="informe-row-main">
                                <div class="informe-row-nombre">
                                    {{ $material->producto->nombre }}
                                    @if($material->producto->marca)
                                        <span style="color:#9ca3af;font-weight:400;">— {{ $material->producto->marca }}</span>
                                    @endif
                                </div>
                                <div class="informe-row-sub">
                                    Servicio: {{ $cs->servicio->nombre }}
                                    &nbsp;·&nbsp; Estándar: {{ $material->cantidad }} {{ $material->unidad }}
                                    &nbsp;·&nbsp; 1 unidad = {{ $material->usos_por_unidad }} uso{{ $material->usos_por_unidad != 1 ? 's' : '' }}
                                </div>
                            </div>
                            <div style="flex-shrink:0; display:flex; align-items:center; gap:0.5rem;">
                                @if(!$consumo)
                                    <span class="material-usos-badge">Sin registro</span>
                                @elseif($usosReales === 0)
                                    <span class="material-usos-badge" style="background:#f3f4f6;color:#6b7280;">No usado</span>
                                @else
                                    <span class="material-usos-badge {{ $esExceso ? 'exceso' : '' }}">
                                        {{ $usosReales }} aplicación{{ $usosReales != 1 ? 'es' : '' }}
                                        @if($esExceso) ⚠ @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        @else
            <div class="informe-empty">Los servicios de esta cita no tienen materiales configurados.</div>
        @endif
    </div>

    {{-- Notas --}}
    @if($cita->notas)
        <div class="informe-section">
            <div class="informe-section-title">Notas</div>
            <div style="padding: 1rem 1.5rem; font-size:0.9rem; color:var(--text-dark); font-weight:300; white-space:pre-line;">{{ $cita->notas }}</div>
        </div>
    @endif

</div>
@endsection
