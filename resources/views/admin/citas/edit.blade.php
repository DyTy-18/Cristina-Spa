@extends('admin.layouts.app')

@section('title', 'Editar Cita')
@section('page-title', 'Editar Cita')

@push('styles')
<style>
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
        <a href="{{ route('admin.citas.show', $cita) }}" class="btn btn-sm btn-outline">← Ver cita</a>
        <a href="{{ route('admin.citas.calendario') }}" class="btn btn-sm btn-outline">Calendario</a>
    </div>

    <form action="{{ route('admin.citas.update', $cita) }}" method="POST" id="formCita">
        @csrf
        @method('PUT')

        <div class="create-layout">

            {{-- ====== COLUMNA PRINCIPAL ====== --}}
            <div class="create-main">

                {{-- SECCIÓN 1: Cliente (solo lectura) --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">1</div>
                        <span class="form-section-title">Cliente</span>
                    </div>
                    <div class="form-section-body">
                        @if($cita->cliente)
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <div style="width:42px;height:42px;background:var(--accent-color);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--white);flex-shrink:0;">
                                    {{ mb_substr($cita->cliente->nombre, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-size:1rem;color:var(--text-dark);font-weight:400;">{{ $cita->cliente->nombre_completo }}</div>
                                    @if($cita->cliente->telefono)
                                        <div style="font-size:0.82rem;color:var(--text-light);font-weight:300;">{{ $cita->cliente->telefono }}</div>
                                    @endif
                                </div>
                                <a href="{{ route('admin.clientes.show', $cita->cliente) }}" class="btn btn-outline btn-sm" style="margin-left:auto;">Ver perfil</a>
                            </div>
                        @else
                            <p style="color:var(--text-light);font-style:italic;font-size:0.88rem;margin:0;">Sin cliente asociado</p>
                        @endif
                    </div>
                </div>

                {{-- SECCIÓN 2: Servicios --}}
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-num">2</div>
                        <span class="form-section-title">Servicios y profesionales</span>
                    </div>
                    <div class="form-section-body">
                        @error('servicios')
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
                            @foreach($cita->citaServicios as $i => $cs)
                                <div class="linea-servicio" data-index="{{ $i }}">
                                    <div class="form-group" style="margin:0;">
                                        <select name="servicios[{{ $i }}][servicio_id]" class="form-control select-servicio" required
                                                onchange="onServicioChange(this)">
                                            <option value="">— Seleccionar —</option>
                                            @foreach($servicios as $s)
                                                <option value="{{ $s->id }}"
                                                        data-precio="{{ $s->precio }}"
                                                        data-duracion="{{ $s->duracion_minutos }}"
                                                        {{ $cs->servicio_id == $s->id ? 'selected' : '' }}>
                                                    {{ $s->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group linea-precio-wrap" style="margin:0;">
                                        <span class="linea-precio-prefix">Bs.</span>
                                        <input type="number" name="servicios[{{ $i }}][precio]" class="form-control linea-precio-input"
                                               step="0.01" min="0" placeholder="0.00"
                                               value="{{ $cs->precio_unitario !== null ? number_format($cs->precio_unitario, 2, '.', '') : '' }}"
                                               oninput="updateResume()">
                                    </div>
                                    <div class="form-group linea-desc-wrap" style="margin:0;">
                                        <input type="number" name="servicios[{{ $i }}][descuento]" class="form-control linea-desc-input"
                                               step="1" min="0" max="100" placeholder="0"
                                               value="{{ $cs->descuento_porcentaje > 0 ? number_format($cs->descuento_porcentaje, 0, '.', '') : '' }}"
                                               oninput="updateResume()">
                                        <span class="linea-desc-suffix">%</span>
                                    </div>
                                    <button type="button" class="btn-remove-linea" onclick="removeServicio(this)" title="Quitar">✕</button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-outline btn-sm" onclick="addServicio()" style="margin-top:0.25rem;">
                            + Agregar servicio
                        </button>
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

                        <div id="profesionalesContainer">
                            @php $profIdx = 0; @endphp
                            @foreach($cita->citaServicios->whereNotNull('empleado_id')->values() as $cs)
                                <div class="linea-profesional" data-index="{{ $profIdx }}">
                                    <div class="form-group" style="margin:0;">
                                        <select name="profesionales[{{ $profIdx }}][empleado_id]" class="form-control">
                                            <option value="">— Seleccionar —</option>
                                            @foreach($empleados as $emp)
                                                <option value="{{ $emp->id }}" {{ $cs->empleado_id == $emp->id ? 'selected' : '' }}>
                                                    {{ $emp->nombre }} {{ $emp->apellido }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <select name="profesionales[{{ $profIdx }}][servicio_id]" class="form-control select-prof-servicio"
                                                data-current="{{ $cs->servicio_id }}">
                                            {{-- rebuilt by JS on init --}}
                                        </select>
                                    </div>
                                    <button type="button" class="btn-remove-linea" onclick="removeProfesional(this)" title="Quitar">✕</button>
                                </div>
                                @php $profIdx++; @endphp
                            @endforeach
                        </div>

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
                                       value="{{ old('fecha', $cita->fecha->format('Y-m-d')) }}" required>
                                @error('fecha')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hora <span style="color:var(--error-color)">*</span></label>
                                <input type="time" name="hora" class="form-control"
                                       value="{{ old('hora', substr($cita->getRawOriginal('hora'), 0, 5)) }}" required>
                                @error('hora')
                                    <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-control">
                                @foreach(['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('estado', $cita->estado) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control" rows="2"
                                      placeholder="Preferencias del cliente, observaciones...">{{ old('notas', $cita->notas) }}</textarea>
                        </div>

                        @if($campanas->isNotEmpty())
                        <div class="form-group" style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(0,0,0,0.05);">
                            <label class="form-label">Campaña</label>
                            <p style="font-size:0.78rem;color:var(--text-light);font-weight:300;margin-bottom:0.75rem;font-style:italic;">
                                Opcional — vincula esta visita a una campaña activa.
                            </p>
                            @php $campanaSeleccionada = old('campana_id', $cita->campana_id ?? ''); @endphp
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.5rem;">
                                <label class="campana-card {{ $campanaSeleccionada === '' || $campanaSeleccionada === null ? 'selected' : '' }}"
                                       for="campana_ninguna">
                                    <input type="radio" name="campana_id" id="campana_ninguna" value=""
                                           {{ $campanaSeleccionada === '' || $campanaSeleccionada === null ? 'checked' : '' }}>
                                    <span class="campana-card-name" style="color:var(--text-light);font-style:italic;">Sin campaña</span>
                                </label>
                                @foreach($campanas as $camp)
                                    <label class="campana-card {{ $campanaSeleccionada == $camp->id ? 'selected' : '' }}"
                                           for="campana_{{ $camp->id }}">
                                        <input type="radio" name="campana_id" id="campana_{{ $camp->id }}"
                                               value="{{ $camp->id }}"
                                               {{ $campanaSeleccionada == $camp->id ? 'checked' : '' }}>
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

            </div>

            {{-- ====== COLUMNA LATERAL: resumen ====== --}}
            <div class="create-aside">
                <div class="form-section">
                    <div class="form-section-header">
                        <span class="form-section-title">Resumen</span>
                    </div>
                    <div class="form-section-body" style="padding:1.25rem 1.5rem;">

                        <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(0,0,0,0.05);">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Cliente</div>
                            <div style="font-size:0.9rem;color:var(--text-dark);">{{ $cita->cliente?->nombre_completo ?? '—' }}</div>
                        </div>

                        <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(0,0,0,0.05);">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Servicios</div>
                            <div id="resumeServicios" style="font-size:0.9rem;color:var(--text-light);font-style:italic;">Cargando…</div>
                        </div>

                        <div style="margin-bottom:1.5rem;">
                            <div class="detail-label" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.25rem;">Total estimado</div>
                            <div id="resumeAhorro" style="font-size:0.78rem;color:var(--success-color);margin-bottom:0.15rem;display:none;"></div>
                            <div id="resumePrecio" style="font-family:'Cormorant Garamond',serif;font-size:2rem;color:var(--secondary-color);line-height:1;">—</div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;text-align:center;justify-content:center;">
                            Guardar cambios
                        </button>
                        <a href="{{ route('admin.citas.show', $cita) }}"
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
    const SERVICIOS = @json($serviciosJson);
    const EMPLEADOS = @json($empleadosJson);

    let servicioIdx = {{ $cita->citaServicios->count() }};
    let profIdx     = {{ $cita->citaServicios->whereNotNull('empleado_id')->count() }};

    const serviciosSeleccionados = new Map();

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
            if (precioInp && !precioInp.value) precioInp.value = parseFloat(opt.dataset.precio || 0).toFixed(2);
            // Solo auto-aplica descuento si el campo está vacío (fila nueva)
            if (descInp && !descInp.value) {
                const desc = srv?.descuento ?? 0;
                descInp.value            = desc > 0 ? desc : '';
                descInp.style.background = desc > 0 ? 'rgba(76,175,80,0.08)' : '';
                descInp.title            = desc > 0 ? `Descuento programado: ${desc}%` : '';
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

    // ===== Sección Profesionales =====
    function buildEmpleadoOptions() {
        return '<option value="">— Seleccionar —</option>' +
            EMPLEADOS.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
    }

    function buildProfServicioOptions(selected = '') {
        const items = [...serviciosSeleccionados.values()];
        if (items.length === 0) return '<option value="">— Elige servicios primero —</option>';
        return '<option value="">— Qué realizó —</option>' +
            items.map(s => `<option value="${s.servicio_id}"${s.servicio_id == selected ? ' selected' : ''}>${s.nombre}</option>`).join('');
    }

    function rebuildProfServDropdowns() {
        document.querySelectorAll('.select-prof-servicio').forEach(sel => {
            const current = sel.value || sel.dataset.current;
            const items   = [...serviciosSeleccionados.values()];
            sel.innerHTML = '<option value="">— Qué realizó —</option>' +
                items.map(s => `<option value="${s.servicio_id}"${s.servicio_id == current ? ' selected' : ''}>${s.nombre}</option>`).join('');
            delete sel.dataset.current;
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

    // ===== Inicializar estado desde filas pre-renderizadas =====
    document.addEventListener('DOMContentLoaded', function () {
        // Poblar serviciosSeleccionados desde filas existentes
        document.querySelectorAll('#serviciosContainer .linea-servicio').forEach(row => {
            const sel = row.querySelector('.select-servicio');
            if (sel && sel.value) {
                serviciosSeleccionados.set(row.dataset.index, {
                    servicio_id: sel.value,
                    nombre: sel.options[sel.selectedIndex].text,
                });
            }
        });

        // Mostrar header de profesionales si hay filas
        if (document.querySelectorAll('.linea-profesional').length > 0) {
            document.getElementById('profHeadersRow').style.display = 'grid';
        }

        // Reconstruir dropdowns de servicio en las filas de profesionales
        rebuildProfServDropdowns();

        updateResume();

        // ===== Selector de campaña =====
        document.querySelectorAll('.campana-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.campana-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
            });
        });
    });
</script>
@endpush
