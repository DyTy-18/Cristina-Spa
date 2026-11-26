{{--
    FullCalendar (CDN) con el tema del panel y textos en español.
    Incluir fuera de las secciones; en la config del calendario usar: { ...FC_ES, ... }.
--}}
@once
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
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


    /* "noviembre de 2026" -> "Noviembre de 2026" */
    .fc .fc-toolbar-title::first-letter,
    .fc .fc-multimonth-title::first-letter,
    .fc .fc-list-day-text::first-letter,
    .fc .fc-list-day-side-text::first-letter { text-transform: uppercase; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    // Textos explícitos: no dependen de que cargue el archivo de idiomas.
    const FC_ES = {
        locale: 'es',
        firstDay: 1,
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', year: 'Año', list: 'Lista' },
        allDayText: 'Todo el día',
        noEventsText: 'No hay eventos para mostrar',
        moreLinkText: n => `+${n} más`,
        weekText: 'Sm',
    };
</script>
@endpush
@endonce
