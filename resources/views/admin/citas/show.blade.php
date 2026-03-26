@extends('admin.layouts.app')

@section('title', 'Cita — ' . ($cita->cliente?->nombre_completo ?? 'Sin cliente'))
@section('page-title', 'Detalle de Cita')

@push('styles')
<style>
    .cita-header {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .cita-header-left { flex: 1; min-width: 0; }
    .cita-cliente-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 1px;
        line-height: 1.15;
        margin-bottom: 0.5rem;
    }
    .cita-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .cita-meta-item {
        font-size: 0.88rem;
        color: var(--text-light);
        font-weight: 300;
    }
    .cita-meta-item strong {
        color: var(--text-dark);
        font-weight: 400;
    }
    .cita-precio-big {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.4rem;
        color: var(--secondary-color);
        line-height: 1;
        text-align: right;
    }
    .cita-precio-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-light);
        text-align: right;
        margin-bottom: 0.25rem;
    }

    .detail-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1.25rem;
    }
    .detail-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 0.8px;
    }
    .detail-card-body {
        padding: 1.5rem;
    }

    .servicio-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        gap: 1rem;
    }
    .servicio-row:last-child { border-bottom: none; }
    .servicio-nombre {
        font-size: 0.92rem;
        color: var(--text-dark);
        font-weight: 400;
    }
    .servicio-info {
        font-size: 0.8rem;
        color: var(--text-light);
        font-weight: 300;
        margin-top: 0.15rem;
    }
    .servicio-precio {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.25rem;
        color: var(--secondary-color);
        white-space: nowrap;
        text-align: right;
    }
    .servicio-precio-original {
        font-family: 'Cormorant Garamond', serif;
        font-size: 0.95rem;
        color: var(--text-light);
        text-decoration: line-through;
        display: block;
        text-align: right;
    }
    .servicio-desc-badge {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 500;
        letter-spacing: 0.3px;
        color: var(--white);
        background: var(--success-color);
        padding: 0.1rem 0.4rem;
        border-radius: 2px;
        margin-left: 0.35rem;
        vertical-align: middle;
    }
    .servicio-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        margin-top: 0.25rem;
        border-top: 1px solid rgba(0,0,0,0.08);
    }
    .servicio-total-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-light);
    }
    .servicio-total-val {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--secondary-color);
    }

    .detail-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }
    .detail-main { min-width: 0; }
    .detail-aside { position: sticky; top: calc(var(--header-height) + 1rem); }

    @media (max-width: 860px) {
        .detail-layout { grid-template-columns: 1fr; }
        .detail-aside { position: static; }
    }
</style>
@endpush

