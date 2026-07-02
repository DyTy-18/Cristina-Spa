@extends('admin.layouts.app')

@section('title', 'Nueva Cita')
@section('page-title', 'Nueva Cita')

@push('styles')
<style>
    /* ===== Toggle cliente ===== */
    .cliente-toggle {
        display: flex;
        border: 1px solid rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        width: fit-content;
    }
    .toggle-btn {
        padding: 0.55rem 1.4rem;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.78rem;
        font-weight: 300;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        background: var(--white);
        border: none;
        cursor: pointer;
        color: var(--text-light);
        transition: var(--transition);
        border-right: 1px solid rgba(0,0,0,0.1);
    }
    .toggle-btn:last-child { border-right: none; }
    .toggle-btn.active {
        background: var(--primary-color);
        color: var(--white);
    }
    .toggle-btn:not(.active):hover {
        background: var(--light-bg);
        color: var(--text-dark);
    }

    /* ===== Buscador de cliente ===== */
    .search-cliente-wrapper {
        position: relative;
    }
    .search-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        right: 0;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.12);
        z-index: 200;
        max-height: 240px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .search-dropdown.visible { display: block; }
    .search-option {
        padding: 0.75rem 1rem;
        cursor: pointer;
        font-size: 0.88rem;
        font-weight: 300;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.15s;
    }
    .search-option:hover { background: var(--light-bg); }
    .search-option .opt-name { color: var(--text-dark); font-weight: 400; }
    .search-option .opt-phone { color: var(--text-light); font-size: 0.78rem; }
    .search-option-empty {
        padding: 1rem;
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 300;
        font-style: italic;
        text-align: center;
    }
    .search-selected-badge {
        display: none;
        align-items: center;
        gap: 0.6rem;
        margin-top: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: rgba(201,169,110,0.12);
        border: 1px solid rgba(201,169,110,0.3);
        font-size: 0.82rem;
        font-weight: 300;
    }
    .search-selected-badge.visible { display: flex; }
    .badge-clear {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-light);
        font-size: 0.9rem;
        margin-left: auto;
        padding: 0;
    }
    .badge-clear:hover { color: var(--error-color); }

    /* ===== Líneas de servicio ===== */
    .linea-servicio {
        display: grid;
        grid-template-columns: 1fr 100px 78px auto;
        gap: 0.75rem;
        align-items: end;
        padding: 0.75rem;
        background: var(--light-bg);
        border: 1px solid rgba(0,0,0,0.06);
        margin-bottom: 0.6rem;
    }
    .linea-desc-wrap { position: relative; }
    .linea-desc-suffix {
        position: absolute;
        right: 0.55rem; top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .linea-desc-input { padding-right: 1.6rem !important; }

    /* ===== Líneas de profesional ===== */
    .linea-profesional {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
        padding: 0.75rem;
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.07);
        margin-bottom: 0.5rem;
    }
    .prof-section-hint {
        font-size: 0.78rem;
        color: var(--text-light);
        font-weight: 300;
        margin-bottom: 0.75rem;
        font-style: italic;
    }

    .linea-precio-wrap { position: relative; }
    .linea-precio-prefix {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        color: var(--text-light);
        pointer-events: none;
    }
    .linea-precio-input { padding-left: 2rem !important; }
    .btn-remove-linea {
        background: none;
        border: 1px solid rgba(0,0,0,0.12);
        color: var(--text-light);
        cursor: pointer;
        padding: 0.45rem 0.6rem;
        font-size: 0.9rem;
        transition: var(--transition);
        line-height: 1;
        height: 38px;
    }
    .btn-remove-linea:hover { color: var(--error-color); border-color: var(--error-color); }

    /* ===== Pestañas Servicios / Paquetes ===== */
    .srv-tab-strip {
        display: flex;
        align-items: stretch;
        border-bottom: 1px solid rgba(0,0,0,.08);
        background: var(--light-bg);
    }
    .srv-tab {
        padding: .65rem 1.5rem;
        font-size: .75rem;
        letter-spacing: .8px;
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
    .srv-tab:hover:not(.active) { color: var(--text-dark); background: rgba(0,0,0,.03); }
    .srv-tab.active { color: var(--primary-color); border-bottom-color: var(--accent-color); background: var(--white); font-weight: 500; }

    .pkg-applied-chip {
        display: none;
        align-items: center;
        gap: .4rem;
        margin-left: auto;
        padding: 0 1.25rem;
        font-size: .75rem;
        color: #166534;
        background: rgba(34,197,94,.06);
        border-left: 1px solid rgba(0,0,0,.06);
    }
    .pkg-applied-chip.show { display: flex; }

    /* Package cards panel */
    .pkg-cat-strip { display: flex; gap: 0; margin-bottom: 1rem; border: 1px solid rgba(0,0,0,.09); width: fit-content; flex-wrap: wrap; }
    .pkg-cat-btn {
        padding: .35rem 1rem;
        font-size: .7rem;
        letter-spacing: .6px;
        text-transform: uppercase;
        cursor: pointer;
        background: var(--white);
        border: none;
        border-right: 1px solid rgba(0,0,0,.09);
        color: var(--text-light);
        transition: var(--transition);
    }
    .pkg-cat-btn:last-child { border-right: none; }
    .pkg-cat-btn.active { background: var(--primary-color); color: #fff; }
    .pkg-cat-btn:not(.active):hover { background: var(--light-bg); }

    .pkg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(175px, 1fr)); gap: .75rem; }
    .pkg-card {
        border: 1px solid rgba(0,0,0,.08);
        padding: .9rem 1rem;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        gap: .2rem;
    }
    .pkg-card:hover { border-color: var(--accent-color); box-shadow: 0 2px 10px rgba(0,0,0,.07); }
    .pkg-card.applied { border-color: #22c55e; background: rgba(34,197,94,.04); }
    .pkg-nivel-badge { font-size: .58rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #fff; padding: .06rem .4rem; border-radius: 2px; display: inline-block; width: fit-content; }
    .pkg-card-nombre { font-size: .85rem; color: var(--text-dark); font-weight: 400; margin-top: .1rem; }
    .pkg-card-precio { font-size: .75rem; color: var(--accent-color); font-weight: 500; }
    .pkg-card-svcs { font-size: .68rem; color: var(--text-light); font-style: italic; line-height: 1.4; }

    /* ===== Sección cards ===== */
    .form-section {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 1.25rem;
    }
    .form-section-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .form-section-num {
        width: 24px;
        height: 24px;
        background: var(--primary-color);
        color: var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 400;
        flex-shrink: 0;
    }
    .form-section-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.15rem;
        color: var(--primary-color);
        font-weight: 400;
        letter-spacing: 1px;
    }
    .form-section-body {
        padding: 1.5rem;
    }

    /* ===== Layout ===== */
    .create-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.25rem;
        align-items: start;
    }
    .create-main { min-width: 0; }
    .create-aside { position: sticky; top: calc(var(--header-height) + 1rem); }

    @media (max-width: 900px) {
        .create-layout { grid-template-columns: 1fr; }
        .create-aside { position: static; }
    }

    /* ===== Sección contrato ===== */
    .contrato-section-inner {
        background: rgba(201,169,110,0.04);
        border: 1px solid rgba(201,169,110,0.2);
        padding: 1.1rem 1.25rem;
        margin-top: 0.1rem;
    }
    .contrato-section-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--accent-color);
        font-weight: 500;
        margin-bottom: 0.75rem;
    }

    /* ===== Selector de campaña ===== */
    .campana-card {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.65rem 0.9rem;
        border: 1px solid rgba(0,0,0,0.1);
        cursor: pointer;
        transition: var(--transition);
        background: var(--white);
        position: relative;
    }
    .campana-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
    .campana-card:hover { border-color: var(--accent-color); background: var(--light-bg); }
    .campana-card.selected { border-color: var(--primary-color); background: rgba(201,169,110,0.08); }
    .campana-card-name { font-size: 0.85rem; color: var(--text-dark); font-weight: 400; }
    .campana-card-date { font-size: 0.72rem; color: var(--text-light); font-weight: 300; }
