@extends('admin.layouts.app')

@section('title', 'Solicitudes de cita')
@section('page-title', 'Solicitudes de cita — Web')

@push('styles')
<style>
    .sol-filters {
        display: flex;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(0,0,0,0.1);
        background: var(--white);
        width: fit-content;
        flex-wrap: wrap;
    }
    .sol-filter-tab {
        padding: 0.55rem 1.2rem;
        font-size: 0.78rem;
        font-weight: 300;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
        color: var(--text-light);
        border-right: 1px solid rgba(0,0,0,0.1);
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .sol-filter-tab:last-child { border-right: none; }
    .sol-filter-tab:hover { background: var(--light-bg); color: var(--text-dark); }
    .sol-filter-tab.active { background: var(--primary-color); color: var(--white); }
    .sol-filter-tab .count-bubble {
        background: rgba(0,0,0,0.07);
        font-size: 0.7rem;
        padding: 0.05rem 0.45rem;
        border-radius: 20px;
    }
    .sol-filter-tab.active .count-bubble { background: rgba(255,255,255,0.25); }

    .sol-estado {
        border: 1px solid transparent;
        font-size: 0.75rem;
        font-family: inherit;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        cursor: pointer;
        outline: none;
    }
    .sol-estado.pendiente  { background: #fff3cd; color: #856404; }
    .sol-estado.contactado { background: #cff4fc; color: #055160; }
    .sol-estado.agendado   { background: #d1e7dd; color: #0f5132; }
    .sol-estado.descartado { background: #f8d7da; color: #842029; }

    .sol-consulta {
        font-size: 0.83rem;
        color: var(--text-dark);
        max-width: 360px;
        white-space: pre-line;
        line-height: 1.45;
    }
    .sol-meta { font-size: 0.72rem; color: var(--text-light); margin-top: 0.2rem; }

    .wa-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.78rem;
        color: #25d366;
        text-decoration: none;
        padding: 0.25rem 0.6rem;
        border: 1px solid #25d36640;
        border-radius: 4px;
        white-space: nowrap;
    }
    .wa-btn:hover { background: #25d36615; }
    .sol-hora { font-size: 0.8rem; margin-top: 0.2rem; color: var(--text-dark); }
    .sol-live-status { font-size: 0.72rem; color: var(--text-light); display: inline-flex; align-items: center; gap: .35rem; }
    .sol-live-dot { width: 7px; height: 7px; border-radius: 50%; background: #28a745; animation: solPulse 2s infinite; }
    @keyframes solPulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }
    tr.sol-highlight { animation: solFlash 2.5s ease; }
    @keyframes solFlash { 0%, 40% { background: #fff8e1; } 100% { background: transparent; } }
</style>
@endpush

@section('content')

    {{-- Todo lo que está dentro de #solLive se reemplaza cuando el polling detecta cambios --}}
    <div id="solLive" data-firma="{{ $firma }}">
    @php
        $hoyLP    = \App\Models\SolicitudCita::ahora()->toDateString();
        $mananaLP = \App\Models\SolicitudCita::ahora()->addDay()->toDateString();
    @endphp

    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon">🗓️</div>
            <div class="stat-value">{{ $totales->sum() }}</div>
            <div class="stat-label">Total solicitudes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔔</div>
            <div class="stat-value" style="color:#856404;">{{ $totales['pendiente'] ?? 0 }}</div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-value" style="color:#055160;">{{ $totales['contactado'] ?? 0 }}</div>
            <div class="stat-label">Contactados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value" style="color:#0f5132;">{{ $totales['agendado'] ?? 0 }}</div>
            <div class="stat-label">Agendados</div>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
        <div class="sol-filters">
            @php
                $tabs = ['pendiente' => 'Pendientes', 'contactado' => 'Contactados', 'agendado' => 'Agendados', 'descartado' => 'Descartados', 'todos' => 'Todos'];
            @endphp
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.solicitudes-cita.index', ['estado' => $key]) }}"
                   class="sol-filter-tab {{ $estado === $key ? 'active' : '' }}">
                    {{ $label }}
                    <span class="count-bubble">{{ $key === 'todos' ? $totales->sum() : ($totales[$key] ?? 0) }}</span>
                </a>
            @endforeach
        </div>

        <div style="font-size:0.8rem;color:var(--text-light);">
            Link público:
            <a href="{{ route('agendar') }}" target="_blank" style="color:var(--secondary-color);">{{ route('agendar') }}</a>
            <button type="button" class="btn btn-outline btn-sm" style="padding:.2rem .6rem;margin-left:.3rem;"
                    onclick="navigator.clipboard.writeText('{{ route('agendar') }}').then(() => this.textContent = 'Copiado ✓')">Copiar</button>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Solicitudes recibidas</h3>
            <span class="sol-live-status" title="La lista se actualiza sola">
                <span class="sol-live-dot"></span> En vivo · actualizado {{ \App\Models\SolicitudCita::ahora()->format('H:i:s') }}
            </span>
        </div>

        @if($solicitudes->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p class="empty-state-text">No hay solicitudes en esta categoría</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Recibida</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Fecha y hora</th>
                        <th>Consulta</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;">Contactar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitudes as $sol)
                        <tr id="sol-{{ $sol->id }}">
                            <td style="font-size:0.82rem;white-space:nowrap;color:var(--text-light);">
                                {{ $sol->created_at->format('d/m/Y') }}<br>
                                <span style="font-size:0.75rem;">{{ $sol->created_at->format('H:i') }} · {{ $sol->created_at->locale('es')->diffForHumans() }}</span>
                            </td>
                            <td style="font-weight:400;">{{ $sol->nombre }}</td>
                            <td style="font-size:0.85rem;white-space:nowrap;">
                                <a href="tel:{{ $sol->telefono }}" style="color:inherit;text-decoration:none;">{{ $sol->telefono }}</a>
                            </td>
                            <td style="font-size:0.85rem;">{{ $sol->servicio?->nombre ?? '—' }}</td>
                            <td style="font-size:0.85rem;white-space:nowrap;">
                                @php $f = $sol->fecha_preferida; $fs = $f->toDateString(); @endphp
                                <strong style="font-weight:500;{{ $fs < $hoyLP ? 'color:var(--text-light);' : '' }}">
                                    {{ ucfirst(str_replace('.', '', $f->locale('es')->isoFormat('ddd DD/MM/YYYY'))) }}
                                </strong>
                                @if($fs === $hoyLP) <span style="color:#c0392b;font-size:.72rem;">· hoy</span>
                                @elseif($fs === $mananaLP) <span style="color:#856404;font-size:.72rem;">· mañana</span>
                                @endif
                                <div class="sol-hora">🕒 {{ $sol->hora_preferida_corta }} hrs</div>
                            </td>
                            <td>
                                <div class="sol-consulta">{{ $sol->consulta }}</div>
                                @if($sol->atendidoPor)
                                    <div class="sol-meta">Atendido por {{ $sol->atendidoPor->name }} · {{ $sol->atendido_at?->format('d/m H:i') }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <form method="POST" action="{{ route('admin.solicitudes-cita.estado', $sol) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado" class="sol-estado {{ $sol->estado }}" onchange="this.form.submit()">
                                        @foreach(\App\Models\SolicitudCita::ESTADOS as $opt)
                                            <option value="{{ $opt }}" {{ $sol->estado === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td style="text-align:center;">
                                @php
                                    $waText = urlencode("Hola {$sol->nombre}, te escribimos de Cristina Spa. Recibimos tu solicitud de cita" . ($sol->servicio ? " de {$sol->servicio->nombre}" : '') . " para el {$sol->fecha_preferida->locale('es')->isoFormat('dddd D [de] MMMM')} a las {$sol->hora_preferida_corta}. ¿Te confirmamos ese horario?");
                                @endphp
                                <a href="https://wa.me/{{ $sol->telefono_wa }}?text={{ $waText }}" target="_blank" rel="noopener" class="wa-btn">
                                    <svg viewBox="0 0 32 32" width="13" height="13" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z"/></svg>
                                    WhatsApp
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($solicitudes->hasPages())
                <div style="padding:1rem 1.5rem;">{{ $solicitudes->links() }}</div>
            @endif
        @endif
    </div>
    </div>{{-- /#solLive --}}

@endsection

@push('scripts')
<script>
(function () {
    // Resalta la fila si se llegó desde una notificación (#sol-ID)
    var row = location.hash && document.querySelector(location.hash);
    if (row) { row.classList.add('sol-highlight'); row.scrollIntoView({ block: 'center' }); }

    var live = document.getElementById('solLive');
    var refrescando = false;

    function idsActuales() {
        return Array.prototype.map.call(live.querySelectorAll('tbody tr[id^="sol-"]'), function (tr) { return tr.id; });
    }

    function refrescar() {
        if (refrescando) return;

        // No interrumpir si están cambiando un estado en este momento
        var activo = document.activeElement;
        if (activo && activo.tagName === 'SELECT' && live.contains(activo)) return;

        refrescando = true;
        var antes = idsActuales();

        fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.text() : null; })
            .then(function (html) {
                if (!html) return;
                var nuevo = new DOMParser().parseFromString(html, 'text/html').getElementById('solLive');
                if (!nuevo) return;

                live.innerHTML = nuevo.innerHTML;
                live.dataset.firma = nuevo.dataset.firma;

                idsActuales().forEach(function (id) {
                    if (antes.indexOf(id) === -1) document.getElementById(id).classList.add('sol-highlight');
                });
            })
            .catch(function () {})
            .finally(function () { refrescando = false; });
    }

    // El polling global (campana) emite este evento en cada consulta con la firma actual
    document.addEventListener('solicitudes:poll', function (e) {
        if (e.detail.firma && e.detail.firma !== live.dataset.firma) refrescar();
    });
})();
</script>
@endpush
