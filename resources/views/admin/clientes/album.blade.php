@extends('admin.layouts.app')

@section('title', 'Álbum — ' . $cliente->nombre . ' ' . $cliente->apellido)
@section('page-title', 'Álbum de fotos')

@section('content')
    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-sm btn-outline">← Volver a la ficha</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">
                {{ $cliente->nombre }} {{ $cliente->apellido }}
                <span style="font-size:0.8rem;font-weight:300;color:var(--text-light);">
                    · {{ $fotos->count() }} {{ $fotos->count() === 1 ? 'foto' : 'fotos' }}
                </span>
            </h3>
        </div>
        <div class="card-body">
            <p style="font-size:0.82rem;color:var(--text-light);margin:0;">
                Historial de resultados de sus servicios. Las fotos se suben desde el <strong>Seguimiento</strong> de cada cita.
            </p>
        </div>
    </div>

    @forelse($grupos as $citaId => $fotosGrupo)
        @php $cita = $fotosGrupo->first()->cita; @endphp
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h3 class="card-title" style="font-size:1.05rem;">
                    @if($cita)
                        {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                        <span style="font-size:0.8rem;font-weight:300;color:var(--text-light);">
                            · {{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->unique()->implode(' · ') ?: 'Sin servicios' }}
                        </span>
                    @else
                        Cita eliminada
                    @endif
                </h3>
                @if($cita)
                    <a href="{{ route('admin.citas.seguimiento.show', $cita) }}" class="btn btn-outline btn-sm">Ir al seguimiento</a>
                @endif
            </div>
            <div class="card-body">
                @include('admin.clientes._fotos_grid', ['fotos' => $fotosGrupo])
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <p style="color:var(--text-light);font-size:0.88rem;font-style:italic;margin:0;">
                    Esta clienta todavía no tiene fotos. Súbelas desde el seguimiento de una cita.
                </p>
            </div>
        </div>
    @endforelse
@endsection