</style>
@endpush

@section('content')

    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;">
        <a href="{{ route('admin.citas.calendario') }}" class="btn btn-sm btn-outline">← Calendario</a>
        <a href="{{ route('admin.citas.index') }}" class="btn btn-sm btn-outline">Ver lista</a>
    </div>

    <form action="{{ route('admin.citas.store') }}" method="POST" id="formCita">
        @csrf
        <input type="hidden" name="cliente_tipo" id="clienteTipo" value="{{ old('cliente_tipo', 'existente') }}">

        <div class="create-layout">

            {{-- ====== COLUMNA PRINCIPAL ====== --}}
            <div class="create-main">

                {{-- SECCIÓN 1: Cliente --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">1</div>
                        <span class="form-section-title">Cliente</span>
                    </div>
                    <div class="form-section-body">

                        <div class="cliente-toggle">
                            <button type="button" class="toggle-btn {{ old('cliente_tipo','existente') === 'existente' ? 'active' : '' }}"
                                    id="btnExistente" onclick="switchCliente('existente')">
                                Cliente registrado
                            </button>
                            <button type="button" class="toggle-btn {{ old('cliente_tipo') === 'nuevo' ? 'active' : '' }}"
                                    id="btnNuevo" onclick="switchCliente('nuevo')">
                                + Nuevo cliente
                            </button>
                        </div>

                        {{-- Buscador --}}
                        <div id="sectionExistente" style="{{ old('cliente_tipo') === 'nuevo' ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label class="form-label">Buscar cliente <span style="color:var(--error-color)">*</span></label>
                                <div class="search-cliente-wrapper">
                                    <input type="text" id="searchInput" class="form-control"
                                           placeholder="Nombre, apellido o teléfono..."
                                           autocomplete="off" value="{{ old('_cliente_display', '') }}">
                                    <input type="hidden" name="cliente_id" id="clienteIdInput"
                                           value="{{ old('cliente_id') }}">
                                    <div id="searchDropdown" class="search-dropdown"></div>
                                </div>
                                <div class="search-selected-badge {{ old('cliente_id') ? 'visible' : '' }}" id="selectedBadge">
                                    <span id="selectedName">{{ old('_cliente_display') }}</span>
                                    <button type="button" class="badge-clear" onclick="clearCliente()" title="Cambiar">✕</button>
                                </div>
                                @error('cliente_id')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Nuevo cliente --}}
                        <div id="sectionNuevo" style="{{ old('cliente_tipo') !== 'nuevo' ? 'display:none' : '' }}">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nombre <span style="color:var(--error-color)">*</span></label>
                                    <input type="text" name="nuevo_nombre" class="form-control"
                                           value="{{ old('nuevo_nombre') }}" placeholder="Nombre">
                                    @error('nuevo_nombre')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Apellido</label>
                                    <input type="text" name="nuevo_apellido" class="form-control"
                                           value="{{ old('nuevo_apellido') }}" placeholder="Apellido">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Teléfono <span style="color:var(--error-color)">*</span></label>
                                    <input type="text" name="nuevo_telefono" class="form-control"
                                           value="{{ old('nuevo_telefono') }}" placeholder="70000000">
                                    @error('nuevo_telefono')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="nuevo_email" class="form-control"
                                           value="{{ old('nuevo_email') }}" placeholder="correo@ejemplo.com">
                                    @error('nuevo_email')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- SECCIÓN 2: Servicios / Paquetes --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">2</div>
                        <span class="form-section-title">Servicios y profesionales</span>
                    </div>

                    {{-- Tab strip --}}
                    <div class="srv-tab-strip">
                        <button type="button" class="srv-tab active" id="tabBtnServicios"
                            onclick="switchSrvTab('servicios')">✂️ Servicios</button>
                        <button type="button" class="srv-tab" id="tabBtnPaquetes"
                            onclick="switchSrvTab('paquetes')">🎁 Paquetes</button>
                        <div class="pkg-applied-chip" id="pkgAppliedChip">
                            <span>✓</span>
                            <span id="pkgAppliedName"></span>
                            <button type="button" onclick="clearPaquete()"
                                style="background:none;border:none;cursor:pointer;color:#166534;font-size:.9rem;padding:0;"
                                title="Quitar paquete">✕</button>
                        </div>
                    </div>

                    {{-- Panel: Servicios individuales --}}
                    <div class="form-section-body" id="srvPanelServicios">
                        @error('lineas')
                            <span style="color:var(--error-color);font-size:0.78rem;display:block;margin-bottom:0.5rem;">{{ $message }}</span>
                        @enderror

                        {{-- Filtro por categoría --}}
                        <div class="pkg-cat-strip" id="srvCatStrip" style="margin-bottom:0.75rem;">
                            <button type="button" class="pkg-cat-btn active" data-cat="" onclick="filterSrvCat(this, '')">Todas</button>
                            <button type="button" class="pkg-cat-btn" data-cat="peluqueria" onclick="filterSrvCat(this, 'peluqueria')">Peluquería</button>
                            <button type="button" class="pkg-cat-btn" data-cat="peinados" onclick="filterSrvCat(this, 'peinados')">Peinados</button>
                            <button type="button" class="pkg-cat-btn" data-cat="coloracion" onclick="filterSrvCat(this, 'coloracion')">Coloración</button>
                            <button type="button" class="pkg-cat-btn" data-cat="alisado" onclick="filterSrvCat(this, 'alisado')">Alisado</button>
                            <button type="button" class="pkg-cat-btn" data-cat="depilacion" onclick="filterSrvCat(this, 'depilacion')">Depilación</button>
                            <button type="button" class="pkg-cat-btn" data-cat="maquillaje" onclick="filterSrvCat(this, 'maquillaje')">Maquillaje</button>
                            <button type="button" class="pkg-cat-btn" data-cat="pies_manos" onclick="filterSrvCat(this, 'pies_manos')">Pies y Manos</button>
                            <button type="button" class="pkg-cat-btn" data-cat="extensiones" onclick="filterSrvCat(this, 'extensiones')">Extensiones</button>
                            <button type="button" class="pkg-cat-btn" data-cat="spa" onclick="filterSrvCat(this, 'spa')">Spa</button>
                            <button type="button" class="pkg-cat-btn" data-cat="tratamientos" onclick="filterSrvCat(this, 'tratamientos')">Tratamientos</button>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 100px 78px auto;gap:0.5rem;margin-bottom:0.4rem;padding:0 0.75rem;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-light);">Servicio</span>
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-light);">Precio (Bs.)</span>
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-light);">Desc.</span>
                            <span></span>
                        </div>

                        <div id="serviciosContainer">
                            <div class="linea-servicio" data-index="0">
                                <div class="form-group" style="margin:0;">
                                    <select name="servicios[0][servicio_id]" class="form-control select-servicio" required
                                            onchange="onServicioChange(this)">
                                        <option value="">— Seleccionar —</option>
                                        @foreach($servicios as $s)
                                            <option value="{{ $s->id }}" data-precio="{{ $s->precio }}" data-duracion="{{ $s->duracion_minutos }}">
                                                {{ $s->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group linea-precio-wrap" style="margin:0;">
                                    <span class="linea-precio-prefix">Bs.</span>
                                    <input type="number" name="servicios[0][precio]" class="form-control linea-precio-input"
                                           step="0.01" min="0" placeholder="0.00" oninput="updateResume()">
                                </div>
                                <div class="form-group linea-desc-wrap" style="margin:0;">
                                    <input type="number" name="servicios[0][descuento]" class="form-control linea-desc-input"
                                           step="1" min="0" max="100" placeholder="0" oninput="updateResume()">
                                    <span class="linea-desc-suffix">%</span>
                                </div>
                                <button type="button" class="btn-remove-linea" onclick="removeServicio(this)" title="Quitar">✕</button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline btn-sm" onclick="addServicio()" style="margin-top:0.25rem;">
                            + Agregar servicio
                        </button>
                    </div>

                    {{-- Panel: Paquetes --}}
                    <div class="form-section-body" id="srvPanelPaquetes" style="display:none;">
                        <div class="pkg-cat-strip">
                            <button type="button" class="pkg-cat-btn active" data-cat="todos" onclick="filterPkgCat('todos')">Todos</button>
                            <button type="button" class="pkg-cat-btn" data-cat="novia" onclick="filterPkgCat('novia')">👰 Novia</button>
                            <button type="button" class="pkg-cat-btn" data-cat="quinceañera" onclick="filterPkgCat('quinceañera')">🌸 Quinceañera</button>
                            <button type="button" class="pkg-cat-btn" data-cat="eventos" onclick="filterPkgCat('eventos')">🎉 Eventos</button>
                        </div>
                        <div class="pkg-grid" id="pkgCardsWrap"></div>
                        <p style="font-size:.75rem;color:var(--text-light);margin-top:.75rem;font-style:italic;">
                            Al seleccionar un paquete se pre-rellenan los servicios. Puedes modificarlos después.
                        </p>
                    </div>
                </div>

                {{-- SECCIÓN 3: Profesionales --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">3</div>
                        <span class="form-section-title">Profesionales</span>
                    </div>
                    <div class="form-section-body">
                        <p class="prof-section-hint">Opcional — asigna qué profesional realiza cada servicio.</p>

                        <div id="profHeadersRow" style="display:none;grid-template-columns:1fr 1fr auto;gap:0.5rem;margin-bottom:0.4rem;padding:0 0.75rem;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-light);">Profesional</span>
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-light);">Realizó</span>
                            <span></span>
                        </div>

                        <div id="profesionalesContainer"></div>

                        <button type="button" class="btn btn-outline btn-sm" onclick="addProfesional()" style="margin-top:0.25rem;">
                            + Agregar profesional
                        </button>
                    </div>
                </div>

                {{-- SECCIÓN 4: Horario y detalles --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">4</div>
                        <span class="form-section-title">Horario y detalles</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Fecha <span style="color:var(--error-color)">*</span></label>
                                <input type="date" name="fecha" class="form-control"
                                       value="{{ old('fecha', date('Y-m-d')) }}" required>
                                @error('fecha')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora <span style="color:var(--error-color)">*</span></label>
                                <input type="time" name="hora" class="form-control"
                                       value="{{ old('hora', '09:00') }}" required>
                                @error('hora')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
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
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-control">
                                @foreach(['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('estado', 'pendiente') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de pago</label>
                            <select name="tipo_pago" class="form-control">
                                <option value="">— Sin especificar —</option>
                                <option value="efectivo" {{ old('tipo_pago') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="tarjeta"  {{ old('tipo_pago') === 'tarjeta'  ? 'selected' : '' }}>Tarjeta</option>
                                <option value="qr"       {{ old('tipo_pago') === 'qr'       ? 'selected' : '' }}>QR</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control" rows="2"
                                      placeholder="Preferencias del cliente, observaciones...">{{ old('notas') }}</textarea>
                        </div>

                        @if($campanas->isNotEmpty())
                        <div class="form-group" style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(0,0,0,0.05);">
                            <label class="form-label">Campaña</label>
                            <p style="font-size:0.78rem;color:var(--text-light);font-weight:300;margin-bottom:0.75rem;font-style:italic;">
                                Opcional — vincula esta visita a una campaña activa.
                            </p>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.5rem;">
                                <label class="campana-card {{ old('campana_id') === null ? 'selected' : '' }}" for="campana_ninguna">
                                    <input type="radio" name="campana_id" id="campana_ninguna" value=""
                                           {{ old('campana_id') === null ? 'checked' : '' }}>
                                    <span class="campana-card-name" style="color:var(--text-light);font-style:italic;">Sin campaña</span>
                                </label>
                                @foreach($campanas as $camp)
                                    <label class="campana-card {{ old('campana_id') == $camp->id ? 'selected' : '' }}"
                                           for="campana_{{ $camp->id }}">
                                        <input type="radio" name="campana_id" id="campana_{{ $camp->id }}"
                                               value="{{ $camp->id }}"
                                               {{ old('campana_id') == $camp->id ? 'checked' : '' }}>
                                        <span class="campana-card-name">{{ $camp->nombre }}</span>
                                        @if($camp->fecha_fin)
                                            <span class="campana-card-date">hasta {{ $camp->fecha_fin->format('d/m/Y') }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- SECCIÓN 5: Contrato de paquete (aparece al aplicar un paquete) --}}
                <div class="form-section" id="contratoSection" style="display:none;">
                    <div class="form-section-header">
                        <div class="form-section-num">5</div>
                        <span class="form-section-title">Contrato del paquete</span>
                    </div>
                    <div class="form-section-body" style="padding:0;">
                        <div class="contrato-section-inner">
                            <div class="contrato-section-label">📋 Datos del contrato</div>
                            <input type="hidden" name="crear_contrato" id="contrCrear" value="0">
                            <input type="hidden" name="paquete_id" id="contrPaqueteId" value="">

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Fecha de inicio</label>
                                    <input type="date" name="contrato_fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Precio total (Bs.)</label>
                                    <input type="number" name="contrato_precio_total" id="contrPrecioTotal" class="form-control"
                                           step="0.01" min="0" placeholder="0.00" oninput="contrUpdateSuma()">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:0.5rem;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.35rem;">
                                    <label class="form-label" style="margin:0;">Pagos registrados</label>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="contrAddPago()">+ Añadir pago</button>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 120px 1fr auto;gap:0.4rem;margin-bottom:0.25rem;">
                                    <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-light);">Monto (Bs.)</span>
                                    <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-light);">Fecha</span>
                                    <span style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-light);">Método</span>
                                    <span></span>
                                </div>
                                <div id="contrPagosContainer">
                                    <div style="display:grid;grid-template-columns:1fr 120px 1fr auto;gap:0.4rem;align-items:center;margin-bottom:0.35rem;">
                                        <div class="form-group linea-precio-wrap" style="margin:0;">
                                            <span class="linea-precio-prefix">Bs.</span>
                                            <input type="number" name="contrato_pagos[0][monto]" class="form-control linea-precio-input contr-monto"
                                                   step="0.01" min="0" placeholder="0.00" oninput="contrUpdateSuma()">
                                        </div>
                                        <input type="date" name="contrato_pagos[0][fecha_pago]" class="form-control" value="{{ date('Y-m-d') }}">
                                        <select name="contrato_pagos[0][metodo_pago]" class="form-control" style="font-size:.8rem;">
                                            <option value="">— Método —</option>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="qr">QR</option>
                                        </select>
                                        <span style="font-size:.7rem;color:var(--accent-color);font-weight:600;">★</span>
                                    </div>
                                </div>
                                <div style="font-size:0.78rem;color:var(--text-light);margin-top:0.3rem;">
                                    Adelantado: Bs. <span id="contrSumaCuotas" style="color:var(--text-dark);font-weight:400;">0.00</span>
                                    · Pendiente: Bs. <span id="contrTotalRef">0.00</span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Notas del contrato</label>
                                <textarea name="contrato_notas" class="form-control" rows="2"
                                          placeholder="Observaciones sobre el contrato..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ====== COLUMNA LATERAL: resumen ====== --}}
            <div class="create-aside">
                <div class="form-section">
                    <div class="form-section-header">
                        <span class="form-section-title">Resumen</span>
                    </div>
                    <div class="form-section-body" style="padding:1.25rem 1.5rem;">

                        <div id="resumeCliente" style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(0,0,0,0.05);">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Cliente</div>
                            <div id="resumeClienteName" style="font-size:0.9rem;color:var(--text-light);font-style:italic;">Sin seleccionar</div>
                        </div>

                        <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(0,0,0,0.05);">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Servicios</div>
                            <div id="resumeServicios" style="font-size:0.9rem;color:var(--text-light);font-style:italic;">Sin seleccionar</div>
                        </div>

                        <div style="margin-bottom:1.5rem;">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Total estimado</div>
                            <div id="resumeAhorro" style="font-size:0.78rem;color:var(--success-color);margin-bottom:0.15rem;display:none;"></div>
                            <div id="resumePrecio" style="font-family:'Cormorant Garamond',serif;font-size:2rem;color:var(--secondary-color);line-height:1;">—</div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;text-align:center;justify-content:center;">
                            Guardar cita
                        </button>
                        <a href="{{ route('admin.citas.calendario') }}"
                           class="btn btn-outline btn-sm"
                           style="width:100%;text-align:center;display:block;margin-top:0.75rem;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>

@endsection

@push('scripts')
<script>
    const CLIENTES  = @json($clientesJson);
    const SERVICIOS = @json($serviciosJson);
    const EMPLEADOS = @json($empleadosJson);
    const PAQUETES  = @json($paquetesJson);

    // ===== Pestañas Servicios / Paquetes =====
    let pkgCatActual  = 'todos';
    let pkgAplicadoId = null;

    function switchSrvTab(tab) {
        const isServicios = tab === 'servicios';
        document.getElementById('srvPanelServicios').style.display = isServicios ? '' : 'none';
        document.getElementById('srvPanelPaquetes').style.display  = isServicios ? 'none' : '';
        document.getElementById('tabBtnServicios').classList.toggle('active', isServicios);
        document.getElementById('tabBtnPaquetes').classList.toggle('active', !isServicios);
        if (!isServicios && document.getElementById('pkgCardsWrap').children.length === 0) {
            renderPkgCards();
        }
    }

    function filterPkgCat(cat) {
        pkgCatActual = cat;
        document.querySelectorAll('.pkg-cat-btn').forEach(t => t.classList.toggle('active', t.dataset.cat === cat));
        renderPkgCards();
    }

    function renderPkgCards() {
        const wrap = document.getElementById('pkgCardsWrap');
        wrap.innerHTML = '';
        const filtered = pkgCatActual === 'todos' ? PAQUETES : PAQUETES.filter(p => p.categoria === pkgCatActual);

        if (filtered.length === 0) {
            wrap.innerHTML = '<p style="font-size:.8rem;color:var(--text-light);font-style:italic;">Sin paquetes en esta categoría.</p>';
            return;
        }

        filtered.forEach(pkg => {
            const card = document.createElement('div');
            card.className = 'pkg-card' + (pkg.id === pkgAplicadoId ? ' applied' : '');
            const nivelBadge = pkg.nivel
                ? `<span class="pkg-nivel-badge" style="background:${pkg.nivel.color}">${pkg.nivel.nombre}</span>`
                : '';
            const total  = pkg.precio_total ? `Bs. ${parseFloat(pkg.precio_total).toFixed(0)}` : '';
            const sNames = pkg.servicios.map(s => s.nombre).join(' · ');
            card.innerHTML = `${nivelBadge}
                <div class="pkg-card-nombre">${pkg.nombre}</div>
                <div class="pkg-card-precio">${total}</div>
                <div class="pkg-card-svcs">${sNames}</div>`;
            card.addEventListener('click', () => applyPaquete(pkg));
            wrap.appendChild(card);
        });
    }

    function applyPaquete(pkg) {
        const container = document.getElementById('serviciosContainer');
        container.innerHTML = '';
        servicioIdx = 0;

        pkg.servicios.forEach(s => {
            const idx = String(servicioIdx++);
            const div = document.createElement('div');
            div.className = 'linea-servicio';
            div.dataset.index = idx;
            div.innerHTML = `
                <div class="form-group" style="margin:0;">
                    <select name="servicios[${idx}][servicio_id]" class="form-control select-servicio" required
                            onchange="onServicioChange(this)">
                        ${buildServicioOptions(s.servicio_id)}
                    </select>
                </div>
                <div class="form-group linea-precio-wrap" style="margin:0;">
                    <span class="linea-precio-prefix">Bs.</span>
                    <input type="number" name="servicios[${idx}][precio]" class="form-control linea-precio-input"
                           step="0.01" min="0" placeholder="0.00" value="${s.precio.toFixed(2)}" oninput="updateResume()">
                </div>
                <div class="form-group linea-desc-wrap" style="margin:0;">
                    <input type="number" name="servicios[${idx}][descuento]" class="form-control linea-desc-input"
                           step="1" min="0" max="100" placeholder="0" value="${s.descuento > 0 ? s.descuento : ''}"
                           oninput="updateResume()"
                           style="${s.descuento > 0 ? 'background:rgba(34,197,94,0.08);' : ''}">
                    <span class="linea-desc-suffix">%</span>
                </div>
                <button type="button" class="btn-remove-linea" onclick="removeServicio(this)" title="Quitar">✕</button>`;
            container.appendChild(div);
            serviciosSeleccionados.set(idx, { servicio_id: String(s.servicio_id), nombre: s.nombre });
        });

        pkgAplicadoId = pkg.id;
        document.getElementById('pkgAppliedName').textContent = pkg.nombre + ' · ' + pkg.servicios.length + ' servicios';
        document.getElementById('pkgAppliedChip').classList.add('show');
        contrShowSection(pkg);
        renderPkgCards();
        rebuildProfServDropdowns();
        updateResume();
        // Cambiar a la pestaña de servicios para ver lo que se aplicó
        switchSrvTab('servicios');
    }

    function clearPaquete() {
        pkgAplicadoId = null;
        document.getElementById('pkgAppliedChip').classList.remove('show');
        renderPkgCards();
        contrHideSection();
    }

    let servicioIdx = 1;
    let profIdx     = 0;

    // Map: rowIndex → {servicio_id, nombre} de los servicios seleccionados
    const serviciosSeleccionados = new Map();

    // ===== Toggle cliente =====
    function switchCliente(tipo) {
        document.getElementById('clienteTipo').value = tipo;
        document.getElementById('btnExistente').classList.toggle('active', tipo === 'existente');
        document.getElementById('btnNuevo').classList.toggle('active', tipo === 'nuevo');
        document.getElementById('sectionExistente').style.display = tipo === 'existente' ? '' : 'none';
        document.getElementById('sectionNuevo').style.display    = tipo === 'nuevo'      ? '' : 'none';
        updateResume();
    }

    // ===== Buscador de cliente =====
    const searchInput   = document.getElementById('searchInput');
    const clienteId     = document.getElementById('clienteIdInput');
    const dropdown      = document.getElementById('searchDropdown');
    const selectedBadge = document.getElementById('selectedBadge');
    const selectedName  = document.getElementById('selectedName');

    function renderDropdown(term) {
        const q = term.toLowerCase().trim();
        if (q.length < 1) { dropdown.classList.remove('visible'); return; }
        const matches = CLIENTES.filter(c => c.nombre.toLowerCase().includes(q) || c.tel.includes(q)).slice(0, 12);
        if (matches.length === 0) {
            dropdown.innerHTML = `<div class="search-option-empty">No se encontró ningún cliente con "${term}"</div>`;
        } else {
            dropdown.innerHTML = matches.map(c => `
                <div class="search-option" onclick="selectCliente(${c.id}, '${c.nombre.replace(/'/g,"\\'")}')">
                    <span class="opt-name">${c.nombre}</span>
                    <span class="opt-phone">${c.tel}</span>
                </div>`).join('');
        }
        dropdown.classList.add('visible');
    }

    function selectCliente(id, nombre) {
        clienteId.value = id;
        searchInput.value = nombre;
        searchInput.style.display = 'none';
        dropdown.classList.remove('visible');
        selectedName.textContent = nombre;
        selectedBadge.classList.add('visible');
        updateResume();
    }

    function clearCliente() {
        clienteId.value = '';
        searchInput.value = '';
        searchInput.style.display = '';
        searchInput.focus();
        selectedBadge.classList.remove('visible');
        updateResume();
    }

    searchInput.addEventListener('input', () => renderDropdown(searchInput.value));
    searchInput.addEventListener('focus', () => { if (searchInput.value) renderDropdown(searchInput.value); });
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-cliente-wrapper')) dropdown.classList.remove('visible');
    });

    // ===== Sección Servicios =====
    let srvCatFiltro = '';

    function filterSrvCat(btn, cat) {
        srvCatFiltro = cat;
        document.querySelectorAll('#srvCatStrip .pkg-cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('#serviciosContainer .select-servicio').forEach(sel => {
            const currentVal = sel.value;
            sel.innerHTML = buildServicioOptions(currentVal);
        });
    }

    function buildServicioOptions(selected = '') {
        const lista = srvCatFiltro
            ? SERVICIOS.filter(s => s.categoria === srvCatFiltro)
            : SERVICIOS;
        return '<option value="">— Seleccionar —</option>' +
            lista.map(s => `<option value="${s.id}" data-precio="${s.precio}" data-duracion="${s.duracion}"${s.id == selected ? ' selected' : ''}>${s.nombre}</option>`).join('');
    }

    function onServicioChange(sel) {
        const row       = sel.closest('.linea-servicio');
        const idx       = row.dataset.index;
        const opt       = sel.options[sel.selectedIndex];
        const precioInp = row.querySelector('.linea-precio-input');
        const descInp   = row.querySelector('.linea-desc-input');

        if (opt.value) {
            serviciosSeleccionados.set(idx, { servicio_id: opt.value, nombre: opt.text });
            const srv = SERVICIOS.find(s => s.id == opt.value);
            if (precioInp) precioInp.value = parseFloat(opt.dataset.precio || 0).toFixed(2);
            if (descInp) {
                const desc = srv?.descuento ?? 0;
                descInp.value          = desc > 0 ? desc : '';
                descInp.style.background = desc > 0 ? 'rgba(76,175,80,0.08)' : '';
                descInp.title          = desc > 0 ? `Descuento programado: ${desc}%` : '';
            }
        } else {
            serviciosSeleccionados.delete(idx);
            if (precioInp) precioInp.value = '';
            if (descInp)  { descInp.value = ''; descInp.style.background = ''; descInp.title = ''; }
        }

        rebuildProfServDropdowns();
        updateResume();
    }

    function addServicio() {
        const idx = String(servicioIdx++);
        const div = document.createElement('div');
        div.className = 'linea-servicio';
        div.dataset.index = idx;
        div.innerHTML = `
            <div class="form-group" style="margin:0;">
                <select name="servicios[${idx}][servicio_id]" class="form-control select-servicio" required
                        onchange="onServicioChange(this)">
                    ${buildServicioOptions()}
                </select>
            </div>
            <div class="form-group linea-precio-wrap" style="margin:0;">
                <span class="linea-precio-prefix">Bs.</span>
                <input type="number" name="servicios[${idx}][precio]" class="form-control linea-precio-input"
                       step="0.01" min="0" placeholder="0.00" oninput="updateResume()">
            </div>
            <div class="form-group linea-desc-wrap" style="margin:0;">
                <input type="number" name="servicios[${idx}][descuento]" class="form-control linea-desc-input"
                       step="1" min="0" max="100" placeholder="0" oninput="updateResume()">
                <span class="linea-desc-suffix">%</span>
            </div>
            <button type="button" class="btn-remove-linea" onclick="removeServicio(this)" title="Quitar">✕</button>`;
        document.getElementById('serviciosContainer').appendChild(div);
    }

    function removeServicio(btn) {
        const container = document.getElementById('serviciosContainer');
        if (container.querySelectorAll('.linea-servicio').length <= 1) return;
        const row = btn.closest('.linea-servicio');
        serviciosSeleccionados.delete(row.dataset.index);
        row.remove();
        rebuildProfServDropdowns();
        updateResume();
    }

    document.getElementById('serviciosContainer').addEventListener('change', e => {
        if (!e.target.classList.contains('select-servicio')) updateResume();
    });
    document.getElementById('serviciosContainer').addEventListener('input', e => {
        if (e.target.classList.contains('linea-precio-input')) updateResume();
    });

    // ===== Sección Profesionales =====
    function buildEmpleadoOptions() {
        return '<option value="">— Seleccionar —</option>' +
            EMPLEADOS.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
    }

    function buildProfServicioOptions() {
        const items = [...serviciosSeleccionados.values()];
        if (items.length === 0) return '<option value="">— Elige servicios primero —</option>';
        return '<option value="">— Qué realizó —</option>' +
            items.map(s => `<option value="${s.servicio_id}">${s.nombre}</option>`).join('');
    }

    function rebuildProfServDropdowns() {
        document.querySelectorAll('.select-prof-servicio').forEach(sel => {
            const current = sel.value;
            const items   = [...serviciosSeleccionados.values()];
            sel.innerHTML = '<option value="">— Qué realizó —</option>' +
                items.map(s => `<option value="${s.servicio_id}"${s.servicio_id == current ? ' selected' : ''}>${s.nombre}</option>`).join('');
        });
    }

    function addProfesional() {
        const idx = profIdx++;
        const header = document.getElementById('profHeadersRow');
        header.style.display = 'grid';

        const div = document.createElement('div');
        div.className = 'linea-profesional';
        div.dataset.index = idx;
        div.innerHTML = `
            <div class="form-group" style="margin:0;">
                <select name="profesionales[${idx}][empleado_id]" class="form-control">
                    ${buildEmpleadoOptions()}
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <select name="profesionales[${idx}][servicio_id]" class="form-control select-prof-servicio">
                    ${buildProfServicioOptions()}
                </select>
            </div>
            <button type="button" class="btn-remove-linea" onclick="removeProfesional(this)" title="Quitar">✕</button>`;
        document.getElementById('profesionalesContainer').appendChild(div);
    }

    function removeProfesional(btn) {
        btn.closest('.linea-profesional').remove();
        if (document.querySelectorAll('.linea-profesional').length === 0) {
            document.getElementById('profHeadersRow').style.display = 'none';
        }
    }

    // ===== Resumen lateral =====
    function updateResume() {
        const tipo = document.getElementById('clienteTipo').value;
        let clienteLabel = 'Sin seleccionar';
        if (tipo === 'existente' && selectedName.textContent) {
            clienteLabel = selectedName.textContent;
        } else if (tipo === 'nuevo') {
            const n = document.querySelector('[name="nuevo_nombre"]').value.trim();
            const a = document.querySelector('[name="nuevo_apellido"]').value.trim();
            clienteLabel = n ? n + (a ? ' ' + a : '') + ' (nuevo)' : 'Completar datos...';
        }
        const resumeNombre = document.getElementById('resumeClienteName');
        resumeNombre.textContent = clienteLabel;
        resumeNombre.style.fontStyle = clienteLabel === 'Sin seleccionar' ? 'italic' : 'normal';
        resumeNombre.style.color = clienteLabel === 'Sin seleccionar' ? 'var(--text-light)' : 'var(--text-dark)';

        const rows = document.querySelectorAll('#serviciosContainer .linea-servicio');
        const items = [];
        let totalBruto = 0;
        let totalNeto  = 0;

        rows.forEach(row => {
            const sel      = row.querySelector('.select-servicio');
            const precioIn = row.querySelector('.linea-precio-input');
            const descIn   = row.querySelector('.linea-desc-input');
            if (sel && sel.value) {
                const nombre  = sel.options[sel.selectedIndex].text;
                const precio  = precioIn && precioIn.value ? parseFloat(precioIn.value) : parseFloat(sel.options[sel.selectedIndex].dataset.precio || 0);
                const desc    = descIn && descIn.value ? parseFloat(descIn.value) : 0;
                const neto    = Math.round(precio * (1 - desc / 100) * 100) / 100;
                items.push({ nombre, precio, desc, neto });
                totalBruto += precio;
                totalNeto  += neto;
            }
        });

        const ahorro = Math.round((totalBruto - totalNeto) * 100) / 100;

        const resumeServicios = document.getElementById('resumeServicios');
        if (items.length > 0) {
            resumeServicios.innerHTML = items.map(i => `
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:0.2rem;gap:0.5rem;">
                    <span style="color:var(--text-dark);">• ${i.nombre}</span>
                    <span style="color:var(--accent-color);font-weight:400;white-space:nowrap;">
                        ${i.desc > 0 ? `<span style="text-decoration:line-through;color:var(--text-light);font-size:0.78rem;">Bs.${i.precio.toFixed(2)}</span> ` : ''}Bs. ${i.neto.toFixed(2)}
                    </span>
                </div>`).join('');
        } else {
            resumeServicios.innerHTML = '<span style="color:var(--text-light);font-style:italic;">Sin seleccionar</span>';
        }

        const ahorroEl = document.getElementById('resumeAhorro');
        if (ahorro > 0) {
            ahorroEl.textContent = `Ahorro: Bs. ${ahorro.toFixed(2)}`;
            ahorroEl.style.display = 'block';
        } else {
            ahorroEl.style.display = 'none';
        }
        document.getElementById('resumePrecio').textContent = totalNeto > 0 ? 'Bs. ' + totalNeto.toFixed(2) : '—';
    }

    document.querySelectorAll('[name="nuevo_nombre"],[name="nuevo_apellido"]').forEach(el => {
        el.addEventListener('input', updateResume);
    });

    // ===== Sección Contrato =====
    let contrPagoIdx = 1;

    function contrShowSection(pkg) {
        document.getElementById('contratoSection').style.display = '';
        document.getElementById('contrCrear').value     = '1';
        document.getElementById('contrPaqueteId').value = pkg.id;
        const precio = pkg.precio_total ? parseFloat(pkg.precio_total).toFixed(2) : '';
        document.getElementById('contrPrecioTotal').value = precio;
        // Reset extra pago rows (keep only the first mandatory one)
        const container = document.getElementById('contrPagosContainer');
        [...container.children].slice(1).forEach(r => r.remove());
        contrPagoIdx = 1;
        contrUpdateSuma();
    }

    function contrHideSection() {
        document.getElementById('contratoSection').style.display = 'none';
        document.getElementById('contrCrear').value     = '0';
        document.getElementById('contrPaqueteId').value = '';
    }

    function contrAddPago() {
        const idx = contrPagoIdx++;
        const div = document.createElement('div');
        div.style.cssText = 'display:grid;grid-template-columns:1fr 120px 1fr auto;gap:0.4rem;align-items:center;margin-bottom:0.35rem;';
        div.innerHTML = `
            <div class="form-group linea-precio-wrap" style="margin:0;">
                <span class="linea-precio-prefix">Bs.</span>
                <input type="number" name="contrato_pagos[${idx}][monto]" class="form-control linea-precio-input contr-monto"
                       step="0.01" min="0" placeholder="0.00" oninput="contrUpdateSuma()">
            </div>
            <input type="date" name="contrato_pagos[${idx}][fecha_pago]" class="form-control" value="${new Date().toISOString().split('T')[0]}">
            <select name="contrato_pagos[${idx}][metodo_pago]" class="form-control" style="font-size:.8rem;">
                <option value="">— Método —</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="qr">QR</option>
            </select>
            <button type="button" class="btn-remove-linea" onclick="this.closest('div').remove();contrUpdateSuma();" title="Quitar">✕</button>`;
        document.getElementById('contrPagosContainer').appendChild(div);
    }

    function contrUpdateSuma() {
        let suma = 0;
        document.querySelectorAll('.contr-monto').forEach(inp => suma += parseFloat(inp.value) || 0);
        document.getElementById('contrSumaCuotas').textContent = suma.toFixed(2);
        const precioVal = parseFloat(document.getElementById('contrPrecioTotal').value) || 0;
        const pend = Math.max(0, precioVal - suma);
        document.getElementById('contrTotalRef').textContent = pend.toFixed(2);
    }

    // ===== Selector de campaña =====
    document.querySelectorAll('.campana-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.campana-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
        });
    });
</script>
@endpush
