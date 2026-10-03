<style>
    /* Página /agendar — extiende el estilo de la sección de contacto del landing */
    .agendar-hero { padding: 10rem 2rem 5rem; }
    .contact.agendar { padding: 5rem 0 7rem; }
    .agendar-inner { align-items: start; }

    .ag-step {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; margin-right: .4rem;
        border: 1px solid var(--gold-primary); border-radius: 50%;
        font-size: .68rem; letter-spacing: 0;
    }

    /* ── Calendario ── */
    .ag-cal {
        background: var(--cream);
        padding: 1.75rem 1.75rem 1.25rem;
        border: 1px solid transparent;
        transition: border-color .25s;
    }
    .ag-cal.is-invalid { border-color: #c0392b; }
    .ag-cal-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .ag-cal-month {
        font-size: 1rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;
        color: var(--dark-luxury);
    }
    .ag-cal-nav {
        width: 38px; height: 38px; border-radius: 50%;
        border: 1px solid rgba(0,0,0,.1); background: var(--white); color: var(--dark-luxury);
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: var(--transition);
    }
    .ag-cal-nav:hover:not(:disabled) { border-color: var(--gold-primary); color: var(--gold-primary); }
    .ag-cal-nav:disabled { opacity: .3; cursor: default; }

    .ag-cal-week, .ag-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: .35rem; }
    .ag-cal-week { margin-bottom: .5rem; }
    .ag-cal-week span {
        text-align: center; font-size: .68rem; font-weight: 500; letter-spacing: 1.5px;
        text-transform: uppercase; color: var(--text-muted);
    }
    .ag-cal-week span:last-child { color: rgba(0,0,0,.25); }

    .ag-day {
        aspect-ratio: 1; width: 100%; max-height: 52px;
        border: 1px solid transparent; border-radius: 50%; background: transparent;
        font-family: 'Nunito', sans-serif; font-size: .92rem; color: var(--dark-luxury);
        cursor: pointer; transition: var(--transition);
    }
    .ag-day:hover:not(:disabled) { border-color: var(--gold-primary); color: var(--gold-dark); }
    .ag-day:disabled { color: rgba(0,0,0,.2); cursor: default; }
    .ag-day.is-today { border-color: rgba(201,169,110,.5); }
    .ag-day.is-selected,
    .ag-day.is-selected:hover {
        background: var(--gold-primary); border-color: var(--gold-primary); color: var(--white);
        box-shadow: var(--shadow-gold);
    }
    .ag-cal-legend {
        margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(0,0,0,.06);
        font-size: .78rem; color: var(--text-light); text-align: center;
    }

    /* ── Horas ── */
    .ag-horas-wrap {
        margin-top: 1.25rem; padding: 1.5rem 1.75rem;
        background: var(--cream); border: 1px solid transparent; transition: border-color .25s;
    }
    .ag-horas-wrap.is-invalid { border-color: #c0392b; }
    .ag-horas-title { margin-bottom: .75rem; }
    .ag-horas-hint { font-size: .82rem; color: var(--text-light); margin-bottom: .75rem; }
    .ag-horas-hint[hidden] { display: none; }
    .ag-horas-grupo + .ag-horas-grupo { margin-top: 1rem; }
    .ag-horas-label {
        display: block; margin-bottom: .45rem;
        font-size: .68rem; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-muted);
    }
    .ag-horas { display: grid; grid-template-columns: repeat(4, 1fr); gap: .45rem; }
    .ag-hora { cursor: pointer; }
    .ag-hora input { position: absolute; opacity: 0; pointer-events: none; }
    .ag-hora span {
        display: block; text-align: center; padding: .6rem .25rem;
        background: var(--white); border: 1px solid rgba(0,0,0,.1);
        font-size: .88rem; color: var(--dark-luxury); transition: var(--transition);
    }
    .ag-hora:hover input:not(:disabled) + span { border-color: var(--gold-primary); color: var(--gold-dark); }
    .ag-hora input:checked + span {
        background: var(--gold-primary); border-color: var(--gold-primary); color: var(--white); box-shadow: var(--shadow-gold);
    }
    .ag-hora input:disabled + span { color: rgba(0,0,0,.22); background: transparent; border-style: dashed; cursor: default; }
    .ag-hora input:focus-visible + span { outline: 2px solid var(--gold-light); outline-offset: 2px; }

    /* ── Formulario ── */
    .ag-resumen {
        display: flex; align-items: center; gap: .6rem;
        padding: .85rem 1rem; margin-bottom: 1.5rem;
        background: var(--white); border-left: 2px solid var(--gold-primary);
        color: var(--dark-luxury); font-size: .9rem;
    }
    .ag-resumen[hidden] { display: none; }
    .ag-resumen svg { color: var(--gold-primary); flex-shrink: 0; }
    .ag-resumen span::first-letter { text-transform: uppercase; }

    .ag-phone {
        display: flex; background: var(--white);
        border: 1px solid rgba(0,0,0,0.1); transition: border-color .25s, box-shadow .25s;
    }
    .ag-phone:focus-within { border-color: var(--gold-primary); box-shadow: 0 0 0 3px rgba(201,169,110,0.1); }
    .contact-form .ag-phone input { border: none; box-shadow: none; letter-spacing: 1px; }
    .ag-phone-prefix {
        display: flex; align-items: center; padding: 0 1rem;
        border-right: 1px solid rgba(0,0,0,.08); background: var(--champagne);
        font-size: .9rem; color: var(--dark-luxury); font-weight: 600; user-select: none;
    }

    .contact-form .is-invalid, .ag-phone.is-invalid { border-color: #c0392b; }
    .ag-err { font-size: .78rem; color: #c0392b; margin-top: .3rem; }

    .ag-submit { background: var(--gold-primary); letter-spacing: 2px; text-transform: uppercase; }
    .ag-submit:disabled { opacity: .6; cursor: wait; transform: none; }
    .ag-note { font-size: .8rem; color: var(--text-light); text-align: center; margin-top: 1rem; }

    .ag-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

    @media (max-width: 992px) {
        .agendar-inner { grid-template-columns: 1fr; gap: 3rem; }
    }
    @media (max-width: 480px) {
        .agendar-hero { padding: 8rem 1rem 3.5rem; }
        .contact.agendar { padding: 3rem 0 5rem; }
        .agendar-inner { padding: 0 1rem; }
        .ag-cal { padding: 1.25rem 1rem 1rem; }
        .ag-cal-week, .ag-cal-grid { gap: .2rem; }
        .ag-horas-wrap { padding: 1.25rem 1rem; }
        .ag-horas { gap: .3rem; }
        .ag-hora span { font-size: .82rem; }
    }
</style>
