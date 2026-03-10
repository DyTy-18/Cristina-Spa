@extends('admin.layouts.app')

@section('title', 'Calendario de Citas')
@section('page-title', 'Calendario')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
    /* ===== Override content padding para el calendario ===== */
    .content-body {
        padding: 0;
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--header-height));
        overflow: hidden;
    }

    /* ===== Barra superior del calendario ===== */
    .cal-topbar {
        padding: 1rem 2rem;
        background: var(--white);
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .cal-legend {
        display: flex;
        gap: 1.2rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        color: var(--text-light);
        font-weight: 300;
        letter-spacing: 0.3px;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* ===== Layout principal ===== */
    .cal-layout {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .cal-main {
        flex: 1;
        padding: 1.5rem 2rem;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    /* ===== Panel de detalle ===== */
    .cal-detail {
        width: 0;
        overflow: hidden;
        background: var(--white);
        border-left: 1px solid rgba(0,0,0,0.06);
        transition: width 0.3s ease;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
    }

    .cal-detail.open {
        width: 320px;
    }

    .cal-detail-inner {
        width: 320px;
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .detail-close {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .detail-close-btn {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        color: var(--text-light);
        padding: 0.2rem 0.4rem;
        transition: color 0.2s;
    }
    .detail-close-btn:hover { color: var(--text-dark); }

    .detail-estado {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 400;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .detail-service {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--primary-color);
        font-weight: 400;
        line-height: 1.1;
        margin-bottom: 0.3rem;
    }

    .detail-client {
        font-size: 1rem;
        color: var(--secondary-color);
        font-weight: 300;
        margin-bottom: 1.5rem;
    }

    .detail-divider {
        height: 1px;
        background: rgba(0,0,0,0.06);
        margin: 1.2rem 0;
    }

    .detail-row {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        margin-bottom: 1rem;
    }

    .detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-light);
        font-weight: 300;
    }

    .detail-value {
        font-size: 0.92rem;
        color: var(--text-dark);
        font-weight: 300;
    }

    .detail-price {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.8rem;
        color: var(--secondary-color);
        line-height: 1;
        margin: 1rem 0;
    }

    .detail-notas {
        font-size: 0.82rem;
        color: var(--text-light);
        font-style: italic;
        font-weight: 300;
        line-height: 1.5;
        background: var(--light-bg);
        padding: 0.75rem 1rem;
        border-left: 2px solid var(--accent-color);
    }

    /* ===== FullCalendar theme overrides ===== */
    .fc {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.85rem;
    }

    .fc .fc-toolbar-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        font-weight: 400;
        color: var(--primary-color);
        letter-spacing: 1px;
    }

    .fc .fc-button {
        background: var(--primary-color);
        border-color: var(--primary-color);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.75rem;
        font-weight: 300;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-radius: 0;
        padding: 0.4rem 0.9rem;
    }

    .fc .fc-button:hover {
        background: var(--accent-color);
        border-color: var(--accent-color);
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .fc .fc-today-button:disabled {
        background: var(--text-light);
        border-color: var(--text-light);
        opacity: 0.5;
    }

    .fc .fc-col-header-cell {
        font-weight: 400;
        font-size: 0.75rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--text-light);
        padding: 0.5rem 0;
    }

    .fc .fc-daygrid-day-number {
        font-size: 0.82rem;
        color: var(--text-dark);
        font-weight: 300;
        padding: 0.4rem;
    }

    .fc .fc-day-today .fc-daygrid-day-number {
        background: var(--accent-color);
        color: var(--white);
        border-radius: 50%;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fc .fc-day-today {
        background: rgba(201, 169, 110, 0.06) !important;
    }

    .fc .fc-event {
        border-radius: 0;
        font-size: 0.75rem;
        font-weight: 300;
        cursor: pointer;
        border-left-width: 3px;
        border-top-width: 0;
        border-right-width: 0;
        border-bottom-width: 0;
        padding: 1px 4px;
    }

    .fc .fc-event:hover {
        opacity: 0.88;
    }

    .fc .fc-event-selected,
    .fc .fc-event:focus {
        box-shadow: 0 0 0 2px var(--accent-color);
    }

    .fc .fc-timegrid-slot {
        height: 2.5rem;
    }

    .fc .fc-timegrid-slot-label {
        font-size: 0.72rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .fc .fc-scrollgrid {
        border-radius: 0;
    }

    .fc th, .fc td {
        border-color: rgba(0,0,0,0.06);
    }

    /* Multimonth year view */
    .fc .fc-multimonth-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        font-weight: 400;
        letter-spacing: 1px;
    }

    /* ===== Event selected highlight ===== */
    .fc-event.fc-event-selected {
        outline: 2px solid var(--accent-color);
        outline-offset: 1px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 900px) {
        .cal-detail.open {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            width: 300px !important;
            z-index: 500;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
        }
        .cal-detail-inner { width: 300px; }
        .cal-main { padding: 1rem; }
        .cal-topbar { padding: 0.75rem 1rem; }
    }

    @media (max-width: 576px) {
        .content-body {
            height: auto;
            overflow: visible;
        }
        .cal-layout { flex-direction: column; }
        .cal-main { overflow: visible; }
    }
</style>
@endpush

@section('content')

    {{-- Barra superior con leyenda --}}
    <div class="cal-topbar">
        <div class="cal-legend">
            <span style="font-size:0.75rem;color:var(--text-light);letter-spacing:0.5px;text-transform:uppercase;font-weight:300;">Estado</span>
            <div class="legend-item">
                <div class="legend-dot" style="background:#ffc107;"></div> Pendiente
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background:#c9a96e;"></div> Confirmada
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background:#28a745;"></div> Completada
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background:#e8726b;"></div> Cancelada
            </div>
        </div>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('admin.citas.index') }}" class="btn btn-sm btn-outline">Ver lista</a>
        </div>
    </div>

    {{-- Layout calendar + panel --}}
    <div class="cal-layout">
        <div class="cal-main">
            <div id="calendar"></div>
        </div>

        <div class="cal-detail" id="detailPanel">
            <div class="cal-detail-inner">
                <div class="detail-close">
                    <button class="detail-close-btn" onclick="closeDetail()" title="Cerrar">✕</button>
                </div>
                <div id="detailContent">
                    {{-- Populated by JS --}}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    const CALENDAR_DATOS_URL = '{{ route('admin.citas.calendarDatos') }}';
    const HISTORIAL_BASE_URL  = '{{ url('admin/clientes') }}';

    let selectedEventEl = null;

    const estadoBadge = {
        pendiente:  { bg: '#ffc107', color: '#1a1a1a' },
        confirmada: { bg: '#c9a96e', color: '#fff' },
        completada: { bg: '#28a745', color: '#fff' },
        cancelada:  { bg: '#e8726b', color: '#fff' },
    };

    function closeDetail() {
        document.getElementById('detailPanel').classList.remove('open');
        if (selectedEventEl) {
            selectedEventEl.style.outline = '';
            selectedEventEl = null;
        }
    }

    function renderDetail(props) {
        const badge = estadoBadge[props.estado] ?? estadoBadge.pendiente;
        const precio = props.precio ? 'Bs. ' + parseFloat(props.precio).toFixed(2) : null;
        const historialUrl = props.cliente_id ? `${HISTORIAL_BASE_URL}/${props.cliente_id}/historial` : null;

        return `
            <div class="detail-estado" style="background:${badge.bg};color:${badge.color};">
                ${props.estado}
            </div>
            <div class="detail-service">${props.servicio ?? '—'}</div>
            <div class="detail-client">${props.cliente ?? 'Cliente desconocido'}</div>

            <div class="detail-divider"></div>

            <div class="detail-row">
                <span class="detail-label">Fecha</span>
                <span class="detail-value">${props.fecha_fmt ?? '—'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Hora</span>
                <span class="detail-value">${props.hora ?? '—'}${props.duracion ? ' &nbsp;·&nbsp; ' + props.duracion : ''}</span>
            </div>
            ${props.empleado ? `
            <div class="detail-row">
                <span class="detail-label">Profesional</span>
                <span class="detail-value">${props.empleado}</span>
            </div>` : ''}

            ${precio ? `<div class="detail-price">${precio}</div>` : ''}

            ${props.notas ? `
            <div class="detail-divider"></div>
            <div class="detail-notas">${props.notas}</div>` : ''}

            ${historialUrl ? `
            <div class="detail-divider"></div>
            <a href="${historialUrl}" class="btn btn-sm btn-outline" style="width:100%;text-align:center;display:block;">
                Ver historial del cliente →
            </a>` : ''}
        `;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const calEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            firstDay: 1,
            nowIndicator: true,
            dayMaxEvents: 4,
            height: 'auto',

            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridDay,multiMonthYear',
            },

            views: {
                timeGridDay: {
                    buttonText: 'Día',
                    slotMinTime: '07:00:00',
                    slotMaxTime: '21:00:00',
                    slotDuration: '00:30:00',
                    slotLabelInterval: '01:00',
                    allDaySlot: false,
                },
                dayGridMonth: {
                    buttonText: 'Mes',
                },
                multiMonthYear: {
                    buttonText: 'Año',
                    multiMonthMaxColumns: 3,
                    multiMonthMinWidth: 200,
                },
            },

            eventTimeFormat: {
                hour:   '2-digit',
                minute: '2-digit',
                hour12: false,
            },

            events: function (info, successCb, failureCb) {
                fetch(`${CALENDAR_DATOS_URL}?start=${info.startStr}&end=${info.endStr}`)
                    .then(r => r.json())
                    .then(data => successCb(data))
                    .catch(() => failureCb());
            },

            eventClick: function (info) {
                // Highlight selected
                if (selectedEventEl) selectedEventEl.style.outline = '';
                selectedEventEl = info.el;
                info.el.style.outline = '2px solid #c9a96e';
                info.el.style.outlineOffset = '1px';

                // Populate & open panel
                document.getElementById('detailContent').innerHTML = renderDetail(info.event.extendedProps);
                document.getElementById('detailPanel').classList.add('open');
            },

            // Close panel when clicking on empty day in month view
            dateClick: function () {
                closeDetail();
            },
        });

        calendar.render();

        // ESC closes the panel
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDetail();
        });
    });
</script>
@endpush
