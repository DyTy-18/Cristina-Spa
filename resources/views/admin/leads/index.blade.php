@extends('admin.layouts.app')

@section('title', 'Leads')
@section('page-title', 'Leads — Solicitudes Web')

@push('styles')
<style>
    .leads-filters {
        display: flex;
        gap: 0;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(0,0,0,0.1);
        background: var(--white);
        width: fit-content;
        flex-wrap: wrap;
    }
    .leads-filter-tab {
        padding: 0.55rem 1.2rem;
        font-size: 0.78rem;
        font-weight: 300;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
        color: var(--text-light);
        border-right: 1px solid rgba(0,0,0,0.1);
        transition: var(--transition);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .leads-filter-tab:last-child { border-right: none; }
    .leads-filter-tab:hover { background: var(--light-bg); color: var(--text-dark); }
    .leads-filter-tab.active { background: var(--primary-color); color: var(--white); }

    .leads-filter-tab .count-bubble {
        background: rgba(255,255,255,0.25);
        font-size: 0.7rem;
        padding: 0.05rem 0.45rem;
        border-radius: 20px;
        font-weight: 400;
    }
    .leads-filter-tab:not(.active) .count-bubble {
        background: rgba(0,0,0,0.07);
        color: var(--text-light);
    }

    .estado-select {
        border: none;
        background: transparent;
        font-size: 0.78rem;
        font-weight: 400;
        cursor: pointer;
        padding: 0.2rem 0.3rem;
        border-radius: 4px;
        outline: none;
        font-family: inherit;
    }

    .estado-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 400;
        cursor: pointer;
        transition: opacity .15s;
        user-select: none;
    }
    .estado-badge:hover { opacity: 0.8; }
    .estado-badge.nuevo      { background: #fff3cd; color: #856404; }
    .estado-badge.contactado { background: #cff4fc; color: #055160; }
    .estado-badge.convertido { background: #d1e7dd; color: #0f5132; }
    .estado-badge.perdido    { background: #f8d7da; color: #842029; }

    .mensaje-cell {
        font-size: 0.82rem;
        color: var(--text-light);
        font-style: italic;
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

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
        transition: var(--transition);
        white-space: nowrap;
    }
    .wa-btn:hover { background: #25d36615; }

    /* Estado dropdown popup */
    .estado-popup {
        display: none;
        position: absolute;
        z-index: 100;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.1);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        min-width: 160px;
        padding: 0.3rem 0;
        top: calc(100% + 4px);
        left: 0;
    }
    .estado-popup.open { display: block; }
    .estado-popup-item {
        padding: 0.5rem 1rem;
        font-size: 0.82rem;
        cursor: pointer;
        transition: background .1s;
        color: var(--text-dark);
    }
    .estado-popup-item:hover { background: var(--light-bg); }

    .estado-wrap { position: relative; display: inline-block; }
</style>
@endpush

@section('content')

    {{-- KPI cards --}}
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon">📥</div>
            <div class="stat-value">{{ $totales->sum() }}</div>
            <div class="stat-label">Total leads</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color:#856404;">🔔</div>
            <div class="stat-value" style="color:#856404;">{{ $totales['nuevo'] ?? 0 }}</div>
            <div class="stat-label">Nuevos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color:#055160;">💬</div>
            <div class="stat-value" style="color:#055160;">{{ $totales['contactado'] ?? 0 }}</div>
            <div class="stat-label">Contactados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color:#0f5132;">✅</div>
            <div class="stat-value" style="color:#0f5132;">{{ $totales['convertido'] ?? 0 }}</div>
            <div class="stat-label">Convertidos</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="leads-filters">
        @php
            $tabs = [
                'todos'      => 'Todos',
                'nuevo'      => 'Nuevos',
                'contactado' => 'Contactados',
                'convertido' => 'Convertidos',
                'perdido'    => 'Perdidos',
            ];
            $counts = [
                'todos'      => $totales->sum(),
                'nuevo'      => $totales['nuevo'] ?? 0,
                'contactado' => $totales['contactado'] ?? 0,
                'convertido' => $totales['convertido'] ?? 0,
                'perdido'    => $totales['perdido'] ?? 0,
            ];
            $estadoActivo = $estado ?: 'todos';
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.leads.index', ['estado' => $key === 'todos' ? null : $key]) }}"
               class="leads-filter-tab {{ $estadoActivo === $key ? 'active' : '' }}">
                {{ $label }}
                <span class="count-bubble">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Tabla --}}
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Solicitudes recibidas</h3>
            <span style="font-size:0.82rem;color:var(--text-light);font-weight:300;">
                Formularios enviados desde la web
            </span>
        </div>

        @if($leads->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p class="empty-state-text">No hay leads en esta categoría</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>País</th>
                        <th>Sucursal</th>
                        <th>Servicio</th>
                        <th>Mensaje</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;">Contactar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                        <tr id="lead-row-{{ $lead->id }}">
                            <td style="font-size:0.82rem;white-space:nowrap;color:var(--text-light);">
                                {{ $lead->created_at->format('d/m/Y') }}<br>
                                <span style="font-size:0.75rem;">{{ $lead->created_at->format('H:i') }}</span>
                            </td>
                            <td style="font-weight:400;">{{ $lead->nombre }}</td>
                            <td style="font-size:0.85rem;">{{ $lead->telefono }}</td>
                            <td style="font-size:0.82rem;white-space:nowrap;">
                                @if($lead->pais_codigo)
                                    @php
                                        // Convierte código ISO (BO) en emoji de bandera
                                        $flag = implode('', array_map(
                                            fn($c) => mb_chr(0x1F1E0 + ord($c) - ord('A')),
                                            str_split(strtoupper($lead->pais_codigo))
                                        ));
                                    @endphp
                                    {{ $flag }} {{ $lead->pais }}
                                @else
                                    <span style="color:var(--text-light);">—</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem;">{{ $lead->sucursal ?? '—' }}</td>
                            <td style="font-size:0.82rem;">{{ $lead->servicio ?? '—' }}</td>
                            <td>
                                @if($lead->mensaje)
                                    <span class="mensaje-cell" title="{{ $lead->mensaje }}">{{ $lead->mensaje }}</span>
                                @else
                                    <span style="color:var(--text-light);font-size:0.8rem;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <div class="estado-wrap">
                                    <span class="estado-badge {{ $lead->estado }}"
                                          onclick="toggleEstadoPopup({{ $lead->id }}, event)">
                                        {{ ucfirst($lead->estado) }} ▾
                                    </span>
                                    <div class="estado-popup" id="popup-{{ $lead->id }}">
                                        @foreach(['nuevo','contactado','convertido','perdido'] as $opt)
                                            <div class="estado-popup-item"
                                                 onclick="cambiarEstado({{ $lead->id }}, '{{ $opt }}')">
                                                {{ ucfirst($opt) }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @php
                                    $servStr  = $lead->servicio ?? 'nuestros servicios';
                                    $sucStr   = $lead->sucursal ?? '';
                                    $waText   = urlencode("Hola {$lead->nombre}, te contactamos de Cristina Spa. Recibimos tu solicitud para {$servStr} en sucursal {$sucStr}. ¿Cuándo te gustaría agendar?");
                                @endphp
                                <a href="https://wa.me/591{{ preg_replace('/\D/', '', $lead->telefono) }}?text={{ $waText }}"
                                   target="_blank" rel="noopener" class="wa-btn">
                                    <svg viewBox="0 0 32 32" width="13" height="13" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z"/></svg>
                                    WhatsApp
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginación --}}
            @if($leads->hasPages())
                <div style="padding:1rem 1.5rem;">
                    {{ $leads->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

@push('scripts')
<script>
    const LEADS_UPDATE_URL = '{{ url("admin/leads") }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Cierra todos los popups al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.estado-wrap')) {
            document.querySelectorAll('.estado-popup.open').forEach(p => p.classList.remove('open'));
        }
    });

    function toggleEstadoPopup(id, e) {
        e.stopPropagation();
        const popup = document.getElementById('popup-' + id);
        const wasOpen = popup.classList.contains('open');
        document.querySelectorAll('.estado-popup.open').forEach(p => p.classList.remove('open'));
        if (!wasOpen) popup.classList.add('open');
    }

    function cambiarEstado(id, nuevoEstado) {
        document.getElementById('popup-' + id).classList.remove('open');

        fetch(LEADS_UPDATE_URL + '/' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ estado: nuevoEstado }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            const badge = document.querySelector('#lead-row-' + id + ' .estado-badge');
            badge.className = 'estado-badge ' + data.estado;
            badge.textContent = data.estado.charAt(0).toUpperCase() + data.estado.slice(1) + ' ▾';
        });
    }
</script>
@endpush
