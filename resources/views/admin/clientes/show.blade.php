@extends('admin.layouts.app')

@section('title', $cliente->nombre_completo . ' — Historial')
@section('page-title', 'Historial del Cliente')

@push('styles')
<style>
    /* ===== Client Profile ===== */
    .client-profile {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 2rem;
        display: flex;
        align-items: flex-start;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .client-avatar-lg {
        width: 72px;
        height: 72px;
        background: var(--accent-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--white);
        flex-shrink: 0;
    }

    .client-meta {
        flex: 1;
    }

    .client-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 1px;
        line-height: 1.1;
    }

    .client-details {
        display: flex;
        gap: 1.5rem;
        margin-top: 0.6rem;
        flex-wrap: wrap;
    }

    .client-detail-item {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .client-detail-item strong {
        color: var(--text-dark);
        font-weight: 400;
    }

    /* ===== Period Filter Tabs ===== */
    .period-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 2rem;
        border: 1px solid rgba(0,0,0,0.1);
        background: var(--white);
        width: fit-content;
        flex-wrap: wrap;
    }

    .period-tab {
        padding: 0.6rem 1.3rem;
        font-size: 0.78rem;
        font-weight: 300;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-decoration: none;
        color: var(--text-light);
        border-right: 1px solid rgba(0,0,0,0.1);
        transition: var(--transition);
        white-space: nowrap;
    }

    .period-tab:last-child {
        border-right: none;
    }

    .period-tab:hover {
        background: var(--light-bg);
        color: var(--text-dark);
    }

    .period-tab.active {
        background: var(--primary-color);
        color: var(--white);
    }

    /* ===== Agenda (reemplaza timeline) ===== */
    .agenda-wrapper {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.06);
        overflow: visible;
    }

    .agenda-month-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 1.4rem;
        background: rgba(0,0,0,0.022);
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .agenda-month-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 0.95rem;
        color: var(--secondary-color);
        letter-spacing: 2.5px;
        text-transform: uppercase;
        font-weight: 400;
    }

    .agenda-month-count {
        font-size: 0.68rem;
        color: var(--text-light);
        letter-spacing: 0.5px;
    }

    .agenda-row {
        display: grid;
        grid-template-columns: 56px 1fr auto;
        gap: 0;
        border-bottom: 1px solid rgba(0,0,0,0.045);
        transition: background 0.15s;
        position: relative;
    }
    .agenda-row:last-child { border-bottom: none; }
    .agenda-row:hover { background: rgba(0,0,0,0.014); }

    .agenda-date-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1rem 0;
        border-right: 1px solid rgba(0,0,0,0.045);
        flex-shrink: 0;
    }

    .agenda-day-num {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--primary-color);
        line-height: 1;
        font-weight: 400;
    }

    .agenda-day-name {
        font-size: 0.62rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 0.1rem;
    }

    .agenda-hour {
        font-size: 0.65rem;
        color: var(--accent-color);
        letter-spacing: 0.3px;
        margin-top: 0.25rem;
        font-weight: 400;
    }

    .agenda-body-col {
        padding: 0.9rem 1.2rem;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.2rem;
    }

    .agenda-service-name {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.15rem;
        color: var(--primary-color);
        font-weight: 400;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .agenda-sub {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
    }

    .agenda-notes {
        font-size: 0.73rem;
        color: var(--text-light);
        font-style: italic;
        font-weight: 300;
        margin-top: 0.1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .agenda-right-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        padding: 0.9rem 1.2rem;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .agenda-price {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.2rem;
        color: var(--secondary-color);
        white-space: nowrap;
    }

    /* ===== Status pill / inline changer ===== */
    .estado-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.22rem 0.65rem;
        font-size: 0.68rem;
        font-weight: 400;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        cursor: pointer;
        border: 1px solid transparent;
        transition: opacity 0.15s, box-shadow 0.15s;
        user-select: none;
        position: relative;
        white-space: nowrap;
    }
    .estado-pill:hover { opacity: 0.82; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .estado-pill::after { content: ' ▾'; font-size: 0.55rem; opacity: 0.7; }
    .estado-pill.loading::after { content: ' ···'; }

    .estado-pill.completada { background: rgba(34,197,94,0.1);  border-color: rgba(34,197,94,0.3);  color: #15803d; }
    .estado-pill.confirmada { background: rgba(201,169,110,0.12); border-color: rgba(201,169,110,0.4); color: var(--accent-color); }
    .estado-pill.pendiente  { background: rgba(234,179,8,0.1);  border-color: rgba(234,179,8,0.3);  color: #92400e; }
    .estado-pill.cancelada  { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.25); color: #b91c1c; }

    .estado-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.1);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 500;
        min-width: 140px;
        overflow: hidden;
    }
    .estado-menu.open { display: block; }

    .estado-menu-item {
        padding: 0.5rem 0.9rem;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-dark);
        transition: background 0.12s;
        white-space: nowrap;
    }
    .estado-menu-item:hover { background: var(--light-bg); }
    .estado-menu-item.current { color: var(--text-light); cursor: default; }
    .estado-menu-item.current:hover { background: transparent; }

    .estado-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .estado-dot.completada { background: #22c55e; }
    .estado-dot.confirmada { background: var(--accent-color); }
    .estado-dot.pendiente  { background: #eab308; }
    .estado-dot.cancelada  { background: #ef4444; }

    /* ===== Service icon cards in modal ===== */
    .servicio-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
        gap: 0.5rem;
    }
    .servicio-card {
        border: 1px solid rgba(0,0,0,0.1);
        padding: 0.85rem 0.5rem 0.7rem;
        cursor: pointer;
        text-align: center;
        transition: var(--transition);
        background: var(--white);
        user-select: none;
        position: relative;
    }
    .servicio-card:hover {
        border-color: var(--accent-color);
        background: var(--light-bg);
    }
    .servicio-card.sel {
        border-color: var(--primary-color);
        background: var(--primary-color);
    }
    .servicio-card.sel .sc-name { color: var(--white); }
    .servicio-card.sel .sc-price { color: rgba(255,255,255,0.7); }
    .sc-check {
        position: absolute;
        top: 5px; right: 7px;
        font-size: 0.6rem;
        color: var(--white);
        opacity: 0;
        transition: var(--transition);
        font-weight: 700;
        letter-spacing: 0;
    }
    .servicio-card.sel .sc-check { opacity: 1; }
    .sc-icon { font-size: 1.8rem; display: block; margin-bottom: 0.35rem; line-height: 1; }
    .sc-name {
        font-size: 0.67rem;
        color: var(--text-dark);
        line-height: 1.25;
        letter-spacing: 0.2px;
    }
    .sc-price {
        font-size: 0.62rem;
        color: var(--text-light);
        margin-top: 0.18rem;
    }

    /* Price panel for selected services */
    .sc-precio-row {
        display: grid;
        grid-template-columns: 1fr 100px 88px auto;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.7rem;
        background: var(--light-bg);
        border: 1px solid rgba(0,0,0,0.06);
        margin-bottom: 0.35rem;
    }
    .sc-precio-name {
        font-size: 0.8rem;
        color: var(--text-dark);
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sc-precio-wrap { position: relative; }
    .sc-precio-prefix {
        position: absolute;
        left: 0.6rem; top: 50%;
        transform: translateY(-50%);
        font-size: 0.72rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .sc-precio-input { padding-left: 1.9rem !important; width: 100%; }

    .sc-desc-wrap { position: relative; }
    .sc-desc-suffix {
        position: absolute;
        right: 0.55rem; top: 50%;
        transform: translateY(-50%);
        font-size: 0.72rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .sc-desc-input { padding-right: 1.6rem !important; width: 100%; }

    .sc-neto {
        font-size: 0.78rem;
        color: var(--secondary-color);
        font-family: 'Cormorant Garamond', serif;
        font-size: 1rem;
        white-space: nowrap;
        text-align: right;
    }
    .sc-neto.has-desc { color: var(--success-color); }

    /* Discount badge in timeline */
    .desc-badge {
        display: inline-block;
        font-size: 0.67rem;
        font-weight: 400;
        letter-spacing: 0.3px;
        color: var(--white);
        background: var(--success-color);
        padding: 0.1rem 0.4rem;
        border-radius: 2px;
        vertical-align: middle;
        margin-left: 0.35rem;
    }

    /* ===== Líneas de profesionales en modal ===== */
    .linea-prof-modal {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 0.6rem;
        align-items: end;
        padding: 0.6rem;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.07);
        margin-bottom: 0.5rem;
    }
    .btn-remove-linea-modal {
        background: none;
        border: 1px solid rgba(0,0,0,0.12);
        color: var(--text-light);
        cursor: pointer;
        padding: 0.4rem 0.55rem;
        font-size: 0.85rem;
        line-height: 1;
        height: 38px;
        transition: var(--transition);
    }
    .btn-remove-linea-modal:hover { color: var(--error-color); border-color: var(--error-color); }

    /* ===== Pestañas Servicios / Paquetes en modal ===== */
    .modal-srv-tabs {
        display: flex;
        align-items: stretch;
        border-bottom: 1px solid rgba(0,0,0,.08);
        background: var(--light-bg);
        margin-bottom: 1rem;
    }
    .modal-srv-tab {
        padding: .55rem 1.25rem;
        font-size: .72rem;
        letter-spacing: .7px;
        text-transform: uppercase;
        font-weight: 400;
        cursor: pointer;
        background: transparent;
        border: none;
        color: var(--text-light);
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: var(--transition);
        white-space: nowrap;
    }
    .modal-srv-tab:hover:not(.active) { color: var(--text-dark); }
    .modal-srv-tab.active { color: var(--primary-color); border-bottom-color: var(--accent-color); background: var(--white); font-weight: 500; }
    .modal-pkg-chip {
        display: none;
        align-items: center;
        gap: .3rem;
        margin-left: auto;
        padding: 0 .75rem;
        font-size: .7rem;
        color: #166534;
        background: rgba(34,197,94,.06);
        border-left: 1px solid rgba(0,0,0,.06);
    }
    .modal-pkg-chip.show { display: flex; }

    /* Package cards in modal */
    .modal-pkg-cat-strip { display: flex; gap: 0; margin-bottom: .75rem; flex-wrap: wrap; border: 1px solid rgba(0,0,0,.09); width: fit-content; }
    .modal-pkg-cat-btn {
        padding: .3rem .85rem;
        font-size: .68rem;
        letter-spacing: .5px;
        text-transform: uppercase;
        cursor: pointer;
        background: var(--white);
        border: none;
        border-right: 1px solid rgba(0,0,0,.09);
        color: var(--text-light);
        transition: var(--transition);
    }
    .modal-pkg-cat-btn:last-child { border-right: none; }
    .modal-pkg-cat-btn.active { background: var(--primary-color); color: #fff; }
    .modal-pkg-cat-btn:not(.active):hover { background: var(--light-bg); }

    .modal-pkg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .5rem; }
    .modal-pkg-card {
        border: 1px solid rgba(0,0,0,.08);
        padding: .65rem .75rem;
        cursor: pointer;
        transition: var(--transition);
    }
    .modal-pkg-card:hover { border-color: var(--accent-color); box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .modal-pkg-card.applied { border-color: #22c55e; background: rgba(34,197,94,.05); }

    /* ===== Modal ===== */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop.open { display: flex; }

    .modal-box {
        background: var(--white);
        width: 100%;
        max-width: 720px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .modal-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--primary-color);
        letter-spacing: 1px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        color: var(--text-light);
        padding: 0.2rem 0.5rem;
        transition: var(--transition);
    }
    .modal-close:hover { color: var(--text-dark); }

    .modal-body { padding: 1.5rem; }

    /* ===== Period summary bar ===== */
    .period-summary {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 1.5rem;
        display: flex;
        gap: 2.5rem;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .period-summary-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .period-summary-value {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.6rem;
        color: var(--primary-color);
        line-height: 1;
    }

    .period-summary-label {
        font-size: 0.72rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* ===== Campaign cards in modal ===== */
    .campana-cards-modal {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.4rem;
    }
    .campana-card-modal {
        border: 1px solid rgba(0,0,0,0.12);
        padding: 0.45rem 0.85rem;
        cursor: pointer;
        font-size: 0.78rem;
        letter-spacing: 0.5px;
        color: var(--text-light);
        background: var(--white);
        transition: var(--transition);
        user-select: none;
    }
    .campana-card-modal:hover { border-color: var(--accent-color); color: var(--text-dark); }
    .campana-card-modal.selected {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--white);
    }

    /* ===== Sección Recomendaciones ===== */
    .reco-section {
        margin-top: 2rem;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .reco-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.9rem 1.4rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .reco-section-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.1rem;
        color: var(--primary-color);
        letter-spacing: 1px;
    }
    .reco-leads-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
    }
    .reco-leads-table th {
        padding: 0.5rem 1rem;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-light);
        font-weight: 400;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        background: rgba(0,0,0,0.018);
    }
    .reco-leads-table td {
        padding: 0.65rem 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        color: var(--text-dark);
        font-weight: 300;
    }
    .reco-leads-table tr:last-child td { border-bottom: none; }
    .reco-leads-table tr:hover td { background: rgba(0,0,0,0.012); }

    /* ===== Modal Recomendación ===== */
    .reco-modal-link-box {
        background: var(--light-bg);
        border: 1px solid rgba(0,0,0,0.08);
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1rem;
    }
    .reco-modal-link-url {
        flex: 1;
        font-size: 0.75rem;
        color: var(--text-dark);
        word-break: break-all;
        font-weight: 300;
    }
    .reco-modal-stats {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .reco-modal-stat {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }
    .reco-modal-stat-val {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        color: var(--primary-color);
        line-height: 1;
    }
    .reco-modal-stat-lbl {
        font-size: 0.68rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@section('content')

    {{-- Back + actions --}}
    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.clientes.index') }}" class="btn btn-sm btn-outline">← Todos los clientes</a>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-sm btn-outline">
                Editar datos
            </a>
            <a href="{{ route('admin.contratos.index', ['cliente_id' => $cliente->id]) }}" class="btn btn-sm btn-outline">
                📋 Contratos
            </a>
            <button type="button" class="btn btn-sm btn-outline" onclick="generarEnlaceRecomendacion()"
                    id="btnGenRecomendacion" data-url="{{ route('admin.clientes.recomendacion.generar', $cliente) }}">
                🔗 Recomendar
            </button>
            <button type="button" class="btn btn-accent btn-sm" onclick="document.getElementById('modalVisita').classList.add('open')">
                + Registrar visita
            </button>
        </div>
    </div>

    {{-- Profile card --}}
    <div class="client-profile">
        <div class="client-avatar-lg">{{ mb_substr($cliente->nombre, 0, 1) }}</div>
        <div class="client-meta">
            <div class="client-name">{{ $cliente->nombre_completo }}</div>
            <div class="client-details">
                @if($cliente->email)
                    <span class="client-detail-item"><strong>Email</strong> {{ $cliente->email }}</span>
                @endif
                @if($cliente->telefono)
                    <span class="client-detail-item"><strong>Tel.</strong> {{ $cliente->telefono }}</span>
                @endif
                @if($cliente->fecha_nacimiento)
                    <span class="client-detail-item"><strong>Nacimiento</strong> {{ $cliente->fecha_nacimiento->format('d/m/Y') }}</span>
                @endif
                @if($cliente->direccion)
                    <span class="client-detail-item"><strong>Dir.</strong> {{ $cliente->direccion }}</span>
                @endif
            </div>
            @if($cliente->notas)
                <p style="margin-top:0.6rem;font-size:0.82rem;color:var(--text-light);font-style:italic;font-weight:300;">
                    {{ $cliente->notas }}
                </p>
            @endif

            {{-- Sucursal de registro + sucursales visitadas --}}
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem;align-items:center;">
                @if($cliente->sucursal)
                    <span style="font-size:0.7rem;color:var(--text-light);text-transform:uppercase;letter-spacing:0.05em;">Registrado en:</span>
                    <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.65rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:20px;color:#0369a1;font-size:0.75rem;">
                        🏢 {{ $cliente->sucursal->nombre }}
                    </span>
                @endif
                @if($sucursalesVisitadas->isNotEmpty())
                    <span style="font-size:0.7rem;color:var(--text-light);text-transform:uppercase;letter-spacing:0.05em;margin-left:0.25rem;">Visitó:</span>
                    @foreach($sucursalesVisitadas as $sv)
                        <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.18rem 0.65rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;color:#166534;font-size:0.75rem;">
                            ✓ {{ $sv->nombre }}
                        </span>
                    @endforeach
                @endif
            </div>

            @php $cuponPendiente = $cliente->cuponRecomendacionActivo(); @endphp
            @if($cuponPendiente)
                <div style="margin-top:0.75rem;display:inline-flex;align-items:center;gap:0.5rem;background:rgba(201,169,110,0.1);border:1px solid rgba(201,169,110,0.35);padding:0.35rem 0.8rem;">
                    <span style="font-size:0.95rem;">🎁</span>
                    <span style="font-size:0.72rem;color:var(--accent-color);letter-spacing:0.5px;">
                        Cupón de recomendación activo —
                        <strong style="font-weight:500;">{{ $cuponPendiente->valor_label }} off en su próxima visita</strong>
                        · se aplicará automáticamente
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon">💆</div>
            <div class="stat-value">{{ $stats['visitas_mes'] }}</div>
            <div class="stat-label">Visitas este mes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">Bs. {{ number_format($stats['total_mes'], 0) }}</div>
            <div class="stat-label">Total este mes</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-value" style="font-size:1.6rem;">
                {{ $stats['ultima_visita'] ? \Carbon\Carbon::parse($stats['ultima_visita'])->format('d/m/Y') : '—' }}
            </div>
            <div class="stat-label">Última visita</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✂️</div>
            <div class="stat-value" style="font-size:1.4rem;line-height:1.2;">
                {{ $ultimoServicio ?: '—' }}
            </div>
            <div class="stat-label">Último servicio</div>
        </div>
    </div>

    {{-- Period filter --}}
    <div class="period-tabs">
        @php
            $tabs = ['recientes' => 'Últimas 4', 'todo' => 'Todo el historial', 'anio' => 'Este año', 'trimestre' => 'Último trimestre', 'mes' => 'Este mes'];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.clientes.show', [$cliente, 'periodo' => $key]) }}"
               class="period-tab {{ $periodo === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Period summary bar --}}
    @if($citas->count() > 0)
        <div class="period-summary">
            <div class="period-summary-item">
                <span class="period-summary-value">{{ $citas->count() }}</span>
                <span class="period-summary-label">Citas en período</span>
            </div>
            <div class="period-summary-item">
                <span class="period-summary-value">{{ $citas->where('estado','completada')->count() }}</span>
                <span class="period-summary-label">Completadas</span>
            </div>
            <div class="period-summary-item">
                <span class="period-summary-value">Bs. {{ number_format($citas->where('estado','completada')->sum('precio_final'), 0) }}</span>
                <span class="period-summary-label">Gastado en período</span>
            </div>
            @if($citas->where('estado','cancelada')->count() > 0)
                <div class="period-summary-item">
                    <span class="period-summary-value" style="color:var(--error-color);">{{ $citas->where('estado','cancelada')->count() }}</span>
                    <span class="period-summary-label">Canceladas</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Agenda --}}
    @if($citas->isEmpty())
        <div class="table-container">
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p class="empty-state-text">No hay citas registradas en este período</p>
                <a href="{{ route('admin.clientes.show', [$cliente, 'periodo' => 'todo']) }}" class="btn btn-outline">
                    Ver todo el historial
                </a>
            </div>
        </div>
    @else
        <div class="agenda-wrapper">
            @foreach($citasPorMes as $mesKey => $citasDelMes)
                @php
                    $fechaMes  = \Carbon\Carbon::createFromFormat('Y-m', $mesKey);
                    $mesLabel  = mb_convert_case($fechaMes->translatedFormat('F Y'), MB_CASE_TITLE, 'UTF-8');
                    $countMes  = count($citasDelMes);
                @endphp
                <div class="agenda-month-header">
                    <span class="agenda-month-name">{{ $mesLabel }}</span>
                    <span class="agenda-month-count">{{ $countMes }} {{ $countMes === 1 ? 'visita' : 'visitas' }}</span>
                </div>

                @foreach($citasDelMes as $cita)
                    @php
                        $fechaCita    = \Carbon\Carbon::parse($cita->fecha);
                        $nombresServs = $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter();
                        $nombresEmps  = $cita->citaServicios
                                            ->filter(fn($cs) => $cs->empleado)
                                            ->map(fn($cs) => $cs->empleado->nombre)
                                            ->unique();
                        $descuentos   = $cita->citaServicios->filter(fn($cs) => $cs->descuento_porcentaje > 0);
                        $updateUrl    = route('admin.clientes.cita.estado', [$cliente, $cita]);
                    @endphp
                    <div class="agenda-row" id="row-cita-{{ $cita->id }}">

                        {{-- Columna fecha --}}
                        <div class="agenda-date-col">
                            <span class="agenda-day-num">{{ $fechaCita->format('d') }}</span>
                            <span class="agenda-day-name">{{ mb_substr($fechaCita->translatedFormat('D'), 0, 3) }}</span>
                            <span class="agenda-hour">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                        </div>

                        {{-- Columna cuerpo --}}
                        <div class="agenda-body-col">
                            <div class="agenda-service-name">
                                {{ $nombresServs->isNotEmpty() ? $nombresServs->implode(' · ') : 'Servicio no registrado' }}
                                @foreach($descuentos as $cs)
                                    <span class="desc-badge">-{{ rtrim(rtrim(number_format($cs->descuento_porcentaje, 2), '0'), '.') }}%</span>
                                @endforeach
                            </div>
                            @if($nombresEmps->isNotEmpty())
                                <div class="agenda-sub">con {{ $nombresEmps->implode(', ') }}</div>
                            @endif
                            @if($cita->sucursal)
                                <div class="agenda-sub" style="color:#0369a1;">🏢 {{ $cita->sucursal->nombre }}</div>
                            @endif
                            @if($cita->notas)
                                <div class="agenda-notes" title="{{ $cita->notas }}">{{ $cita->notas }}</div>
                            @endif
                        </div>

                        {{-- Columna derecha: precio + estado pill --}}
                        <div class="agenda-right-col">
                            @if($cita->precio_final)
                                <div class="agenda-price">Bs. {{ number_format($cita->precio_final, 0) }}</div>
                            @endif

                            <div style="position:relative;">
                                <div class="estado-pill {{ $cita->estado }}"
                                     data-cita-id="{{ $cita->id }}"
                                     data-url="{{ $updateUrl }}"
                                     onclick="toggleEstadoMenu(this)">
                                    {{ $cita->estado }}
                                </div>
                                <div class="estado-menu" id="emenu-{{ $cita->id }}">
                                    @foreach(['completada','confirmada','pendiente','cancelada'] as $est)
                                        <div class="estado-menu-item {{ $cita->estado === $est ? 'current' : '' }}"
                                             data-estado="{{ $est }}"
                                             onclick="cambiarEstado('{{ $cita->id }}', '{{ $est }}', '{{ $updateUrl }}')">
                                            <span class="estado-dot {{ $est }}"></span>
                                            {{ ucfirst($est) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    {{-- Sección Recomendaciones --}}
    @php
        $recoLinks = $cliente->recomendacionLinks()->with('leads')->latest()->get();
        $recoLeads = \App\Models\Lead::whereIn('recomendacion_link_id', $recoLinks->pluck('id'))
                        ->with(['servicioRel','cita'])
                        ->latest()
                        ->get();
    @endphp

    <div class="reco-section">
        <div class="reco-section-header">
            <span class="reco-section-title">Recomendaciones</span>
            <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                @if($recoLeads->count() > 0)
                    <span style="font-size:0.75rem;color:var(--text-light);">
                        {{ $recoLeads->count() }} {{ $recoLeads->count() === 1 ? 'persona registrada' : 'personas registradas' }}
                    </span>
                @endif
                <button type="button" class="btn btn-sm btn-outline"
                        id="btnGenRecomendacion2"
                        data-url="{{ route('admin.clientes.recomendacion.generar', $cliente) }}"
                        onclick="generarEnlaceRecomendacion()">
                    🔗 Generar enlace de recomendación
                </button>
            </div>
        </div>

        @if($recoLeads->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:var(--text-light);font-size:0.82rem;font-style:italic;font-weight:300;">
                Aún no hay personas registradas mediante este cliente.
            </div>
        @else
            <table class="reco-leads-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Sucursal</th>
                        <th>Servicio</th>
                        <th>Cita</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recoLeads as $lead)
                        <tr>
                            <td>
                                @if($lead->cliente)
                                    <a href="{{ route('admin.clientes.show', $lead->cliente) }}"
                                       style="color:var(--primary-color);text-decoration:none;">
                                        {{ $lead->nombre }}
                                    </a>
                                @else
                                    {{ $lead->nombre }}
                                @endif
                            </td>
                            <td>{{ $lead->telefono }}</td>
                            <td>{{ $lead->sucursal ?? '—' }}</td>
                            <td>{{ $lead->servicio ?? '—' }}</td>
                            <td>
                                @if($lead->cita)
                                    <span class="badge {{ ['completada'=>'badge-success','confirmada'=>'badge-info','pendiente'=>'badge-warning','cancelada'=>'badge-danger'][$lead->cita->estado] ?? 'badge-info' }}">
                                        {{ $lead->cita->estado }}
                                    </span>
                                @else
                                    <span style="color:var(--text-light);">—</span>
                                @endif
                            </td>
                            <td style="color:var(--text-light);">
                                {{ $lead->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Modal: Enlace de recomendación --}}
    <div id="modalRecomendacion" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="modal-box" style="max-width:520px;">
            <div class="modal-header">
                <h3 class="modal-title">Enlace de recomendación</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('modalRecomendacion').classList.remove('open')">✕</button>
            </div>
            <div class="modal-body">
                <div id="recoModalLoading" style="text-align:center;padding:1.5rem;color:var(--text-light);font-size:0.82rem;">
                    Generando enlace...
                </div>
                <div id="recoModalContent" style="display:none;">
                    <div class="reco-modal-stats" id="recoModalStats"></div>

                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);margin-bottom:0.4rem;">
                        Enlace para compartir
                    </div>
                    <div class="reco-modal-link-box">
                        <span class="reco-modal-link-url" id="recoModalUrl"></span>
                        <button type="button" onclick="copiarEnlace()" class="btn btn-sm btn-outline" style="flex-shrink:0;">
                            Copiar
                        </button>
                    </div>

                    <a id="recoWhatsappBtn" href="#" target="_blank" rel="noopener"
                       style="display:flex;align-items:center;justify-content:center;gap:0.6rem;width:100%;padding:0.85rem;background:#25d366;color:#fff;font-size:0.8rem;font-weight:400;letter-spacing:1px;text-decoration:none;text-transform:uppercase;transition:opacity 0.15s;"
                       onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Enviar por WhatsApp
                    </a>

                    <p style="margin-top:0.85rem;font-size:0.72rem;color:var(--text-light);font-style:italic;text-align:center;line-height:1.5;">
                        La persona que abra el enlace obtendrá <strong style="color:var(--text-dark);font-weight:400;">10% de descuento</strong> en su primera visita.<br>
                        Tú recibirás un <strong style="color:var(--text-dark);font-weight:400;">cupón de 10%</strong> automáticamente cuando se registre.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Registrar visita --}}
    <div id="modalVisita" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
        <div class="modal-box">
            <div class="modal-header">
                <h3 class="modal-title">Registrar visita</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('modalVisita').classList.remove('open')">✕</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.clientes.storeCita', $cliente) }}" method="POST">
                    @csrf

                    {{-- Pestañas Servicios / Paquetes --}}
                    <div class="form-group">
                        {{-- Tab strip --}}
                        <div class="modal-srv-tabs">
                            <button type="button" class="modal-srv-tab active" id="mTabBtnServicios"
                                onclick="switchModalTab('servicios')">✂️ Servicios</button>
                            <button type="button" class="modal-srv-tab" id="mTabBtnPaquetes"
                                onclick="switchModalTab('paquetes')">🎁 Paquetes</button>
                            <div class="modal-pkg-chip" id="modalPkgChip">
                                <span>✓</span>
                                <span id="modalPkgChipText"></span>
                                <button type="button" onclick="clearModalPaquete()"
                                    style="background:none;border:none;cursor:pointer;color:#166534;font-size:.85rem;padding:0;"
                                    title="Quitar paquete">✕</button>
                            </div>
                            <span id="scCountBadge" style="display:none;margin-left:auto;margin-right:.75rem;align-self:center;font-size:.72rem;font-weight:400;color:var(--accent-color);letter-spacing:0.5px;"></span>
                        </div>

                        {{-- Panel: Servicios --}}
                        <div id="mPanelServicios">
                            {{-- Filtro por categoría --}}
                            <div class="modal-pkg-cat-strip" id="scCatStrip" style="margin-bottom:0.6rem;">
                                <button type="button" class="modal-pkg-cat-btn active" data-cat="" onclick="filterScCat(this, '')">Todas</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="peluqueria" onclick="filterScCat(this, 'peluqueria')">Peluquería</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="peinados" onclick="filterScCat(this, 'peinados')">Peinados</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="coloracion" onclick="filterScCat(this, 'coloracion')">Coloración</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="alisado" onclick="filterScCat(this, 'alisado')">Alisado</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="depilacion" onclick="filterScCat(this, 'depilacion')">Depilación</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="maquillaje" onclick="filterScCat(this, 'maquillaje')">Maquillaje</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="pies_manos" onclick="filterScCat(this, 'pies_manos')">Pies y Manos</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="extensiones" onclick="filterScCat(this, 'extensiones')">Extensiones</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="spa" onclick="filterScCat(this, 'spa')">Spa</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="tratamientos" onclick="filterScCat(this, 'tratamientos')">Tratamientos</button>
                            </div>
                            <div id="servicioCardsGrid" class="servicio-cards-grid"></div>
                        <div id="scErrorMsg" style="display:none;color:var(--error-color);font-size:0.78rem;margin-top:0.4rem;">
                            Selecciona al menos un servicio para continuar.
                        </div>

                        {{-- Price editing panel (visible when ≥1 card selected) --}}
                        <div id="scPreciosPanel" style="margin-top:0.9rem;display:none;">
                            <div style="display:grid;grid-template-columns:1fr 100px 88px auto;gap:0.5rem;padding:0 0.7rem;margin-bottom:0.3rem;">
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Servicio</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Precio</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);">Descuento</span>
                                <span style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);text-align:right;">Neto</span>
                            </div>
                            <div id="scPreciosContainer"></div>
                        </div>

                            {{-- Hidden inputs injected here by JS before submit --}}
                            <div id="scHiddenInputs"></div>
                        </div>{{-- end mPanelServicios --}}

                        {{-- Panel: Paquetes --}}
                        <div id="mPanelPaquetes" style="display:none;">
                            <div class="modal-pkg-cat-strip">
                                <button type="button" class="modal-pkg-cat-btn active" data-cat="todos" onclick="modalFilterCat('todos')">Todos</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="novia" onclick="modalFilterCat('novia')">👰 Novia</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="quinceañera" onclick="modalFilterCat('quinceañera')">🌸 Quinceañera</button>
                                <button type="button" class="modal-pkg-cat-btn" data-cat="eventos" onclick="modalFilterCat('eventos')">🎉 Eventos</button>
                            </div>
                            <div class="modal-pkg-grid" id="modalPkgCards"></div>
                            <p style="font-size:.72rem;color:var(--text-light);margin-top:.65rem;font-style:italic;">
                                Al seleccionar un paquete se marcan los servicios automáticamente.
                            </p>
                        </div>
                    </div>{{-- end form-group tabs --}}

                    {{-- Profesionales --}}
                    <div class="form-group" style="margin-top:1rem;">
                        <label class="form-label" style="margin-bottom:0.2rem;">Profesionales</label>
                        <div style="font-size:0.75rem;color:var(--text-light);font-style:italic;margin-bottom:0.4rem;">Opcional — asigna quién realizó cada servicio</div>
                        <div id="modalProfHeadersRow" style="display:none;grid-template-columns:1fr 1fr auto;gap:0.4rem;margin-bottom:0.3rem;padding:0 0.1rem;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Profesional</span>
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Realizó</span>
                            <span></span>
                        </div>
                        <div id="modalProfesionalesContainer"></div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addModalProfesional()" style="margin-top:0.25rem;">
                            + Agregar profesional
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fecha <span style="color:var(--error-color)">*</span></label>
                            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hora <span style="color:var(--error-color)">*</span></label>
                            <input type="time" name="hora" class="form-control" value="10:00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estado <span style="color:var(--error-color)">*</span></label>
                        <select name="estado" class="form-control" required>
                            <option value="completada">Completada</option>
                            <option value="confirmada" selected>Confirmada</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipo de pago</label>
                        <select name="tipo_pago" class="form-control">
                            <option value="">— Sin especificar —</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="qr">QR</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Sucursal <span style="color:var(--error-color)">*</span></label>
                        <select name="sucursal_id" class="form-control" required>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}"
                                    {{ (old('sucursal_id', $sucursalActiva->id ?? null) == $s->id) ? 'selected' : '' }}>
                                    {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notas de la visita</label>
                        <textarea name="notas" class="form-control" rows="2"
                                  placeholder="Observaciones, preferencias del cliente..."></textarea>
                    </div>

                    @if($campanas->isNotEmpty())
                    <div class="form-group">
                        <label class="form-label">Campaña <span style="font-size:0.75rem;font-weight:300;color:var(--text-light);">(opcional)</span></label>
                        <input type="hidden" name="campana_id" id="modalCampanaId" value="">
                        <div class="campana-cards-modal">
                            <div class="campana-card-modal selected" data-id="" onclick="selectModalCampana(this)">Sin campaña</div>
                            @foreach($campanas as $camp)
                                <div class="campana-card-modal" data-id="{{ $camp->id }}" onclick="selectModalCampana(this)">{{ $camp->nombre }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Sección contrato (aparece al seleccionar un paquete) --}}
                    <div id="modalContratoSection" style="display:none;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid rgba(201,169,110,0.25);background:rgba(201,169,110,0.04);padding:0.9rem;border:1px solid rgba(201,169,110,0.2);">
                        <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--accent-color);font-weight:500;margin-bottom:0.65rem;">📋 Contrato del paquete</div>
                        <input type="hidden" name="crear_contrato" id="mContrCrear" value="0">
                        <input type="hidden" name="paquete_id" id="mContrPaqueteId" value="">

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:0.5rem;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.72rem;">Fecha inicio</label>
                                <input type="date" name="contrato_fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:0.72rem;">Precio total (Bs.)</label>
                                <input type="number" name="contrato_precio_total" id="mContrPrecio" class="form-control" step="0.01" min="0" placeholder="0.00" oninput="mUpdateContrSuma()">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:0.5rem;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                                <div style="font-size:0.72rem;color:var(--text-light);">Pagos registrados</div>
                                <button type="button" class="btn btn-outline btn-sm" onclick="mAddContrPago()" style="font-size:.7rem;padding:.2rem .6rem;">+ Añadir pago</button>
                            </div>
                            <div id="mContrPagosContainer">
                                <div style="display:grid;grid-template-columns:1fr 100px 1fr auto;gap:0.35rem;align-items:center;margin-bottom:0.3rem;">
                                    <input type="number" name="contrato_pagos[0][monto]" class="form-control mContrMonto"
                                           step="0.01" min="0" placeholder="Monto (Bs.)" oninput="mUpdateContrSuma()">
                                    <input type="date" name="contrato_pagos[0][fecha_pago]" class="form-control" value="{{ date('Y-m-d') }}">
                                    <select name="contrato_pagos[0][metodo_pago]" class="form-control" style="font-size:.78rem;">
                                        <option value="">— Método —</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="qr">QR</option>
                                    </select>
                                    <span style="font-size:.68rem;color:var(--accent-color);font-weight:600;">★</span>
                                </div>
                            </div>
                            <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.25rem;">
                                Adelantado: Bs. <span id="mContrSuma" style="color:var(--text-dark);font-weight:400;">0.00</span>
                                · Pendiente: Bs. <span id="mContrTotal">0.00</span>
                            </div>
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Notas del contrato</label>
                            <textarea name="contrato_notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea>
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                        <button type="submit" class="btn btn-primary">Guardar visita</button>
                        <button type="button" class="btn btn-outline"
                                onclick="document.getElementById('modalVisita').classList.remove('open')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const MODAL_SERVICIOS = @json($modalServiciosJson);
    const MODAL_EMPLEADOS = @json($modalEmpleadosJson);
    const MODAL_PAQUETES  = @json($paquetesJson);

    // ── Pestañas Servicios / Paquetes en modal ────────────────────────────────
    let modalPkgCat      = 'todos';
    let modalPkgAplicado = null;

    function switchModalTab(tab) {
        const isServicios = tab === 'servicios';
        document.getElementById('mPanelServicios').style.display  = isServicios ? '' : 'none';
        document.getElementById('mPanelPaquetes').style.display   = isServicios ? 'none' : '';
        document.getElementById('mTabBtnServicios').classList.toggle('active', isServicios);
        document.getElementById('mTabBtnPaquetes').classList.toggle('active', !isServicios);
        if (!isServicios && document.getElementById('modalPkgCards').children.length === 0) {
            renderModalPkgCards();
        }
    }

    function modalFilterCat(cat) {
        modalPkgCat = cat;
        document.querySelectorAll('.modal-pkg-cat-btn').forEach(t => t.classList.toggle('active', t.dataset.cat === cat));
        renderModalPkgCards();
    }

    function renderModalPkgCards() {
        const wrap = document.getElementById('modalPkgCards');
        if (!wrap) return;
        wrap.innerHTML = '';
        const list = modalPkgCat === 'todos' ? MODAL_PAQUETES : MODAL_PAQUETES.filter(p => p.categoria === modalPkgCat);
        if (list.length === 0) {
            wrap.innerHTML = '<p style="font-size:.78rem;color:var(--text-light);font-style:italic;">Sin paquetes en esta categoría.</p>';
            return;
        }
        list.forEach(pkg => {
            const card = document.createElement('div');
            card.className = 'modal-pkg-card' + (pkg.id === modalPkgAplicado ? ' applied' : '');
            const badge = pkg.nivel
                ? `<span style="background:${pkg.nivel.color};color:#fff;font-size:.58rem;font-weight:700;padding:.05rem .35rem;border-radius:2px;letter-spacing:.8px;text-transform:uppercase;display:inline-block;margin-bottom:.25rem;">${pkg.nivel.nombre}</span><br>`
                : '';
            const total  = pkg.precio_total ? `Bs. ${parseFloat(pkg.precio_total).toFixed(0)}` : '';
            const sNames = pkg.servicios.map(s => s.nombre).join(' · ');
            card.innerHTML = `${badge}
                <div style="font-size:.82rem;color:var(--text-dark);font-weight:400;">${pkg.nombre}</div>
                <div style="font-size:.73rem;color:var(--accent-color);font-weight:500;">${total}</div>
                <div style="font-size:.66rem;color:var(--text-light);font-style:italic;line-height:1.3;margin-top:.15rem;">${sNames}</div>`;
            card.addEventListener('click', () => applyModalPaquete(pkg));
            wrap.appendChild(card);
        });
    }

    function applyModalPaquete(pkg) {
        scSelected.clear();
        document.querySelectorAll('.servicio-card.sel').forEach(c => c.classList.remove('sel'));

        pkg.servicios.forEach(ps => {
            const id = String(ps.servicio_id);
            scSelected.set(id, {
                nombre:    ps.nombre,
                precio:    ps.precio.toFixed(2),
                descuento: ps.descuento > 0 ? String(ps.descuento) : '',
            });
            const card = document.querySelector(`.servicio-card[data-id="${id}"]`);
            if (card) card.classList.add('sel');
        });

        modalPkgAplicado = pkg.id;
        document.getElementById('modalPkgChipText').textContent = pkg.nombre + ' · ' + pkg.servicios.length + ' servicios';
        document.getElementById('modalPkgChip').classList.add('show');
        renderModalPkgCards();
        renderPreciosPanel();
        rebuildModalProfServDropdowns();
        updateScCountBadge();
        document.getElementById('scErrorMsg').style.display = 'none';
        mShowContratoSection(pkg);
        // Cambiar a pestaña servicios para ver lo seleccionado
        switchModalTab('servicios');
    }

    function clearModalPaquete() {
        modalPkgAplicado = null;
        document.getElementById('modalPkgChip').classList.remove('show');
        renderModalPkgCards();
        mHideContratoSection();
    }

    // ── Modal: Sección contrato ───────────────────────────────────────────────
    let mContrPagoIdx = 1;

    function mShowContratoSection(pkg) {
        document.getElementById('modalContratoSection').style.display = '';
        document.getElementById('mContrCrear').value     = '1';
        document.getElementById('mContrPaqueteId').value = pkg.id;
        const precio = pkg.precio_total ? parseFloat(pkg.precio_total).toFixed(2) : '';
        document.getElementById('mContrPrecio').value = precio;
        // Reset extra pago rows (keep only first mandatory)
        const container = document.getElementById('mContrPagosContainer');
        [...container.children].slice(1).forEach(r => r.remove());
        mContrPagoIdx = 1;
        mUpdateContrSuma();
    }

    function mHideContratoSection() {
        document.getElementById('modalContratoSection').style.display = 'none';
        document.getElementById('mContrCrear').value     = '0';
        document.getElementById('mContrPaqueteId').value = '';
    }

    function mAddContrPago() {
        const idx = mContrPagoIdx++;
        const today = new Date().toISOString().split('T')[0];
        const div = document.createElement('div');
        div.style.cssText = 'display:grid;grid-template-columns:1fr 100px 1fr auto;gap:0.35rem;align-items:center;margin-bottom:0.3rem;';
        div.innerHTML = `
            <input type="number" name="contrato_pagos[${idx}][monto]" class="form-control mContrMonto"
                   step="0.01" min="0" placeholder="Monto (Bs.)" oninput="mUpdateContrSuma()">
            <input type="date" name="contrato_pagos[${idx}][fecha_pago]" class="form-control" value="${today}">
            <select name="contrato_pagos[${idx}][metodo_pago]" class="form-control" style="font-size:.78rem;">
                <option value="">— Método —</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="qr">QR</option>
            </select>
            <button type="button" class="btn-remove-linea" onclick="this.closest('div').remove();mUpdateContrSuma();" title="Quitar">✕</button>`;
        document.getElementById('mContrPagosContainer').appendChild(div);
    }

    function mUpdateContrSuma() {
        let suma = 0;
        document.querySelectorAll('.mContrMonto').forEach(inp => suma += parseFloat(inp.value) || 0);
        document.getElementById('mContrSuma').textContent = suma.toFixed(2);
        const precioVal = parseFloat(document.getElementById('mContrPrecio').value) || 0;
        const pend = Math.max(0, precioVal - suma);
        document.getElementById('mContrTotal').textContent = pend.toFixed(2);
    }

    // ── Icon map by keyword ───────────────────────────────────────────────────
    const SC_ICONS = [
        { keys: ['corte','cabello','pelo','haircut'],            icon: '✂️' },
        { keys: ['tinte','color','balayage','mechas','rubio'],   icon: '🎨' },
        { keys: ['novia','bridal','boda','quinceañ'],            icon: '👰' },
        { keys: ['manicure','manicura','uña','nail'],            icon: '💅' },
        { keys: ['pedicure','pedicura','pies','foot'],           icon: '🦶' },
        { keys: ['masaje','massage','relajante','corporal'],     icon: '🧖' },
        { keys: ['facial','limpieza','hidra','rostro','face'],   icon: '✨' },
        { keys: ['depilac','cera','laser'],                      icon: '🪷' },
        { keys: ['maquillaje','makeup','look'],                  icon: '💄' },
        { keys: ['tratamiento','keratina','proteína','repair'],  icon: '💆' },
        { keys: ['cejas','barba','bigote','brow'],               icon: '🪮' },
        { keys: ['spa','relajación','hammam','vapor'],           icon: '🛁' },
    ];

    function getServicioIcon(nombre) {
        const n = nombre.toLowerCase();
        for (const entry of SC_ICONS) {
            if (entry.keys.some(k => n.includes(k))) return entry.icon;
        }
        return '⭐';
    }

    // ── State ─────────────────────────────────────────────────────────────────
    // Map<servicio_id (string) → { nombre, precio (string) }>
    const scSelected = new Map();
    let scCatFiltro = '';

    function filterScCat(btn, cat) {
        scCatFiltro = cat;
        document.querySelectorAll('#scCatStrip .modal-pkg-cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderServicioCards();
    }

    // ── Render cards ──────────────────────────────────────────────────────────
    function renderServicioCards() {
        const grid = document.getElementById('servicioCardsGrid');
        grid.innerHTML = '';
        const lista = scCatFiltro ? MODAL_SERVICIOS.filter(s => s.categoria === scCatFiltro) : MODAL_SERVICIOS;
        lista.forEach(s => {
            const card = document.createElement('div');
            card.className = 'servicio-card' + (scSelected.has(String(s.id)) ? ' sel' : '');
            card.dataset.id       = s.id;
            card.dataset.nombre   = s.nombre;
            card.dataset.precio   = s.precio;
            card.dataset.descuento = s.descuento ?? 0;

            const desc     = parseFloat(s.descuento ?? 0);
            const precioBase = parseFloat(s.precio);
            const precioFmt  = desc > 0
                ? `<span style="text-decoration:line-through;opacity:.55;font-size:.58rem;">Bs.${precioBase.toFixed(0)}</span> Bs.${Math.round(precioBase*(1-desc/100))}`
                : `Bs. ${precioBase.toFixed(0)}`;
            const descLabel = desc > 0
                ? `<div style="font-size:.6rem;color:#fff;background:var(--success-color);padding:.05rem .3rem;border-radius:2px;margin-top:.2rem;display:inline-block;">-${desc}%</div>`
                : '';

            card.innerHTML = `
                <span class="sc-check">✓</span>
                <span class="sc-icon">${getServicioIcon(s.nombre)}</span>
                <div class="sc-name">${s.nombre}</div>
                <div class="sc-price">${precioFmt}</div>
                ${descLabel}`;
            card.addEventListener('click', () => toggleServicioCard(card));
            grid.appendChild(card);
        });
    }

    function toggleServicioCard(card) {
        const id       = String(card.dataset.id);
        const nombre   = card.dataset.nombre;
        const precio   = parseFloat(card.dataset.precio || 0).toFixed(2);
        const descuento = parseFloat(card.dataset.descuento || 0);

        if (scSelected.has(id)) {
            scSelected.delete(id);
            card.classList.remove('sel');
        } else {
            // Auto-aplica el descuento programado vigente
            scSelected.set(id, { nombre, precio, descuento: descuento > 0 ? String(descuento) : '' });
            card.classList.add('sel');
        }
        document.getElementById('scErrorMsg').style.display = 'none';
        renderPreciosPanel();
        rebuildModalProfServDropdowns();
        updateScCountBadge();
    }

    function updateScCountBadge() {
        const badge = document.getElementById('scCountBadge');
        const n = scSelected.size;
        if (n > 0) {
            badge.textContent = `${n} seleccionado${n > 1 ? 's' : ''}`;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    }

    // ── Prices panel ──────────────────────────────────────────────────────────
    function calcNeto(precio, descuento) {
        const p = parseFloat(precio) || 0;
        const d = parseFloat(descuento) || 0;
        return Math.round(p * (1 - d / 100) * 100) / 100;
    }

    function updateNetoLabel(id) {
        const row   = document.querySelector(`.sc-precio-row[data-id="${id}"]`);
        if (!row) return;
        const state = scSelected.get(id);
        const neto  = calcNeto(state.precio, state.descuento);
        const label = row.querySelector('.sc-neto');
        const hasDiscount = parseFloat(state.descuento) > 0;
        label.textContent = `Bs. ${neto.toFixed(2)}`;
        label.className   = 'sc-neto' + (hasDiscount ? ' has-desc' : '');
    }

    function renderPreciosPanel() {
        const panel     = document.getElementById('scPreciosPanel');
        const container = document.getElementById('scPreciosContainer');

        if (scSelected.size === 0) {
            panel.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        panel.style.display = 'block';
        const existing = new Set([...container.querySelectorAll('.sc-precio-row')].map(r => r.dataset.id));

        // Add rows for newly selected services
        scSelected.forEach(({ nombre, precio, descuento }, id) => {
            if (!existing.has(id)) {
                const row = document.createElement('div');
                row.className = 'sc-precio-row';
                row.dataset.id = id;
                row.innerHTML = `
                    <span class="sc-precio-name">${nombre}</span>
                    <div class="sc-precio-wrap">
                        <span class="sc-precio-prefix">Bs.</span>
                        <input type="number" class="form-control sc-precio-input"
                               data-id="${id}" step="0.01" min="0" value="${precio}" placeholder="0.00">
                    </div>
                    <div class="sc-desc-wrap">
                        <input type="number" class="form-control sc-desc-input"
                               data-id="${id}" step="1" min="0" max="100" value="${descuento || ''}" placeholder="0">
                        <span class="sc-desc-suffix">%</span>
                    </div>
                    <span class="sc-neto">Bs. ${parseFloat(precio).toFixed(2)}</span>`;

                row.querySelector('.sc-precio-input').addEventListener('input', e => {
                    scSelected.get(id).precio = e.target.value;
                    updateNetoLabel(id);
                });
                row.querySelector('.sc-desc-input').addEventListener('input', e => {
                    scSelected.get(id).descuento = e.target.value;
                    updateNetoLabel(id);
                });
                container.appendChild(row);
                updateNetoLabel(id);
            }
        });

        // Remove rows for deselected services
        container.querySelectorAll('.sc-precio-row').forEach(row => {
            if (!scSelected.has(row.dataset.id)) row.remove();
        });
    }

    // ── Hidden inputs (injected on submit) ────────────────────────────────────
    function injectHiddenInputs() {
        const wrap = document.getElementById('scHiddenInputs');
        wrap.innerHTML = '';
        let i = 0;
        scSelected.forEach(({ precio, descuento }, id) => {
            const d = parseFloat(descuento) || 0;
            wrap.innerHTML += `
                <input type="hidden" name="servicios[${i}][servicio_id]" value="${id}">
                <input type="hidden" name="servicios[${i}][precio]" value="${precio || 0}">
                <input type="hidden" name="servicios[${i}][descuento]" value="${d}">`;
            i++;
        });
    }

    // ── Form submit guard ─────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        renderServicioCards();
        renderModalPkgCards();

        document.querySelector('#modalVisita form').addEventListener('submit', function(e) {
            if (scSelected.size === 0) {
                e.preventDefault();
                document.getElementById('scErrorMsg').style.display = 'block';
                document.getElementById('servicioCardsGrid').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            injectHiddenInputs();
        });
    });

    // ── Profesionales ─────────────────────────────────────────────────────────
    let modalProfIdx = 0;

    function buildModalEmpleadoOptions() {
        return '<option value="">— Seleccionar —</option>' +
            MODAL_EMPLEADOS.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
    }

    function buildModalProfServicioOptions() {
        if (scSelected.size === 0) return '<option value="">— Elige servicios primero —</option>';
        return '<option value="">— Qué realizó —</option>' +
            [...scSelected.entries()].map(([id, { nombre }]) =>
                `<option value="${id}">${nombre}</option>`).join('');
    }

    function rebuildModalProfServDropdowns() {
        document.querySelectorAll('.modal-select-prof-serv').forEach(sel => {
            const current = sel.value;
            sel.innerHTML = '<option value="">— Qué realizó —</option>' +
                [...scSelected.entries()].map(([id, { nombre }]) =>
                    `<option value="${id}"${id == current ? ' selected' : ''}>${nombre}</option>`).join('');
        });
    }

    function addModalProfesional() {
        const idx = modalProfIdx++;
        document.getElementById('modalProfHeadersRow').style.display = 'grid';
        const div = document.createElement('div');
        div.className = 'linea-prof-modal';
        div.dataset.index = idx;
        div.innerHTML = `
            <select name="profesionales[${idx}][empleado_id]" class="form-control">
                ${buildModalEmpleadoOptions()}
            </select>
            <select name="profesionales[${idx}][servicio_id]" class="form-control modal-select-prof-serv">
                ${buildModalProfServicioOptions()}
            </select>
            <button type="button" class="btn-remove-linea-modal" onclick="removeModalProfesional(this)" title="Quitar">✕</button>`;
        document.getElementById('modalProfesionalesContainer').appendChild(div);
    }

    function removeModalProfesional(btn) {
        btn.closest('.linea-prof-modal').remove();
        if (document.querySelectorAll('.linea-prof-modal').length === 0) {
            document.getElementById('modalProfHeadersRow').style.display = 'none';
        }
    }

    // ── Campaña ───────────────────────────────────────────────────────────────
    function selectModalCampana(card) {
        document.querySelectorAll('.campana-card-modal').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('modalCampanaId').value = card.dataset.id;
    }

    // ── Enlace de recomendación ───────────────────────────────────────────────
    const RECO_GENERAR_URL = '{{ route('admin.clientes.recomendacion.generar', $cliente) }}';

    function generarEnlaceRecomendacion() {
        const modal   = document.getElementById('modalRecomendacion');
        const loading = document.getElementById('recoModalLoading');
        const content = document.getElementById('recoModalContent');

        modal.classList.add('open');
        loading.style.display = '';
        content.style.display = 'none';

        fetch(RECO_GENERAR_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('recoModalUrl').textContent = data.url;
            document.getElementById('recoWhatsappBtn').href = data.whatsapp_url;

            const stats = document.getElementById('recoModalStats');
            stats.innerHTML = `
                <div class="reco-modal-stat">
                    <span class="reco-modal-stat-val">${data.clicks}</span>
                    <span class="reco-modal-stat-lbl">Aperturas</span>
                </div>
                <div class="reco-modal-stat">
                    <span class="reco-modal-stat-val">${data.convertidos}</span>
                    <span class="reco-modal-stat-lbl">Registros</span>
                </div>
            `;

            loading.style.display = 'none';
            content.style.display = '';
        })
        .catch(() => {
            loading.textContent = 'No se pudo generar el enlace. Intenta de nuevo.';
        });
    }

    function copiarEnlace() {
        const url = document.getElementById('recoModalUrl').textContent;
        navigator.clipboard.writeText(url).then(() => {
            const btn = event.target;
            const orig = btn.textContent;
            btn.textContent = '✓ Copiado';
            btn.style.color = 'var(--success-color)';
            setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 2000);
        });
    }

    // ── Cambio de estado inline ───────────────────────────────────────────────
    function toggleEstadoMenu(pill) {
        const citaId = pill.dataset.citaId;
        const menu   = document.getElementById('emenu-' + citaId);
        const isOpen = menu.classList.contains('open');
        // Cierra todos los demás
        document.querySelectorAll('.estado-menu.open').forEach(m => m.classList.remove('open'));
        if (!isOpen) menu.classList.add('open');
    }

    // Cierra menú al clickear fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.estado-pill') && !e.target.closest('.estado-menu')) {
            document.querySelectorAll('.estado-menu.open').forEach(m => m.classList.remove('open'));
        }
    });

    function cambiarEstado(citaId, nuevoEstado, url) {
        const menu = document.getElementById('emenu-' + citaId);
        const pill = document.querySelector(`.estado-pill[data-cita-id="${citaId}"]`);

        // No hacer nada si es el estado actual
        if (pill.classList.contains(nuevoEstado)) { menu.classList.remove('open'); return; }

        pill.classList.add('loading');
        menu.classList.remove('open');

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                              || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ estado: nuevoEstado }),
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(data => {
            // Actualiza la pill
            pill.classList.remove('completada','confirmada','pendiente','cancelada','loading');
            pill.classList.add(data.estado);
            pill.childNodes[0].textContent = data.estado;

            // Actualiza los items del menú
            menu.querySelectorAll('.estado-menu-item').forEach(item => {
                item.classList.toggle('current', item.dataset.estado === data.estado);
            });

            // Flash sutil en la fila
            const row = document.getElementById('row-cita-' + citaId);
            row.style.transition = 'background 0s';
            row.style.background = 'rgba(201,169,110,0.08)';
            setTimeout(() => { row.style.transition = 'background 0.6s'; row.style.background = ''; }, 50);
        })
        .catch(() => {
            pill.classList.remove('loading');
            alert('No se pudo actualizar el estado. Intenta de nuevo.');
        });
    }
</script>
@endpush
