@extends('admin.layouts.app')

@section('title', 'Descuentos & Cupones')
@section('page-title', 'Descuentos & Cupones')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────── */
    .desc-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 900px) { .desc-grid { grid-template-columns: 1fr; } }

    /* ── Section card ────────────────────────────────── */
    .desc-section {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.06);
    }
    .desc-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .desc-section-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem;
        color: var(--primary-color);
        letter-spacing: 0.5px;
    }
    .desc-section-body { padding: 1.5rem; }

    /* ── Verificar cupón widget ──────────────────────── */
    .verificar-widget {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.06);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .verificar-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
        letter-spacing: 0.5px;
    }
    .verificar-row {
        display: flex;
        gap: 0.6rem;
        align-items: stretch;
        max-width: 480px;
    }
    .verificar-input {
        flex: 1;
        font-family: 'Montserrat', monospace;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-size: 1rem;
    }
    .verificar-result {
        margin-top: 1.2rem;
        display: none;
    }
    .vr-card {
        border: 1px solid rgba(0,0,0,0.08);
        padding: 1rem 1.2rem;
    }
    .vr-estado {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 500;
        margin-bottom: 0.4rem;
    }
    .vr-estado.valido   { color: var(--success-color); }
    .vr-estado.agotado  { color: var(--error-color); }
    .vr-estado.vencido  { color: var(--error-color); }
    .vr-estado.inactivo { color: var(--text-light); }
    .vr-estado.no_encontrado { color: var(--error-color); }

    .vr-codigo {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.8rem;
        color: var(--primary-color);
        letter-spacing: 3px;
        line-height: 1;
        margin-bottom: 0.3rem;
    }
    .vr-meta { font-size: 0.8rem; color: var(--text-light); margin-bottom: 0.6rem; }
    .vr-valor {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        color: var(--secondary-color);
        line-height: 1;
        margin-bottom: 0.8rem;
    }
    .vr-stats {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-bottom: 0.8rem;
    }
    .vr-stat { display: flex; flex-direction: column; gap: 0.1rem; }
    .vr-stat-val { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; color: var(--primary-color); line-height: 1; }
    .vr-stat-lbl { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); }

    .vr-usos-list { margin-top: 0.6rem; }
    .vr-uso-row {
        display: flex;
        gap: 1rem;
        padding: 0.35rem 0;
        border-top: 1px solid rgba(0,0,0,0.05);
        font-size: 0.78rem;
        color: var(--text-light);
        flex-wrap: wrap;
    }
    .vr-uso-fecha { color: var(--text-dark); min-width: 110px; }

    /* ── Table ───────────────────────────────────────── */
    .desc-table { width: 100%; border-collapse: collapse; }
    .desc-table th {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-light);
        font-weight: 400;
        padding: 0.5rem 0.6rem;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        white-space: nowrap;
    }
    .desc-table td {
        padding: 0.65rem 0.6rem;
        font-size: 0.82rem;
        color: var(--text-dark);
        border-bottom: 1px solid rgba(0,0,0,0.04);
        vertical-align: middle;
    }
    .desc-table tr:last-child td { border-bottom: none; }
    .desc-table tr:hover td { background: var(--light-bg); }

    .estado-chip {
        display: inline-block;
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 0.18rem 0.55rem;
        border-radius: 2px;
        font-weight: 500;
    }
    .chip-vigente  { background: rgba(76,175,80,0.12);  color: #388e3c; }
    .chip-próximo  { background: rgba(255,193,7,0.15);  color: #f57f17; }
    .chip-vencido  { background: rgba(244,67,54,0.1);   color: #c62828; }
    .chip-inactivo { background: rgba(0,0,0,0.06);       color: var(--text-light); }
    .chip-valido   { background: rgba(76,175,80,0.12);  color: #388e3c; }
    .chip-agotado  { background: rgba(244,67,54,0.1);   color: #c62828; }

    .codigo-chip {
        font-family: 'Montserrat', monospace;
        font-size: 0.75rem;
        letter-spacing: 1.5px;
        background: var(--light-bg);
        padding: 0.2rem 0.5rem;
        border: 1px solid rgba(0,0,0,0.08);
    }

    /* ── Action buttons ──────────────────────────────── */
    .tbl-btn {
        background: none;
        border: 1px solid rgba(0,0,0,0.12);
        color: var(--text-light);
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        transition: var(--transition);
        line-height: 1;
    }
    .tbl-btn:hover { color: var(--text-dark); border-color: var(--text-dark); }
    .tbl-btn.danger:hover { color: var(--error-color); border-color: var(--error-color); }

    /* ── Create forms ────────────────────────────────── */
    .create-form {
        border-top: 1px solid rgba(0,0,0,0.06);
        padding-top: 1.2rem;
        margin-top: 1.2rem;
    }
    .create-form-title {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-light);
        margin-bottom: 0.9rem;
    }

    .empty-mini {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-light);
        font-size: 0.82rem;
        font-style: italic;
    }
</style>
@endpush

@section('content')

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:1.5rem;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
            <ul style="margin:0;padding-left:1.2rem;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ── Verificador de cupones ──────────────────────────────────── --}}
    <div class="verificar-widget">
        <div class="verificar-title">Verificar código de cupón</div>
        <div class="verificar-row">
            <input id="vCodigo" type="text" class="form-control verificar-input" placeholder="SPA-XXXX-XXX" maxlength="30">
            <button type="button" class="btn btn-primary" onclick="verificarCupon()">Verificar</button>
        </div>
        <div class="verificar-result" id="vResult"></div>
    </div>

    {{-- ── Dos columnas ─────────────────────────────────────────────── --}}
    <div class="desc-grid">

        {{-- ════════ Descuentos Programados ════════ --}}
        <div class="desc-section">
            <div class="desc-section-header">
                <span class="desc-section-title">Descuentos Programados</span>
                <span style="font-size:0.72rem;color:var(--text-light);">{{ $descuentos->count() }} registros</span>
            </div>
            <div class="desc-section-body">

                @if($descuentos->isEmpty())
                    <div class="empty-mini">Sin descuentos programados aún.</div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="desc-table">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>%</th>
                                    <th>Desde</th>
                                    <th>Hasta</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($descuentos as $d)
                                    <tr>
                                        <td>
                                            <div style="font-weight:400;">{{ $d->servicio?->nombre ?? '— Todos los servicios —' }}</div>
                                            @if($d->descripcion)
                                                <div style="font-size:0.72rem;color:var(--text-light);font-style:italic;">{{ $d->descripcion }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--secondary-color);">
                                                {{ rtrim(rtrim(number_format($d->porcentaje,2),'0'),'.') }}%
                                            </span>
                                        </td>
                                        <td style="white-space:nowrap;">{{ $d->fecha_inicio->format('d/m/Y') }}</td>
                                        <td style="white-space:nowrap;">{{ $d->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            <span class="estado-chip chip-{{ $d->estado }}">{{ $d->estado }}</span>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <form method="POST" action="{{ route('admin.descuentos.programados.toggle', $d) }}" style="display:inline;">
                                                @csrf @method('PUT')
                                                <button type="submit" class="tbl-btn" title="{{ $d->activo ? 'Desactivar' : 'Activar' }}">
                                                    {{ $d->activo ? '⏸' : '▶' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.descuentos.programados.destroy', $d) }}" style="display:inline;"
                                                  onsubmit="return confirm('¿Eliminar este descuento?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="tbl-btn danger" title="Eliminar">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Crear descuento --}}
                <div class="create-form">
                    <div class="create-form-title">Nuevo descuento programado</div>
                    <form method="POST" action="{{ route('admin.descuentos.programados.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Servicio</label>
                            <select name="servicio_id" class="form-control">
                                <option value="">— Todos los servicios —</option>
                                @foreach($servicios as $s)
                                    <option value="{{ $s->id }}" @selected(old('servicio_id') == $s->id)>{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción <span style="font-weight:300;color:var(--text-light);">(opcional)</span></label>
                            <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion') }}"
                                   placeholder="Ej: Semana de aniversario, Promo verano…" maxlength="200">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Descuento % <span style="color:var(--error-color)">*</span></label>
                                <div style="position:relative;">
                                    <input type="number" name="porcentaje" class="form-control" value="{{ old('porcentaje') }}"
                                           style="padding-right:1.8rem" step="0.5" min="1" max="100" required>
                                    <span style="position:absolute;right:0.7rem;top:50%;transform:translateY(-50%);font-size:0.8rem;color:var(--text-light);pointer-events:none;">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Desde <span style="color:var(--error-color)">*</span></label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hasta <span style="font-weight:300;color:var(--text-light);">(opcional)</span></label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar descuento</button>
                    </form>
                </div>

            </div>
        </div>

        {{-- ════════ Cupones ════════ --}}
        <div class="desc-section">
            <div class="desc-section-header">
                <span class="desc-section-title">Cupones de Descuento</span>
                <span style="font-size:0.72rem;color:var(--text-light);">{{ $cupones->count() }} cupones</span>
            </div>
            <div class="desc-section-body">

                @if($cupones->isEmpty())
                    <div class="empty-mini">Sin cupones generados aún.</div>
                @else
                    <div style="overflow-x:auto;">
                        <table class="desc-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Valor</th>
                                    <th>Usos</th>
                                    <th>Vence</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cupones as $c)
                                    <tr>
                                        <td>
                                            <span class="codigo-chip">{{ $c->codigo }}</span>
                                            @if($c->descripcion)
                                                <div style="font-size:0.7rem;color:var(--text-light);font-style:italic;margin-top:0.15rem;">{{ $c->descripcion }}</div>
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--secondary-color);">
                                                {{ $c->valor_label }}
                                            </span>
                                            <div style="font-size:0.65rem;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;">
                                                {{ $c->tipo === 'porcentaje' ? 'descuento' : 'monto fijo' }}
                                            </div>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;">{{ $c->usos_count }}</span>
                                            @if($c->usos_maximos)
                                                <span style="font-size:0.72rem;color:var(--text-light);"> / {{ $c->usos_maximos }}</span>
                                            @else
                                                <span style="font-size:0.72rem;color:var(--text-light);"> ilim.</span>
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;font-size:0.78rem;">{{ $c->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            <span class="estado-chip chip-{{ $c->estado }}">{{ $c->estado }}</span>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <form method="POST" action="{{ route('admin.descuentos.cupones.toggle', $c) }}" style="display:inline;">
                                                @csrf @method('PUT')
                                                <button type="submit" class="tbl-btn" title="{{ $c->activo ? 'Desactivar' : 'Activar' }}">
                                                    {{ $c->activo ? '⏸' : '▶' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.descuentos.cupones.destroy', $c) }}" style="display:inline;"
                                                  onsubmit="return confirm('¿Eliminar el cupón {{ $c->codigo }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="tbl-btn danger" title="Eliminar">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Generar cupón --}}
                <div class="create-form">
                    <div class="create-form-title">Generar nuevo cupón</div>
                    <form method="POST" action="{{ route('admin.descuentos.cupones.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Código personalizado <span style="font-weight:300;color:var(--text-light);">(opcional — se genera automáticamente)</span></label>
                            <input type="text" name="codigo" class="form-control"
                                   value="{{ old('codigo') }}" placeholder="Ej: VERANO25, SPA50…"
                                   maxlength="30" style="text-transform:uppercase;letter-spacing:1px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción <span style="font-weight:300;color:var(--text-light);">(opcional)</span></label>
                            <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion') }}"
                                   placeholder="Ej: Promo cumpleaños, Cliente VIP…" maxlength="200">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Tipo <span style="color:var(--error-color)">*</span></label>
                                <select name="tipo" class="form-control" id="cuponTipo" onchange="updateValorLabel()">
                                    <option value="porcentaje" @selected(old('tipo','porcentaje')==='porcentaje')>Porcentaje (%)</option>
                                    <option value="monto_fijo" @selected(old('tipo')==='monto_fijo')>Monto fijo (Bs.)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" id="valorLabel">Valor % <span style="color:var(--error-color)">*</span></label>
                                <input type="number" name="valor" class="form-control" value="{{ old('valor') }}"
                                       step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Usos máximos <span style="font-weight:300;color:var(--text-light);">(vacío = ilimitado)</span></label>
                                <input type="number" name="usos_maximos" class="form-control" value="{{ old('usos_maximos') }}"
                                       min="1" placeholder="Ej: 1, 50, 100…">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Vencimiento <span style="font-weight:300;color:var(--text-light);">(opcional)</span></label>
                                <input type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Generar cupón</button>
                    </form>
                </div>

            </div>
        </div>

    </div>{{-- /desc-grid --}}

@endsection

@push('scripts')
<script>
    function updateValorLabel() {
        const tipo = document.getElementById('cuponTipo').value;
        document.getElementById('valorLabel').innerHTML =
            (tipo === 'porcentaje' ? 'Valor %' : 'Valor Bs.') +
            ' <span style="color:var(--error-color)">*</span>';
    }

    // ── Verificar cupón ───────────────────────────────────────────────────────
    const estadoLabels = {
        valido:        'Válido',
        agotado:       'Agotado',
        vencido:       'Vencido',
        inactivo:      'Inactivo',
        no_encontrado: 'No encontrado',
    };

    async function verificarCupon() {
        const codigo = document.getElementById('vCodigo').value.trim();
        if (!codigo) return;

        const result = document.getElementById('vResult');
        result.style.display = 'block';
        result.innerHTML = '<div style="padding:0.8rem;color:var(--text-light);font-size:0.82rem;">Verificando…</div>';

        try {
            const res  = await fetch(`{{ route('admin.descuentos.cupones.verificar') }}?codigo=${encodeURIComponent(codigo)}`);
            const data = await res.json();
            renderVerificacion(data);
        } catch {
            result.innerHTML = '<div style="padding:0.8rem;color:var(--error-color);font-size:0.82rem;">Error al verificar.</div>';
        }
    }

    function renderVerificacion(d) {
        const el = document.getElementById('vResult');

        if (d.estado === 'no_encontrado') {
            el.innerHTML = `
                <div class="vr-card">
                    <div class="vr-estado no_encontrado">No encontrado</div>
                    <div style="font-size:0.82rem;color:var(--text-light);">El código <strong>${d.codigo || '—'}</strong> no existe en el sistema.</div>
                </div>`;
            return;
        }

        const usosMaxLbl = d.usos_maximos ? `/ ${d.usos_maximos}` : '/ ∞';
        const restLbl    = d.usos_maximos
            ? `${d.usos_restantes ?? 0} restante${d.usos_restantes !== 1 ? 's' : ''}`
            : 'Ilimitado';

        let usosHtml = '';
        if (d.usos && d.usos.length > 0) {
            usosHtml = `
                <div style="margin-top:0.8rem;">
                    <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:0.3rem;">Historial de usos (últimos ${d.usos.length})</div>
                    <div class="vr-usos-list">
                        ${d.usos.map(u => `
                            <div class="vr-uso-row">
                                <span class="vr-uso-fecha">${u.fecha}</span>
                                <span>${u.cliente}</span>
                                ${u.notas ? `<span style="font-style:italic;">${u.notas}</span>` : ''}
                            </div>`).join('')}
                    </div>
                </div>`;
        }

        el.innerHTML = `
            <div class="vr-card">
                <div class="vr-estado ${d.estado}">${estadoLabels[d.estado] ?? d.estado}</div>
                <div class="vr-codigo">${d.codigo}</div>
                ${d.descripcion ? `<div class="vr-meta">${d.descripcion}</div>` : ''}
                <div class="vr-valor">${d.valor_label}</div>
                <div class="vr-stats">
                    <div class="vr-stat">
                        <span class="vr-stat-val">${d.usos_actuales} ${usosMaxLbl}</span>
                        <span class="vr-stat-lbl">Usos</span>
                    </div>
                    <div class="vr-stat">
                        <span class="vr-stat-val">${restLbl}</span>
                        <span class="vr-stat-lbl">Disponibilidad</span>
                    </div>
                    ${d.vencimiento ? `
                    <div class="vr-stat">
                        <span class="vr-stat-val">${d.vencimiento}</span>
                        <span class="vr-stat-lbl">Vencimiento</span>
                    </div>` : ''}
                </div>
                ${usosHtml}
            </div>`;
    }

    // Enter key triggers verification
    document.getElementById('vCodigo').addEventListener('keydown', e => {
        if (e.key === 'Enter') verificarCupon();
    });
</script>
@endpush
