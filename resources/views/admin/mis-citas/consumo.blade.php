@extends('admin.layouts.app')

@section('title', 'Registrar Consumo')
@section('page-title', 'Registrar Consumo de Materiales')

@push('styles')
<style>
    .consumo-header {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
    }

    .consumo-cliente {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--primary-color);
        font-weight: 400;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .consumo-meta {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .servicio-bloque {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1rem;
    }

    .servicio-bloque-header {
        padding: 0.75rem 1.25rem;
        background: var(--bg-color, #fafaf8);
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .material-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }

    .material-row:last-child {
        border-bottom: none;
    }

    .material-info { flex: 1; min-width: 0; }

    .material-nombre {
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--text-dark);
    }

    .material-sub {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
        margin-top: 0.2rem;
    }

    .material-stock {
        font-size: 0.75rem;
        padding: 0.15rem 0.6rem;
        border-radius: 9999px;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .stock-ok    { background: #d1fae5; color: #065f46; }
    .stock-bajo  { background: #fef3c7; color: #92400e; }
    .stock-cero  { background: #fee2e2; color: #991b1b; }

    .usos-restantes {
        font-size: 0.72rem;
        color: var(--text-light);
        margin-top: 0.25rem;
        font-weight: 300;
    }

    .usos-restantes strong {
        font-weight: 600;
        color: var(--text-dark);
    }

    .usos-input-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .usos-label {
        font-size: 0.8rem;
        color: var(--text-light);
        white-space: nowrap;
    }

    .usos-input {
        width: 72px;
        padding: 0.35rem 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        text-align: center;
        font-size: 0.9rem;
        font-family: inherit;
    }

    .usos-input:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 2px rgba(var(--accent-rgb, 160,120,80), 0.15);
    }

    .badge-exceso {
        background: #f59e0b;
        color: #fff;
        border-radius: 9999px;
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        font-weight: 700;
    }

    .badge-guardado {
        background: #10b981;
        color: #fff;
        border-radius: 9999px;
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        font-weight: 700;
    }

    .sin-materiales {
        padding: 1rem 1.25rem;
        font-size: 0.85rem;
        color: var(--text-light);
        font-style: italic;
    }

    .error-banner {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-left: 4px solid #ef4444;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        border-radius: 2px;
    }

    .error-banner p {
        margin: 0 0 0.35rem;
        font-size: 0.85rem;
        color: #991b1b;
    }

    .error-banner p:last-child { margin-bottom: 0; }

    .form-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border-color, #e5e7eb);
    }
</style>
@endpush

@section('content')
<div style="max-width: 760px;">

    {{-- Encabezado --}}
    <div class="consumo-header">
        <div class="consumo-cliente">
            {{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}
        </div>
        <div class="consumo-meta">
            Cita del {{ $cita->fecha->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            &nbsp;·&nbsp; {{ substr($cita->getRawOriginal('hora'), 0, 5) }} h
            &nbsp;·&nbsp; <span style="color: var(--accent-color); font-weight: 500;">Completada</span>
        </div>
    </div>

    {{-- Errores de stock --}}
    @if(session('stock_errors'))
        <div class="error-banner">
            <p style="font-weight:600; margin-bottom:0.5rem;">No se pudo guardar el consumo:</p>
            @foreach(session('stock_errors') as $err)
                <p>{!! $err !!}</p>
            @endforeach
        </div>
    @endif

    @if($errors->any())
        <div class="error-banner">
            @foreach($errors->all() as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    {{-- Formulario --}}
    <form action="{{ route('admin.mis-citas.consumo.store', $cita) }}" method="POST">
        @csrf

        @php $hayAlgunMaterial = false; @endphp

        @foreach($citaServicios as $cs)
            @if($cs->servicio && $cs->servicio->materiales->isNotEmpty())
                @php $hayAlgunMaterial = true; @endphp

                <div class="servicio-bloque">
                    <div class="servicio-bloque-header">
                        ✂️ {{ $cs->servicio->nombre }}
                    </div>

                    @foreach($cs->servicio->materiales as $material)
                        @php
                            $consumo        = $consumosExistentes->get($material->id);
                            $usosActuales   = $consumo?->usos_reales ?? 1;
                            $consumoId      = $consumo?->id;
                            $stock          = $stockPorProducto[$material->producto_id] ?? 0;
                            $usosRestantes  = $usosRestantesPorMaterial[$material->id] ?? 0;
                            $yaModificado   = $consumo && $usosActuales !== 1;

                            if ($stock <= 0) {
                                $stockClass = 'stock-cero';
                                $stockLabel = 'Sin stock';
                            } elseif ($stock <= ($material->producto->stock_minimo ?? 0)) {
                                $stockClass = 'stock-bajo';
                                $stockLabel = "Stock: {$stock} unid.";
                            } else {
                                $stockClass = 'stock-ok';
                                $stockLabel = "Stock: {$stock} unid.";
                            }
                        @endphp

                        @if($consumoId)
                            <div class="material-row">
                                <div class="material-info">
                                    <div class="material-nombre">
                                        {{ $material->producto->nombre }}
                                        @if($material->producto->marca)
                                            <span style="color:#9ca3af;font-weight:400;">— {{ $material->producto->marca }}</span>
                                        @endif
                                    </div>
                                    <div class="material-sub">
                                        Estándar: {{ $material->cantidad }} {{ $material->unidad }}
                                        &nbsp;·&nbsp; 1 unidad = {{ $material->usos_por_unidad }} uso{{ $material->usos_por_unidad != 1 ? 's' : '' }}
                                    </div>
                                    @php
                                        $totalAcum      = (int)($material->total_usos_procesados ?? 0);
                                        $usosPorUnidad  = (int)$material->usos_por_unidad;
                                        $usadosEnBote   = $usosPorUnidad > 0 ? ($totalAcum % $usosPorUnidad) : 0;
                                        $restanEnBote   = $usosPorUnidad - $usadosEnBote;
                                    @endphp
                                    <div class="usos-restantes">
                                        Bote en uso: le quedan
                                        <strong @if($restanEnBote <= 3) style="color:#ef4444;" @elseif($restanEnBote <= 10) style="color:#f59e0b;" @endif>
                                            {{ $restanEnBote }}/{{ $usosPorUnidad }}
                                        </strong>
                                        usos — al agotarse se descuenta 1 del stock
                                    </div>
                                </div>

                                <span class="material-stock {{ $stockClass }}">{{ $stockLabel }}</span>

                                <div class="usos-input-wrap">
                                    <span class="usos-label">Aplicaciones:</span>
                                    <input type="number"
                                           class="usos-input"
                                           name="materiales[{{ $consumoId }}][usos_reales]"
                                           value="{{ old("materiales.{$consumoId}.usos_reales", $usosActuales) }}"
                                           min="0">
                                    <input type="hidden" name="materiales[{{ $consumoId }}][consumo_id]" value="{{ $consumoId }}">
                                </div>

                                @if($usosActuales === 0)
                                    <span style="background:#6b7280;color:#fff;border-radius:9999px;font-size:.7rem;padding:.15rem .5rem;font-weight:700;">no usado</span>
                                @elseif($usosActuales > 1)
                                    <span class="badge-guardado">✓ {{ $usosActuales }}×</span>
                                @endif
                            </div>
                        @else
                            {{-- Consumo aún no creado (procesarCita no corrió aún) --}}
                            <div class="material-row" style="opacity:0.5;">
                                <div class="material-info">
                                    <div class="material-nombre">{{ $material->producto->nombre }}</div>
                                    <div class="material-sub">Pendiente de procesamiento</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach

        @if(!$hayAlgunMaterial)
            <div style="background:var(--white);border:1px solid rgba(0,0,0,0.05);padding:2rem;text-align:center;color:var(--text-light);font-size:0.9rem;">
                Los servicios de esta cita no tienen materiales configurados.
            </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Guardar consumo
            </button>
            <a href="{{ route('admin.mis-citas') }}" class="btn btn-outline">
                ← Volver
            </a>
        </div>
    </form>

</div>
@endsection
