@extends('admin.layouts.app')

@section('title', 'Migración de Datos')
@section('page-title', 'Migración de Datos entre Sucursales')

@section('content')

    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.sucursales.index') }}" class="btn btn-sm btn-outline">← Volver a sucursales</a>
    </div>

    {{-- ── Resultado de migración ejecutada ──────────────────────────────────── --}}
    @if(session('migracion_resultados'))
        @php $res = session('migracion_resultados'); @endphp
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
            <h4 style="color:#166534;font-size:1rem;margin:0 0 0.75rem;">✅ Migración completada</h4>
            <p style="font-size:0.85rem;color:#166534;margin:0 0 0.5rem;">
                De <strong>{{ session('migracion_origen') }}</strong> → <strong>{{ session('migracion_destino') }}</strong>
            </p>
            <ul style="margin:0;padding-left:1.25rem;font-size:0.85rem;color:#15803d;">
                @if(isset($res['empleados']))
                    <li>{{ $res['empleados'] }} empleado(s) asignado(s) a la sucursal destino</li>
                @endif
                @if(isset($res['citas']))
                    <li>{{ $res['citas'] }} cita(s) reasignada(s)</li>
                @endif
                @if(isset($res['clientes']))
                    <li>{{ $res['clientes'] }} cliente(s) asignado(s) a la sucursal destino</li>
                @endif
                @if(isset($res['inventario']))
                    <li>{{ $res['inventario']['entradas'] }} entrada(s) y {{ $res['inventario']['salidas'] }} salida(s) de inventario reasignadas</li>
                @endif
            </ul>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        {{-- ── Formulario ─────────────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configurar migración</h3>
            </div>
            <div class="card-body">

                <div style="background:#fef9ec;border:1px solid #f5d87a;border-radius:6px;padding:0.75rem 1rem;margin-bottom:1.5rem;font-size:0.82rem;color:#92600a;line-height:1.55;">
                    ⚠️ <strong>Atención:</strong> La migración de citas e inventario <strong>mueve</strong> los registros (cambia su sucursal). La migración de empleados los <strong>asigna</strong> también a la sucursal destino sin eliminarlos del origen. Esta operación no se puede deshacer fácilmente.
                </div>

                <form method="POST" action="{{ route('admin.sucursales.migracion.procesar') }}" id="migracionForm">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Sucursal origen <span style="color:var(--error-color)">*</span></label>
                            <select name="origen_id" class="form-control @error('origen_id') is-invalid @enderror" required
                                    onchange="actualizarDestino()">
                                <option value="">— Seleccionar —</option>
                                <option value="sin_asignar" {{ old('origen_id', $data['origen_id'] ?? '') === 'sin_asignar' ? 'selected' : '' }}>
                                    🌐 Sin sucursal asignada (datos previos a la migración)
                                </option>
                                @foreach($sucursales as $s)
                                    <option value="{{ $s->id }}"
                                        {{ old('origen_id', $data['origen_id'] ?? '') == $s->id ? 'selected' : '' }}>
                                        {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('origen_id')<span style="color:var(--error-color);font-size:.78rem">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sucursal destino <span style="color:var(--error-color)">*</span></label>
                            <select name="destino_id" class="form-control @error('destino_id') is-invalid @enderror" required>
                                <option value="">— Seleccionar —</option>
                                @foreach($sucursales as $s)
                                    <option value="{{ $s->id }}"
                                        {{ old('destino_id', $data['destino_id'] ?? '') == $s->id ? 'selected' : '' }}>
                                        {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('destino_id')<span style="color:var(--error-color);font-size:.78rem">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">¿Qué datos migrar? <span style="color:var(--error-color)">*</span></label>
                        @error('migrar')<span style="color:var(--error-color);font-size:.78rem;display:block;margin-bottom:0.5rem;">{{ $message }}</span>@enderror

                        @php $migrarSel = old('migrar', $data['migrar'] ?? []); @endphp

                        <label style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.65rem 0.85rem;border:1px solid var(--border-color);border-radius:6px;margin-bottom:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="migrar[]" value="empleados"
                                   {{ in_array('empleados', $migrarSel) ? 'checked' : '' }}
                                   style="width:auto;margin:0.15rem 0 0;">
                            <div>
                                <div style="font-size:0.85rem;font-weight:500;">💼 Empleados</div>
                                <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.1rem;">
                                    Asigna los empleados del origen también a la sucursal destino. No los elimina del origen.
                                </div>
                            </div>
                        </label>

                        <label style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.65rem 0.85rem;border:1px solid var(--border-color);border-radius:6px;margin-bottom:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="migrar[]" value="clientes"
                                   {{ in_array('clientes', $migrarSel) ? 'checked' : '' }}
                                   style="width:auto;margin:0.15rem 0 0;"
                                   onchange="toggleFechas()">
                            <div>
                                <div style="font-size:0.85rem;font-weight:500;">👥 Clientes</div>
                                <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.1rem;">
                                    Asigna los clientes del origen a la sucursal destino. Acepta filtro por fecha de registro.
                                </div>
                            </div>
                        </label>

                        <label style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.65rem 0.85rem;border:1px solid var(--border-color);border-radius:6px;margin-bottom:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="migrar[]" value="citas"
                                   {{ in_array('citas', $migrarSel) ? 'checked' : '' }}
                                   style="width:auto;margin:0.15rem 0 0;"
                                   onchange="toggleFechas()">
                            <div>
                                <div style="font-size:0.85rem;font-weight:500;">📅 Citas</div>
                                <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.1rem;">
                                    Mueve las citas del origen al destino. Acepta filtro por rango de fechas.
                                </div>
                            </div>
                        </label>

                        <label style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.65rem 0.85rem;border:1px solid var(--border-color);border-radius:6px;cursor:pointer;">
                            <input type="checkbox" name="migrar[]" value="inventario"
                                   {{ in_array('inventario', $migrarSel) ? 'checked' : '' }}
                                   style="width:auto;margin:0.15rem 0 0;"
                                   onchange="toggleFechas()">
                            <div>
                                <div style="font-size:0.85rem;font-weight:500;">📦 Inventario</div>
                                <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.1rem;">
                                    Mueve entradas y salidas de inventario del origen al destino.
                                </div>
                            </div>
                        </label>
                    </div>

                    <div id="seccionFechas" style="{{ (in_array('citas', $migrarSel) || in_array('clientes', $migrarSel) || in_array('inventario', $migrarSel)) ? '' : 'display:none;' }}">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Desde (fecha)</label>
                                <input type="date" name="fecha_desde" class="form-control"
                                       value="{{ old('fecha_desde', $data['fecha_desde'] ?? '') }}">
                                <span style="font-size:0.72rem;color:var(--text-light);">Vacío = sin límite inferior</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hasta (fecha)</label>
                                <input type="date" name="fecha_hasta" class="form-control"
                                       value="{{ old('fecha_hasta', $data['fecha_hasta'] ?? '') }}">
                                <span style="font-size:0.72rem;color:var(--text-light);">Vacío = sin límite superior</span>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:0.75rem;margin-top:1rem;flex-wrap:wrap;">
                        <button type="submit" name="accion" value="preview" class="btn btn-outline">
                            🔍 Previsualizar
                        </button>
                        @if(isset($preview) && count($preview) > 0)
                            <button type="submit" name="accion" value="ejecutar" class="btn btn-primary"
                                    onclick="return confirm('¿Confirmas la migración? Esta operación no se puede deshacer fácilmente.')">
                                ✅ Confirmar y ejecutar
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Preview ─────────────────────────────────────────────────────────── --}}
        <div>
            @if(isset($preview) && count($preview) > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Vista previa</h3>
                    </div>
                    <div class="card-body">
                        <p style="font-size:0.85rem;color:var(--text-light);margin-bottom:1rem;">
                            De <strong>{{ $sinAsignar ? 'Sin sucursal asignada' : $origen->nombre }}</strong> → <strong>{{ $destino->nombre }}</strong>
                            @if(!empty($data['fecha_desde']) || !empty($data['fecha_hasta']))
                                <br><span style="font-size:0.78rem;">
                                    Fechas: {{ $data['fecha_desde'] ?? '—' }} al {{ $data['fecha_hasta'] ?? '—' }}
                                </span>
                            @endif
                        </p>

                        @if(isset($preview['empleados']))
                            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border-color);">
                                <span style="font-size:0.85rem;">💼 Empleados a asignar</span>
                                <strong style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:{{ $preview['empleados'] > 0 ? 'var(--accent-color)' : 'var(--text-light)' }}">
                                    {{ $preview['empleados'] }}
                                </strong>
                            </div>
                        @endif

                        @if(isset($preview['citas']))
                            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border-color);">
                                <span style="font-size:0.85rem;">📅 Citas a mover</span>
                                <strong style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:{{ $preview['citas'] > 0 ? 'var(--accent-color)' : 'var(--text-light)' }}">
                                    {{ $preview['citas'] }}
                                </strong>
                            </div>
                        @endif

                        @if(isset($preview['clientes']))
                            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border-color);">
                                <span style="font-size:0.85rem;">👥 Clientes a asignar</span>
                                <strong style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:{{ $preview['clientes'] > 0 ? 'var(--accent-color)' : 'var(--text-light)' }}">
                                    {{ $preview['clientes'] }}
                                </strong>
                            </div>
                        @endif

                        @if(isset($preview['inventario']))
                            <div style="padding:0.75rem 0;">
                                <div style="display:flex;justify-content:space-between;margin-bottom:0.4rem;">
                                    <span style="font-size:0.85rem;">📦 Entradas de inventario</span>
                                    <strong style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:{{ $preview['inventario']['entradas'] > 0 ? 'var(--accent-color)' : 'var(--text-light)' }}">
                                        {{ $preview['inventario']['entradas'] }}
                                    </strong>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="font-size:0.85rem;">📦 Salidas de inventario</span>
                                    <strong style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:{{ $preview['inventario']['salidas'] > 0 ? 'var(--accent-color)' : 'var(--text-light)' }}">
                                        {{ $preview['inventario']['salidas'] }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                        @php
                            $total = ($preview['empleados'] ?? 0) + ($preview['citas'] ?? 0) + ($preview['clientes'] ?? 0)
                                   + ($preview['inventario']['entradas'] ?? 0) + ($preview['inventario']['salidas'] ?? 0);
                        @endphp

                        @if($total === 0)
                            <div style="padding:1rem;text-align:center;color:var(--text-light);font-size:0.85rem;">
                                No hay registros que cumplan los criterios seleccionados.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div style="background:var(--light-bg,#f8f8f6);border:1px dashed var(--border-color);border-radius:8px;padding:2rem;text-align:center;color:var(--text-light);font-size:0.85rem;">
                    <div style="font-size:2rem;margin-bottom:0.5rem;">🔍</div>
                    Configura los parámetros y haz clic en <strong>Previsualizar</strong> para ver cuántos registros se migrarán.
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script>
function toggleFechas() {
    const citasChecked    = document.querySelector('input[value="citas"]').checked;
    const clientesChecked = document.querySelector('input[value="clientes"]').checked;
    const invChecked      = document.querySelector('input[value="inventario"]').checked;
    document.getElementById('seccionFechas').style.display = (citasChecked || clientesChecked || invChecked) ? '' : 'none';
}

function actualizarDestino() {
    const origenId = document.querySelector('[name="origen_id"]').value;
    document.querySelectorAll('[name="destino_id"] option').forEach(opt => {
        opt.disabled = opt.value && opt.value === origenId;
        if (opt.disabled && opt.selected) {
            document.querySelector('[name="destino_id"]').value = '';
        }
    });
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', () => {
    actualizarDestino();
    toggleFechas();
});
</script>
@endpush