@section('content')

    {{-- Top bar --}}
    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.citas.calendario') }}" class="btn btn-sm btn-outline">← Calendario</a>
        <a href="{{ route('admin.citas.edit', $cita) }}" class="btn btn-sm btn-accent">Editar cita</a>
    </div>

    @if(session('success'))
        <div style="background:rgba(40,167,69,0.1);border:1px solid rgba(40,167,69,0.3);color:#1e7e34;padding:0.75rem 1rem;font-size:0.88rem;margin-bottom:1.25rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header card --}}
    <div class="cita-header">
        <div class="cita-header-left">
            <div class="cita-cliente-name">
                @if($cita->cliente)
                    <a href="{{ route('admin.clientes.show', $cita->cliente) }}"
                       style="color:inherit;text-decoration:none;">
                        {{ $cita->cliente->nombre_completo }}
                    </a>
                @else
                    Sin cliente
                @endif
            </div>
            <div class="cita-meta">
                <span class="cita-meta-item">
                    <strong>Fecha</strong>
                    {{ $cita->fecha->translatedFormat('l d \\d\\e F \\d\\e Y') }}
                </span>
                <span class="cita-meta-item">
                    <strong>Hora</strong>
                    {{ substr($cita->getRawOriginal('hora'), 0, 5) }}
                </span>
                @php
                    $badgeMap = [
                        'completada' => 'badge-success',
                        'confirmada' => 'badge-info',
                        'pendiente'  => 'badge-warning',
                        'cancelada'  => 'badge-danger',
                    ];
                @endphp
                <span class="badge {{ $badgeMap[$cita->estado] ?? 'badge-info' }}" style="font-size:0.8rem;">
                    {{ ucfirst($cita->estado) }}
                </span>
            </div>
        </div>
        @if($cita->precio_final)
            <div>
                <div class="cita-precio-label">Total</div>
                <div class="cita-precio-big">Bs. {{ number_format($cita->precio_final, 2) }}</div>
            </div>
        @endif
    </div>

    <div class="detail-layout">

        {{-- Main --}}
        <div class="detail-main">

            {{-- Servicios --}}
            <div class="detail-card">
                <div class="detail-card-header">Servicios</div>
                <div class="detail-card-body" style="padding-top:0.5rem;padding-bottom:0.5rem;">
                    @php
                        $totalOriginal  = $cita->citaServicios->sum(fn($cs) => (float)($cs->precio_unitario ?? 0));
                        $totalAhorro    = $cita->citaServicios->sum(fn($cs) => (float)($cs->precio_unitario ?? 0) - (float)($cs->precio_neto ?? $cs->precio_unitario ?? 0));
                        $hayDescuentos  = $cita->citaServicios->contains(fn($cs) => $cs->descuento_porcentaje > 0);
                    @endphp
                    @forelse($cita->citaServicios as $cs)
                        <div class="servicio-row">
                            <div>
                                <div class="servicio-nombre">
                                    {{ $cs->servicio?->nombre ?? 'Servicio eliminado' }}
                                    @if($cs->descuento_porcentaje > 0)
                                        <span class="servicio-desc-badge">
                                            -{{ rtrim(rtrim(number_format($cs->descuento_porcentaje, 2), '0'), '.') }}%
                                        </span>
                                    @endif
                                </div>
                                @if($cs->empleado)
                                    <div class="servicio-info">
                                        Realizado por {{ $cs->empleado->nombre_completo }}
                                    </div>
                                @endif
                            </div>
                            <div class="servicio-precio">
                                @if($cs->precio_unitario !== null)
                                    @if($cs->descuento_porcentaje > 0)
                                        <span class="servicio-precio-original">Bs. {{ number_format($cs->precio_unitario, 2) }}</span>
                                        Bs. {{ number_format($cs->precio_neto, 2) }}
                                    @else
                                        Bs. {{ number_format($cs->precio_unitario, 2) }}
                                    @endif
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    @empty
                        <p style="color:var(--text-light);font-style:italic;font-size:0.88rem;padding:0.5rem 0;">Sin servicios registrados.</p>
                    @endforelse

                    @if($cita->citaServicios->count() > 0 && $cita->precio_final)
                        @if($hayDescuentos && $totalAhorro > 0)
                            <div class="servicio-total-row" style="border-top:none;padding-top:0.4rem;margin-top:0;">
                                <span class="servicio-total-label" style="color:var(--text-light);">Subtotal</span>
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--text-light);text-decoration:line-through;">
                                    Bs. {{ number_format($totalOriginal, 2) }}
                                </span>
                            </div>
                            <div class="servicio-total-row" style="border-top:none;padding-top:0.2rem;margin-top:0;">
                                <span class="servicio-total-label" style="color:var(--success-color);">Ahorro</span>
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--success-color);">
                                    − Bs. {{ number_format($totalAhorro, 2) }}
                                </span>
                            </div>
                        @endif
                        <div class="servicio-total-row">
                            <span class="servicio-total-label">Total</span>
                            <span class="servicio-total-val">Bs. {{ number_format($cita->precio_final, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notas --}}
            @if($cita->notas)
                <div class="detail-card">
                    <div class="detail-card-header">Notas</div>
                    <div class="detail-card-body">
                        <p style="font-size:0.9rem;color:var(--text-dark);font-weight:300;white-space:pre-line;margin:0;">{{ $cita->notas }}</p>
                    </div>
                </div>
            @endif

        </div>

        {{-- Aside --}}
        <div class="detail-aside">

            {{-- Cliente --}}
            @if($cita->cliente)
                <div class="detail-card" style="margin-bottom:1rem;">
                    <div class="detail-card-header">Cliente</div>
                    <div class="detail-card-body" style="padding:1.25rem 1.5rem;">
                        <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--primary-color);margin-bottom:0.5rem;">
                            {{ $cita->cliente->nombre_completo }}
                        </div>
                        @if($cita->cliente->telefono)
                            <div style="font-size:0.85rem;color:var(--text-light);font-weight:300;margin-bottom:0.25rem;">
                                📞 {{ $cita->cliente->telefono }}
                            </div>
                        @endif
                        @if($cita->cliente->email)
                            <div style="font-size:0.85rem;color:var(--text-light);font-weight:300;margin-bottom:0.75rem;">
                                ✉ {{ $cita->cliente->email }}
                            </div>
                        @endif
                        <a href="{{ route('admin.clientes.show', $cita->cliente) }}"
                           class="btn btn-outline btn-sm" style="width:100%;text-align:center;display:block;">
                            Ver historial
                        </a>
                    </div>
                </div>
            @endif

            {{-- Campaña --}}
            @if($cita->campana)
                <div class="detail-card" style="margin-bottom:1rem;border-left:3px solid var(--accent-color);">
                    <div class="detail-card-header" style="font-size:0.95rem;">📣 Campaña</div>
                    <div class="detail-card-body" style="padding:1rem 1.5rem;">
                        <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--primary-color);line-height:1.2;margin-bottom:0.35rem;">
                            {{ $cita->campana->nombre }}
                        </div>
                        @if($cita->campana->descripcion)
                            <div style="font-size:0.8rem;color:var(--text-light);font-weight:300;">
                                {{ $cita->campana->descripcion }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Acciones --}}
            <div class="detail-card">
                <div class="detail-card-header">Acciones</div>
                <div class="detail-card-body" style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:0.6rem;">
                    <a href="{{ route('admin.citas.edit', $cita) }}" class="btn btn-accent" style="text-align:center;justify-content:center;">
                        Editar cita
                    </a>
                    <a href="{{ route('admin.citas.calendario') }}" class="btn btn-outline btn-sm" style="text-align:center;display:block;">
                        Ver calendario
                    </a>
                    @if($cita->cliente)
                        <a href="{{ route('admin.clientes.show', $cita->cliente) }}" class="btn btn-outline btn-sm" style="text-align:center;display:block;">
                            Perfil del cliente
                        </a>
                    @endif
                </div>
            </div>

        </div>

    </div>

@endsection
