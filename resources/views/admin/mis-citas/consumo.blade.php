@extends('admin.layouts.app')

@section('title', 'Registrar Consumo')
@section('page-title', 'Registrar Consumo de Materiales')

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header" style="margin-bottom:1.25rem;">
        <h3 style="margin:0 0 .25rem;">
            {{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}
        </h3>
        <p style="margin:0;color:#666;font-size:.9rem;">
            Cita del {{ $cita->fecha->format('d/m/Y') }} a las {{ substr($cita->getRawOriginal('hora'), 0, 5) }}
        </p>
    </div>

    <form action="{{ route('admin.mis-citas.consumo.store', $cita) }}" method="POST">
        @csrf

        @foreach($citaServicios as $cs)
            @if($cs->servicio && $cs->servicio->materiales->isNotEmpty())
                <div style="margin-bottom:1.5rem;">
                    <h4 style="font-weight:600;margin-bottom:.75rem;color:#374151;">
                        {{ $cs->servicio->nombre }}
                    </h4>

                    @foreach($cs->servicio->materiales as $material)
                        @php
                            $consumoExistente = $consumosExistentes->get($material->id)?->first();
                            $usosActuales = $consumoExistente?->usos_reales ?? 1;
                            $consumoId = $consumoExistente?->id;
                        @endphp

                        @if($consumoId)
                            <div style="display:flex;align-items:center;gap:1rem;padding:.6rem 0;border-bottom:1px solid #f3f4f6;">
                                <div style="flex:1;">
                                    <span style="font-weight:500;">{{ $material->producto->nombre }}</span>
                                    @if($material->producto->marca)
                                        <span style="color:#888;font-size:.85rem;"> — {{ $material->producto->marca }}</span>
                                    @endif
                                    <div style="font-size:.8rem;color:#6b7280;margin-top:.2rem;">
                                        Estándar: {{ $material->cantidad }} {{ $material->unidad }}
                                        &nbsp;·&nbsp; {{ $material->usos_por_unidad }} usos/unidad
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <label style="font-size:.85rem;color:#374151;">Cabezas usadas:</label>
                                    <input type="number"
                                           name="materiales[{{ $consumoId }}][usos_reales]"
                                           value="{{ $usosActuales }}"
                                           min="1"
                                           style="width:70px;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:6px;text-align:center;">
                                    <input type="hidden" name="materiales[{{ $consumoId }}][consumo_id]" value="{{ $consumoId }}">

                                    @if($usosActuales > 1)
                                        <span style="background:#f59e0b;color:#fff;border-radius:9999px;font-size:.7rem;padding:.15rem .5rem;font-weight:700;">
                                            exceso
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach

        <div style="display:flex;align-items:center;gap:1rem;margin-top:1.5rem;">
            <button type="submit"
                    style="background:#3b82f6;color:#fff;border:none;border-radius:6px;padding:.6rem 1.5rem;font-weight:600;cursor:pointer;">
                Guardar consumo
            </button>
            <a href="{{ route('admin.mis-citas') }}"
               style="color:#6b7280;text-decoration:none;font-size:.9rem;">
                ← Volver a mis citas
            </a>
        </div>
    </form>
</div>
@endsection
