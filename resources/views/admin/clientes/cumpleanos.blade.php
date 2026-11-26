@extends('admin.layouts.app')

@section('title', 'Cumpleaños de clientas')
@section('page-title', 'Cumpleaños')

@include('admin.partials.fullcalendar')

@push('styles')
<style>
    .cumple-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1.5rem; align-items: start; }
    @media (max-width: 1000px) { .cumple-layout { grid-template-columns: 1fr; } }

    .cumple-item { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;
                   padding: 0.7rem 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .cumple-item:last-child { border-bottom: none; }
    .cumple-fecha { font-size: 0.75rem; color: var(--text-light); }
    .cumple-hoy { background: #fdf6e9; margin: 0 -1.25rem; padding: 0.7rem 1.25rem; }
    .cumple-hoy .cumple-fecha { color: var(--accent-color); font-weight: 500; }
</style>
@endpush

@section('content')
    <div class="cumple-layout">
        <div class="card">
            <div class="card-body">
                <div id="cumpleCalendar"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Próximos 30 días</h3>
            </div>
            <div class="card-body">
                @forelse($proximos as $p)
                    @php
                        $c = $p['cliente'];
                        $esHoy = $p['fecha']->isSameDay($hoy);
                        $dias = (int) $hoy->diffInDays($p['fecha']);
                        $tel = preg_replace('/\D/', '', $c->telefono ?? '');
                        $waNum = strlen($tel) <= 8 ? '591' . $tel : $tel;
                        $fechaEs = $p['fecha']->copy()->locale('es');
                    @endphp
                    <div class="cumple-item {{ $esHoy ? 'cumple-hoy' : '' }}">
                        <div>
                            <a href="{{ route('admin.clientes.show', $c) }}" style="color:var(--text-dark);text-decoration:none;">
                                {{ $c->nombre }} {{ $c->apellido }}
                            </a>
                            <div class="cumple-fecha">
                                @if($esHoy)
                                    🎂 ¡Hoy!
                                @elseif($dias === 1)
                                    Mañana · {{ $fechaEs->translatedFormat('d \d\e F') }}
                                @else
                                    {{ ucfirst($fechaEs->translatedFormat('l d \d\e F')) }} · en {{ $dias }} días
                                @endif
                                @if($p['edad'])
                                    · cumple {{ $p['edad'] }}
                                @endif
                            </div>
                        </div>
                        @if($tel)
                            <a href="https://wa.me/{{ $waNum }}?text={{ rawurlencode('¡Feliz cumpleaños, ' . $c->nombre . '! 🎂 Te desea todo el equipo de Cristina Spa.') }}"
                               target="_blank" rel="noopener" class="btn btn-outline btn-sm" title="Felicitar por WhatsApp">WPP</a>
                        @endif
                    </div>
                @empty
                    <p style="color:var(--text-light);font-size:0.88rem;font-style:italic;margin:0;">
                        Ninguna clienta cumple años en los próximos 30 días.
                    </p>
                @endforelse
                <p style="font-size:0.75rem;color:var(--text-light);margin:1rem 0 0;">
                    Se muestran las clientas con fecha de nacimiento registrada en su ficha.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendar = new FullCalendar.Calendar(document.getElementById('cumpleCalendar'), {
            ...FC_ES,
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
            noEventsText: 'Ninguna clienta cumple años en este mes',
            events: '{{ route('admin.clientes.cumpleanos.datos') }}',
            dayMaxEvents: 4,
        });
        calendar.render();
    });
</script>
@endpush
