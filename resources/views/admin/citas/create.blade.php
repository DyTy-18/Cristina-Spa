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

    /* ===== Servicio info preview ===== */
    .servicio-preview {
        display: none;
        margin-top: 0.5rem;
        padding: 0.6rem 0.9rem;
        background: var(--light-bg);
        border-left: 2px solid var(--accent-color);
        font-size: 0.8rem;
        color: var(--text-light);
        font-weight: 300;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .servicio-preview.visible { display: flex; }

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

                {{-- SECCIÓN 2: Servicio --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">2</div>
                        <span class="form-section-title">Servicio</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label class="form-label">Servicio <span style="color:var(--error-color)">*</span></label>
                            <select name="servicio_id" id="selectServicio" class="form-control" required>
                                <option value="">— Seleccionar servicio —</option>
                                @foreach($servicios as $s)
                                    <option value="{{ $s->id }}"
                                            data-precio="{{ $s->precio }}"
                                            data-duracion="{{ $s->duracion_minutos }}"
                                            data-duracion-fmt="{{ $s->duracion_formateada }}"
                                            data-categoria="{{ $s->categoria }}"
                                            {{ old('servicio_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('servicio_id')
                                <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                            @enderror
                            <div class="servicio-preview" id="servicioPreview">
                                <span>⏱ <strong id="prevDuracion"></strong></span>
                                <span>💰 Precio base: <strong id="prevPrecio"></strong></span>
                                <span style="text-transform:capitalize;color:var(--accent-color)" id="prevCategoria"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: Horario y profesional --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">3</div>
                        <span class="form-section-title">Horario y profesional</span>
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
                            <label class="form-label">Profesional asignado</label>
                            <select name="empleado_id" class="form-control">
                                <option value="">— Sin asignar por ahora —</option>
                                @foreach($empleados as $e)
                                    <option value="{{ $e->id }}" {{ old('empleado_id') == $e->id ? 'selected' : '' }}>
                                        {{ $e->nombre }} {{ $e->apellido }}{{ $e->especialidad ? ' · '.$e->especialidad : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 4: Detalles finales --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">4</div>
                        <span class="form-section-title">Detalles</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-row">
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
                                <label class="form-label">Precio final (Bs.)</label>
                                <input type="number" name="precio_final" id="inputPrecio" class="form-control"
                                       step="0.01" min="0"
                                       value="{{ old('precio_final') }}"
                                       placeholder="Se autocompleta al elegir servicio">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control" rows="3"
                                      placeholder="Preferencias del cliente, observaciones...">{{ old('notas') }}</textarea>
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
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Servicio</div>
                            <div id="resumeServicio" style="font-size:0.9rem;color:var(--text-light);font-style:italic;">Sin seleccionar</div>
                            <div id="resumeDuracion" style="font-size:0.78rem;color:var(--text-light);margin-top:0.2rem;"></div>
                        </div>

                        <div style="margin-bottom:1.5rem;">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Precio</div>
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
    // ===== Datos de clientes (para el buscador) =====
    const CLIENTES = @json($clientesJson);

    // ===== Toggle existente / nuevo =====
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

        const matches = CLIENTES.filter(c =>
            c.nombre.toLowerCase().includes(q) || c.tel.includes(q)
        ).slice(0, 12);

        if (matches.length === 0) {
            dropdown.innerHTML = `<div class="search-option-empty">No se encontró ningún cliente con "${term}"</div>`;
        } else {
            dropdown.innerHTML = matches.map(c => `
                <div class="search-option" onclick="selectCliente(${c.id}, '${c.nombre.replace(/'/g,"\\'")}')">
                    <span class="opt-name">${c.nombre}</span>
                    <span class="opt-phone">${c.tel}</span>
                </div>
            `).join('');
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
        if (!e.target.closest('.search-cliente-wrapper')) {
            dropdown.classList.remove('visible');
        }
    });

    // ===== Servicio preview + autocomplete precio =====
    const selectServicio = document.getElementById('selectServicio');
    const inputPrecio    = document.getElementById('inputPrecio');
    const preview        = document.getElementById('servicioPreview');

    selectServicio.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            document.getElementById('prevDuracion').textContent  = opt.dataset.duracionFmt;
            document.getElementById('prevPrecio').textContent    = 'Bs. ' + parseFloat(opt.dataset.precio).toFixed(2);
            document.getElementById('prevCategoria').textContent = opt.dataset.categoria;
            preview.classList.add('visible');
            if (!inputPrecio.value) inputPrecio.value = parseFloat(opt.dataset.precio).toFixed(2);
        } else {
            preview.classList.remove('visible');
        }
        updateResume();
    });

    // ===== Precio actualiza resumen =====
    inputPrecio.addEventListener('input', updateResume);

    // ===== Resumen lateral =====
    function updateResume() {
        const tipo = document.getElementById('clienteTipo').value;
        let clienteLabel = 'Sin seleccionar';

        if (tipo === 'existente' && selectedName.textContent) {
            clienteLabel = selectedName.textContent;
        } else if (tipo === 'nuevo') {
            const n = document.querySelector('[name="nuevo_nombre"]').value.trim();
            const a = document.querySelector('[name="nuevo_apellido"]').value.trim();
            clienteLabel = n ? (n + (a ? ' ' + a : '')) + ' (nuevo)' : 'Completar datos...';
        }

        document.getElementById('resumeClienteName').textContent = clienteLabel;
        document.getElementById('resumeClienteName').style.fontStyle = clienteLabel === 'Sin seleccionar' ? 'italic' : 'normal';
        document.getElementById('resumeClienteName').style.color = clienteLabel === 'Sin seleccionar' ? 'var(--text-light)' : 'var(--text-dark)';

        const opt = selectServicio.options[selectServicio.selectedIndex];
        if (selectServicio.value) {
            document.getElementById('resumeServicio').textContent = opt.text;
            document.getElementById('resumeServicio').style.fontStyle = 'normal';
            document.getElementById('resumeServicio').style.color = 'var(--text-dark)';
            document.getElementById('resumeDuracion').textContent = opt.dataset.duracionFmt;
        } else {
            document.getElementById('resumeServicio').textContent = 'Sin seleccionar';
            document.getElementById('resumeServicio').style.fontStyle = 'italic';
            document.getElementById('resumeServicio').style.color = 'var(--text-light)';
            document.getElementById('resumeDuracion').textContent = '';
        }

        const precio = inputPrecio.value ? 'Bs. ' + parseFloat(inputPrecio.value).toFixed(2) : '—';
        document.getElementById('resumePrecio').textContent = precio;
    }

    // Actualiza resumen al escribir datos de nuevo cliente
    document.querySelectorAll('[name="nuevo_nombre"],[name="nuevo_apellido"]').forEach(el => {
        el.addEventListener('input', updateResume);
    });

    // Trigger inicial (si hay old values)
    if (selectServicio.value) selectServicio.dispatchEvent(new Event('change'));
</script>
@endpush
