@extends('admin.layouts.app')

@section('title', 'Gastos')
@section('page-title', 'Gastos')

@push('styles')
<style>
    /* ===== Tabs módulo ===== */
    .gastos-top-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .periodo-tabs {
        display: flex;
        border: 1px solid rgba(0,0,0,0.1);
        width: fit-content;
    }
    .periodo-tab {
        padding: 0.55rem 1.4rem;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.75rem;
        font-weight: 300;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        background: var(--white);
        border: none;
        border-right: 1px solid rgba(0,0,0,0.1);
        cursor: pointer;
        color: var(--text-light);
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
    }
    .periodo-tab:last-child { border-right: none; }
    .periodo-tab.active    { background: var(--primary-color); color: var(--white); }
    .periodo-tab:not(.active):hover { background: var(--light-bg); color: var(--text-dark); }

    /* ===== Tabs sección gastos/sueldos ===== */
    .seccion-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid rgba(0,0,0,0.07);
        margin-bottom: 1.5rem;
    }
    .seccion-tab {
        padding: 0.65rem 1.5rem;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.78rem;
        font-weight: 400;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        color: var(--text-light);
        text-decoration: none;
        display: inline-block;
        transition: var(--transition);
    }
    .seccion-tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        font-weight: 500;
    }
    .seccion-tab:not(.active):hover { color: var(--text-dark); }

    /* ===== Navegación período ===== */
    .periodo-nav {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .periodo-nav-btn {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.12);
        color: var(--text-light);
        padding: 0.38rem 0.85rem;
        cursor: pointer;
        font-size: 0.82rem;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
        line-height: 1.4;
    }
    .periodo-nav-btn:hover { border-color: var(--accent-color); color: var(--accent-color); }
    .periodo-nav-label {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem;
        color: var(--primary-color);
        letter-spacing: 0.5px;
        min-width: 160px;
        text-align: center;
    }
    .rango-panel {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.07);
        padding: 1.1rem 1.5rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .rango-panel .form-group { margin: 0; min-width: 150px; }
    .rango-panel label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-light); display: block; margin-bottom: 0.3rem; }

    /* ===== KPI cards ===== */
    .kpi-grid-gastos {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 640px) { .kpi-grid-gastos { grid-template-columns: 1fr; } }
    .kpi-card { background: var(--white); border: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem; }
    .kpi-card-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-light); margin-bottom: 0.35rem; }
    .kpi-card-value { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--primary-color); line-height: 1; }
    .kpi-card-value.danger { color: #dc3545; }
    .kpi-card-sub { font-size: 0.7rem; color: var(--text-light); margin-top: 0.2rem; }

    /* ===== Sección tabla ===== */
    .gastos-section { background: var(--white); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 1.25rem; }
    .gastos-section-header {
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }
    .gastos-section-title {
        font-family: 'Cormorant Garamond', serif; font-size: 1.05rem;
        color: var(--primary-color); font-weight: 400;
    }

    /* ===== Tabla ===== */
    .gastos-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .gastos-table th {
        padding: 0.55rem 1rem; text-align: left;
        font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.8px;
        color: var(--text-light); font-weight: 400;
        border-bottom: 1px solid rgba(0,0,0,0.06); background: var(--light-bg);
    }
    .gastos-table td {
        padding: 0.65rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.04);
        color: var(--text-dark); vertical-align: middle;
    }
    .gastos-table tr:last-child td { border-bottom: none; }
    .gastos-table tbody tr:hover td { background: rgba(201,169,110,0.04); }
    .gastos-table .tfoot-row td {
        background: var(--light-bg); font-weight: 500;
        border-top: 1px solid rgba(0,0,0,0.08); border-bottom: none;
    }

    /* ===== Pills ===== */
    .cat-pill, .tipo-pill {
        display: inline-block; padding: 0.16rem 0.55rem;
        font-size: 0.67rem; font-weight: 500; letter-spacing: 0.4px;
        text-transform: uppercase; border-radius: 2px;
    }
    .cat-alimentacion    { background: rgba(255,193,7,0.12);  color: #856404; }
    .cat-limpieza        { background: rgba(23,162,184,0.12); color: #0c6778; }
    .cat-papeleria       { background: rgba(102,16,242,0.1);  color: #510fa0; }
    .cat-servicios_basicos { background: rgba(253,126,20,0.12); color: #9f4a00; }
    .cat-mantenimiento   { background: rgba(108,117,125,0.12); color: #495057; }
    .cat-compras         { background: rgba(40,167,69,0.12);  color: #145523; }
    .cat-otros           { background: rgba(0,0,0,0.06);      color: var(--text-light); }
    .tipo-sueldo   { background: rgba(40,167,69,0.12);  color: #145523; }
    .tipo-comision { background: rgba(201,169,110,0.18); color: #7a5c0f; }
    .tipo-adelanto { background: rgba(253,126,20,0.12); color: #9f4a00; }
    .tipo-bono     { background: rgba(0,123,255,0.1);   color: #0056b3; }

    /* ===== Empty state ===== */
    .empty-state { padding: 2.5rem 2rem; text-align: center; color: var(--text-light); font-size: 0.86rem; font-weight: 300; font-style: italic; }
    .empty-state-icon { font-size: 1.8rem; margin-bottom: 0.4rem; opacity: 0.35; }

    /* ===== Modal ===== */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.45); z-index: 1000;
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: var(--white); width: 100%; max-width: 520px;
        border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        padding: 2rem; position: relative; max-height: 90vh; overflow-y: auto;
    }
    .modal-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem; color: var(--primary-color);
        margin-bottom: 1.25rem; font-weight: 400;
    }
    .modal-close {
        position: absolute; top: 1rem; right: 1rem;
        background: none; border: none; font-size: 1.2rem;
        cursor: pointer; color: var(--text-light); line-height: 1;
    }
    .modal-close:hover { color: var(--text-dark); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }

    /* ===== Success alert ===== */
    .alert-success-inline {
        background: rgba(40,167,69,0.08); border: 1px solid rgba(40,167,69,0.25);
        color: #145523; padding: 0.6rem 1rem; font-size: 0.82rem;
        margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;
    }

    /* ===== Delete form inline ===== */
    .btn-delete-row {
        background: none; border: none; color: var(--text-light);
        cursor: pointer; font-size: 0.78rem; padding: 0.2rem 0.4rem;
        transition: var(--transition);
    }
    .btn-delete-row:hover { color: #dc3545; }
</style>
@endpush

@section('content')

{{-- ===== Barra superior: tabs de período ===== --}}
<div class="gastos-top-bar">
    {{-- Selector de sucursal --}}
    @include('admin.partials.sucursal_badge')

    <div class="periodo-tabs">
        @foreach(['diario'=>'Diario','mensual'=>'Mensual','trimestral'=>'Trimestral','rango'=>'Por rango'] as $p => $label)
        <a href="{{ route('admin.gastos.index', array_merge(request()->except('periodo'), ['periodo' => $p])) }}"
           class="periodo-tab {{ $periodo === $p ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- ===== Panel de rango ===== --}}
@if($periodo === 'rango')
<form method="GET" action="{{ route('admin.gastos.index') }}" class="rango-panel">
    <input type="hidden" name="periodo" value="rango">
    <input type="hidden" name="seccion" value="{{ $seccion }}">
    <div class="form-group">
        <label>Desde</label>
        <input type="date" name="desde" value="{{ isset($desde) ? $desde->format('Y-m-d') : now()->format('Y-m-d') }}"
               class="form-control" required>
    </div>
    <div class="form-group">
        <label>Hasta</label>
        <input type="date" name="hasta" value="{{ isset($hasta) ? $hasta->format('Y-m-d') : now()->format('Y-m-d') }}"
               class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary" style="white-space:nowrap;">Consultar</button>
    @if(isset($labelPeriodo))
    <span style="margin-left:auto;font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--text-light);align-self:center;">
        {{ $labelPeriodo }}
        <span style="font-size:0.75rem;font-family:'Montserrat',sans-serif;">({{ $diasRango ?? '' }} días)</span>
    </span>
    @endif
</form>
@else
{{-- Navegación prev/next --}}
<div class="periodo-nav">
    @if($periodo === 'diario')
        @php $navBase = array_merge(request()->except('fecha'), ['periodo'=>'diario']); @endphp
        <a href="{{ route('admin.gastos.index', array_merge($navBase, ['fecha'=>$fechaAnterior])) }}" class="periodo-nav-btn">← Anterior</a>
        <div class="periodo-nav-label">{{ $fecha->isoFormat('ddd D MMM YYYY') }}</div>
        <a href="{{ route('admin.gastos.index', array_merge($navBase, ['fecha'=>$fechaSiguiente])) }}"
           class="periodo-nav-btn" style="{{ $fecha->isToday() ? 'opacity:.4;pointer-events:none;' : '' }}">Siguiente →</a>
        @if(!$fecha->isToday())
        <a href="{{ route('admin.gastos.index', $navBase) }}" class="periodo-nav-btn" style="margin-left:.5rem;">Hoy</a>
        @endif
        <form method="GET" action="{{ route('admin.gastos.index') }}" style="display:flex;gap:0.5rem;align-items:center;margin-left:auto;">
            <input type="hidden" name="periodo" value="diario">
            <input type="hidden" name="seccion" value="{{ $seccion }}">
            <input type="date" name="fecha" value="{{ $fecha->format('Y-m-d') }}" class="form-control" style="width:155px;" onchange="this.form.submit()">
        </form>

    @elseif($periodo === 'mensual')
        @php $navBase = array_merge(request()->except('mes','anio'), ['periodo'=>'mensual']); @endphp
        <a href="{{ route('admin.gastos.index', array_merge($navBase, ['mes'=>$mesPrev,'anio'=>$anioPrev])) }}" class="periodo-nav-btn">← Anterior</a>
        <div class="periodo-nav-label">{{ $labelPeriodo }}</div>
        <a href="{{ route('admin.gastos.index', array_merge($navBase, ['mes'=>$mesSig,'anio'=>$anioSig])) }}" class="periodo-nav-btn">Siguiente →</a>
        <form method="GET" action="{{ route('admin.gastos.index') }}" style="display:flex;gap:0.5rem;align-items:center;margin-left:auto;">
            <input type="hidden" name="periodo" value="mensual">
            <input type="hidden" name="seccion" value="{{ $seccion }}">
            <select name="mes" class="form-control" style="width:130px;" onchange="this.form.submit()">
                @foreach($mesesNombres as $n => $nombre)
                    <option value="{{ $n }}" {{ $n == $mes ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="{{ now()->year+1 }}" class="form-control" style="width:80px;" onchange="this.form.submit()">
        </form>

    @elseif($periodo === 'trimestral')
        @php $navBase = array_merge(request()->except('trimestre','anio'), ['periodo'=>'trimestral']); @endphp
        <a href="{{ route('admin.gastos.index', array_merge($navBase, ['trimestre'=>$trimPrev,'anio'=>$anioPrev])) }}" class="periodo-nav-btn">← Anterior</a>
        <div class="periodo-nav-label">{{ $labelPeriodo }}</div>
        <a href="{{ route('admin.gastos.index', array_merge($navBase, ['trimestre'=>$trimSig,'anio'=>$anioSig])) }}" class="periodo-nav-btn">Siguiente →</a>
        <form method="GET" action="{{ route('admin.gastos.index') }}" style="display:flex;gap:0.5rem;align-items:center;margin-left:auto;">
            <input type="hidden" name="periodo" value="trimestral">
            <input type="hidden" name="seccion" value="{{ $seccion }}">
            <select name="trimestre" class="form-control" style="width:140px;" onchange="this.form.submit()">
                @foreach([1=>'T1 (Ene-Mar)',2=>'T2 (Abr-Jun)',3=>'T3 (Jul-Sep)',4=>'T4 (Oct-Dic)'] as $t => $tn)
                    <option value="{{ $t }}" {{ $t == $trimestre ? 'selected' : '' }}>{{ $tn }}</option>
                @endforeach
            </select>
            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="{{ now()->year+1 }}" class="form-control" style="width:80px;" onchange="this.form.submit()">
        </form>
    @endif
</div>
@endif

{{-- ===== KPI Resumen ===== --}}
<div class="kpi-grid-gastos">
    <div class="kpi-card">
        <div class="kpi-card-label">Total gastos generales</div>
        <div class="kpi-card-value danger">Bs. {{ number_format($totalGastos, 2) }}</div>
        <div class="kpi-card-sub">{{ $gastos->count() }} registro(s)</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-card-label">Total sueldos y comisiones</div>
        <div class="kpi-card-value danger">Bs. {{ number_format($totalPagos, 2) }}</div>
        <div class="kpi-card-sub">{{ $pagos->count() }} pago(s)</div>
    </div>
</div>

{{-- ===== Tabs de sección ===== --}}
<div class="seccion-tabs">
    <a href="{{ route('admin.gastos.index', array_merge(request()->all(), ['seccion' => 'gastos'])) }}"
       class="seccion-tab {{ $seccion === 'gastos' ? 'active' : '' }}">Gastos Generales</a>
    <a href="{{ route('admin.gastos.index', array_merge(request()->all(), ['seccion' => 'sueldos'])) }}"
       class="seccion-tab {{ $seccion === 'sueldos' ? 'active' : '' }}">Sueldos y Comisiones</a>
</div>

{{-- ===== Alertas de éxito ===== --}}
@if(session('success_gastos'))
<div class="alert-success-inline">✓ {{ session('success_gastos') }}</div>
@endif
@if(session('success_sueldos'))
<div class="alert-success-inline">✓ {{ session('success_sueldos') }}</div>
@endif

{{-- ========================================================== --}}
{{-- SECCIÓN: GASTOS GENERALES                                   --}}
{{-- ========================================================== --}}
@if($seccion === 'gastos')
<div class="gastos-section">
    <div class="gastos-section-header">
        <span class="gastos-section-title">Gastos Generales</span>
        <button class="btn btn-primary" style="font-size:0.78rem;padding:0.4rem 1rem;"
                onclick="document.getElementById('modal-gasto').classList.add('open')">
            + Registrar gasto
        </button>
    </div>

    @if($gastos->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">🧾</div>
            No hay gastos registrados en este período.
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="gastos-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Método pago</th>
                    <th style="text-align:right;">Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($gastos as $g)
                <tr>
                    <td style="white-space:nowrap;color:var(--text-light);font-size:0.8rem;">
                        {{ $g->fecha->format('d/m/Y') }}
                    </td>
                    <td>
                        {{ $g->descripcion }}
                        @if($g->notas)
                            <div style="font-size:0.73rem;color:var(--text-light);margin-top:0.1rem;">{{ $g->notas }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="cat-pill cat-{{ $g->categoria }}">
                            {{ \App\Models\Gasto::categorias()[$g->categoria] ?? $g->categoria }}
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-light);">
                        {{ $g->metodo_pago ? ucfirst($g->metodo_pago) : '—' }}
                    </td>
                    <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.05rem;color:#dc3545;">
                        Bs. {{ number_format($g->monto, 2) }}
                    </td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('admin.gastos.destroyGasto', $g) }}"
                              onsubmit="return confirm('¿Eliminar este gasto?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete-row" title="Eliminar">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="tfoot-row">
                    <td colspan="4" style="text-align:right;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.6px;">
                        Total
                    </td>
                    <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:#dc3545;">
                        Bs. {{ number_format($totalGastos, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>
@endif

{{-- ========================================================== --}}
{{-- SECCIÓN: SUELDOS Y COMISIONES                               --}}
{{-- ========================================================== --}}
@if($seccion === 'sueldos')
<div class="gastos-section">
    <div class="gastos-section-header">
        <span class="gastos-section-title">Sueldos y Comisiones</span>
        <button class="btn btn-primary" style="font-size:0.78rem;padding:0.4rem 1rem;"
                onclick="document.getElementById('modal-pago').classList.add('open')">
            + Registrar pago
        </button>
    </div>

    @if($pagos->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">💼</div>
            No hay pagos registrados en este período.
        </div>
    @else
    <div style="overflow-x:auto;">
        <table class="gastos-table">
            <thead>
                <tr>
                    <th>Fecha pago</th>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Período</th>
                    <th>Método</th>
                    <th style="text-align:right;">Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $p)
                <tr>
                    <td style="white-space:nowrap;color:var(--text-light);font-size:0.8rem;">
                        {{ $p->fecha_pago->format('d/m/Y') }}
                    </td>
                    <td>
                        {{ $p->empleado->nombre }} {{ $p->empleado->apellido }}
                        @if($p->notas)
                            <div style="font-size:0.73rem;color:var(--text-light);margin-top:0.1rem;">{{ $p->notas }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="tipo-pill tipo-{{ $p->tipo }}">
                            {{ \App\Models\PagoSueldo::tipos()[$p->tipo] ?? $p->tipo }}
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-light);">
                        @if($p->periodo_desde && $p->periodo_hasta)
                            {{ $p->periodo_desde->format('d/m') }} — {{ $p->periodo_hasta->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-light);">
                        {{ $p->metodo_pago ? ucfirst($p->metodo_pago) : '—' }}
                    </td>
                    <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.05rem;color:#dc3545;">
                        Bs. {{ number_format($p->monto, 2) }}
                    </td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('admin.gastos.destroyPago', $p) }}"
                              onsubmit="return confirm('¿Eliminar este pago?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete-row" title="Eliminar">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="tfoot-row">
                    <td colspan="5" style="text-align:right;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.6px;">
                        Total
                    </td>
                    <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:#dc3545;">
                        Bs. {{ number_format($totalPagos, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>
@endif


{{-- ========================================================== --}}
{{-- MODAL: Registrar Gasto General                              --}}
{{-- ========================================================== --}}
<div class="modal-overlay" id="modal-gasto" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('modal-gasto').classList.remove('open')">✕</button>
        <div class="modal-title">Registrar Gasto General</div>

        <form method="POST" action="{{ route('admin.gastos.storeGasto') }}">
            @csrf

            {{-- Preservar params de navegación --}}
            @foreach(request()->except('_token') as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <input type="hidden" name="seccion" value="gastos">

            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Descripción *</label>
                <input type="text" name="descripcion" class="form-control"
                       placeholder="Ej: Botellas de refresco, escobas…" required
                       value="{{ old('descripcion') }}">
            </div>

            <div class="form-row" style="margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Monto (Bs.) *</label>
                    <input type="number" name="monto" class="form-control" step="0.01" min="0.01"
                           required value="{{ old('monto') }}" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha" class="form-control" required
                           value="{{ old('fecha', now()->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-row" style="margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select name="categoria" class="form-control" required>
                        @foreach(\App\Models\Gasto::categorias() as $val => $label)
                            <option value="{{ $val }}" {{ old('categoria') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Método de pago</label>
                    <select name="metodo_pago" class="form-control">
                        <option value="">— Sin especificar —</option>
                        <option value="efectivo"     {{ old('metodo_pago') === 'efectivo'     ? 'selected' : '' }}>Efectivo</option>
                        <option value="transferencia"{{ old('metodo_pago') === 'transferencia'? 'selected' : '' }}>Transferencia</option>
                        <option value="tarjeta"      {{ old('metodo_pago') === 'tarjeta'      ? 'selected' : '' }}>Tarjeta</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label">Notas</label>
                <textarea name="notas" class="form-control" rows="2" placeholder="Opcional…">{{ old('notas') }}</textarea>
            </div>

            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-gasto').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar gasto</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================== --}}
{{-- MODAL: Registrar Pago de Sueldo / Comisión                  --}}
{{-- ========================================================== --}}
<div class="modal-overlay" id="modal-pago" onclick="if(event.target===this)cerrarModalPago()">
    <div class="modal-box" style="max-width:920px;width:96vw;padding:0;overflow:hidden;max-height:92vh;display:flex;flex-direction:column;">

        {{-- ===== PASO 1: Contraseña ===== --}}
        <div id="pago-paso1" style="padding:2.5rem 2.5rem 2rem;flex:1;">
            <div style="text-align:center;margin-bottom:1.75rem;">
                <div style="width:56px;height:56px;background:var(--light-bg);border:1px solid rgba(0,0,0,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;">🔒</div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:var(--primary-color);font-weight:400;letter-spacing:0.3px;">Autorización requerida</div>
                <p style="font-size:0.83rem;color:var(--text-light);margin-top:0.5rem;max-width:320px;margin-left:auto;margin-right:auto;line-height:1.5;">
                    Para registrar un pago de sueldo o comisión ingresa la contraseña de administrador.
                </p>
            </div>

            <div style="max-width:320px;margin:0 auto;">
                <input type="password" id="pago-password" class="form-control"
                       placeholder="Contraseña" autocomplete="current-password"
                       style="text-align:center;letter-spacing:0.1em;font-size:1rem;height:46px;margin-bottom:0.5rem;">
                <div id="pago-pass-error" style="min-height:1.3rem;font-size:0.8rem;color:#842029;text-align:center;margin-bottom:0.75rem;"></div>

                <button type="button" id="btn-verificar-pass" class="btn btn-primary"
                        style="width:100%;height:44px;font-size:0.87rem;letter-spacing:0.5px;">
                    Continuar →
                </button>
                <button type="button" onclick="cerrarModalPago()"
                        style="display:block;width:100%;margin-top:0.65rem;background:none;border:none;font-size:0.79rem;color:var(--text-light);cursor:pointer;padding:0.3rem;transition:var(--transition);">
                    Cancelar
                </button>
            </div>
        </div>

        {{-- ===== PASO 2: Formulario ===== --}}
        <div id="pago-paso2" style="display:none;flex-direction:column;flex:1;overflow:hidden;">

            {{-- Encabezado --}}
            <div style="padding:1rem 1.75rem;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;align-items:center;justify-content:space-between;background:var(--light-bg);flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:0.65rem;">
                    <span style="width:26px;height:26px;background:#d1fae5;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;">✓</span>
                    <div>
                        <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:var(--primary-color);line-height:1.2;">Registrar Pago de Sueldo / Comisión</div>
                        <div style="font-size:0.65rem;color:#145523;letter-spacing:0.5px;text-transform:uppercase;margin-top:0.1rem;">Autorizado</div>
                    </div>
                </div>
                <button onclick="cerrarModalPago()" style="background:none;border:none;font-size:1.05rem;cursor:pointer;color:var(--text-light);padding:0.25rem 0.4rem;line-height:1;">✕</button>
            </div>

            {{-- Cuerpo con scroll --}}
            <div style="padding:1.25rem 1.75rem 1.5rem;overflow-y:auto;flex:1;">
                <form method="POST" action="{{ route('admin.gastos.storePago') }}" id="form-pago">
                    @csrf
                    @foreach(request()->except('_token') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <input type="hidden" name="seccion" value="sueldos">
                    <input type="hidden" name="password_confirmacion" id="hidden-password">

                    {{-- Empleado --}}
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label class="form-label">Empleado *</label>
                        <select name="empleado_id" id="pago-empleado" class="form-control" required>
                            <option value="">— Seleccionar empleado —</option>
                            @foreach($empleados as $emp)
                                <option value="{{ $emp->id }}" {{ old('empleado_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->nombre }} {{ $emp->apellido }}{{ $emp->cargo ? ' ('.$emp->cargo.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Consultar servicios --}}
                    <div style="background:var(--light-bg);border:1px solid rgba(0,0,0,0.07);padding:0.85rem 1rem;margin-bottom:1rem;">
                        <div style="font-size:0.67rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);margin-bottom:0.6rem;">
                            Consultar servicios realizados en el período
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:0.75rem;align-items:flex-end;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Desde</label>
                                <input type="date" id="pago-desde" name="periodo_desde" class="form-control"
                                       value="{{ old('periodo_desde', now()->startOfMonth()->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Hasta</label>
                                <input type="date" id="pago-hasta" name="periodo_hasta" class="form-control"
                                       value="{{ old('periodo_hasta', now()->format('Y-m-d')) }}">
                            </div>
                            <button type="button" id="btn-consultar-servicios" class="btn btn-secondary"
                                    style="white-space:nowrap;height:38px;">Consultar</button>
                        </div>
                    </div>

                    {{-- Tabla de servicios --}}
                    <div id="servicios-resultado" style="margin-bottom:1rem;display:none;">
                        <div style="font-size:0.67rem;text-transform:uppercase;letter-spacing:0.7px;color:var(--text-light);margin-bottom:0.5rem;">Servicios encontrados</div>
                        <div id="servicios-loading" style="display:none;text-align:center;padding:0.75rem;font-size:0.82rem;color:var(--text-light);">Cargando…</div>
                        <div id="servicios-tabla-wrap" style="overflow-x:auto;border:1px solid rgba(0,0,0,0.07);max-height:220px;overflow-y:auto;">
                            <table class="gastos-table" style="font-size:0.79rem;">
                                <thead style="position:sticky;top:0;z-index:1;">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th style="text-align:right;">Precio</th>
                                        <th style="text-align:right;">% Com.</th>
                                        <th style="text-align:right;">Comisión</th>
                                    </tr>
                                </thead>
                                <tbody id="servicios-tbody"></tbody>
                                <tfoot>
                                    <tr style="background:var(--light-bg);font-weight:500;">
                                        <td colspan="3" style="font-size:0.71rem;text-transform:uppercase;letter-spacing:0.5px;padding:0.45rem 1rem;">
                                            Total (<span id="servicios-count">0</span> servicios)
                                        </td>
                                        <td style="text-align:right;padding:0.45rem 1rem;font-family:'Cormorant Garamond',serif;">
                                            Bs. <span id="total-precio">0.00</span>
                                        </td>
                                        <td></td>
                                        <td style="text-align:right;padding:0.45rem 1rem;font-family:'Cormorant Garamond',serif;color:var(--primary-color);">
                                            Bs. <span id="total-comisiones">0.00</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="servicios-empty" style="display:none;text-align:center;padding:0.75rem;font-size:0.82rem;color:var(--text-light);font-style:italic;">
                            No hay servicios completados en este período.
                        </div>
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.55rem;">
                            <span style="font-size:0.71rem;color:var(--text-light);">Usar total de comisiones como monto:</span>
                            <button type="button" id="btn-usar-comisiones" class="btn btn-secondary"
                                    style="font-size:0.72rem;padding:0.22rem 0.65rem;">
                                Aplicar Bs. <span id="btn-comision-val">0.00</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tipo / Monto / Fecha / Método --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0.9rem;margin-bottom:0.9rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Tipo *</label>
                            <select name="tipo" class="form-control" required>
                                @foreach(\App\Models\PagoSueldo::tipos() as $tval => $tlabel)
                                    <option value="{{ $tval }}" {{ old('tipo') === $tval ? 'selected' : '' }}>{{ $tlabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Monto (Bs.) *</label>
                            <input type="number" name="monto" id="pago-monto" class="form-control"
                                   step="0.01" min="0.01" required value="{{ old('monto') }}" placeholder="0.00">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Fecha de pago *</label>
                            <input type="date" name="fecha_pago" id="pago-fecha" class="form-control" required
                                   value="{{ old('fecha_pago', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Método</label>
                            <select name="metodo_pago" class="form-control">
                                <option value="">— Sin especificar —</option>
                                <option value="efectivo"      {{ old('metodo_pago') === 'efectivo'      ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ old('metodo_pago') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:1.25rem;">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="2" placeholder="Opcional…">{{ old('notas') }}</textarea>
                    </div>

                    <div style="display:flex;gap:0.75rem;justify-content:flex-end;padding-top:0.75rem;border-top:1px solid rgba(0,0,0,0.06);">
                        <button type="button" onclick="cerrarModalPago()"
                                style="background:none;border:1px solid rgba(0,0,0,0.15);color:var(--text-light);padding:0.5rem 1.2rem;cursor:pointer;font-size:0.82rem;">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1.5rem;">
                            Guardar pago
                        </button>
                    </div>
                </form>
            </div>{{-- /cuerpo scroll --}}
        </div>{{-- /paso2 --}}

    </div>{{-- /modal-box --}}
</div>{{-- /modal-overlay --}}

<script>
function cerrarModalPago() {
    document.getElementById('modal-pago').classList.remove('open');
    setTimeout(function() {
        document.getElementById('pago-paso1').style.display = 'block';
        document.getElementById('pago-paso2').style.display = 'none';
        document.getElementById('pago-password').value = '';
        document.getElementById('pago-pass-error').textContent = '';
    }, 200);
}

document.addEventListener('DOMContentLoaded', function () {
    var urlServicios = '{{ route('admin.gastos.serviciosEmpleado') }}';
    var urlVerificar = '{{ route('admin.gastos.verificarPassword') }}';
    var csrfToken    = '{{ csrf_token() }}';

    function fmt(n) { return parseFloat(n).toFixed(2); }

    // ── Verificación de contraseña ───────────────────────────────
    var btnVerificar = document.getElementById('btn-verificar-pass');
    var passInput    = document.getElementById('pago-password');
    var passError    = document.getElementById('pago-pass-error');

    function verificarContrasena() {
        var pass = passInput.value;
        if (!pass) { passError.textContent = 'Ingresa la contraseña.'; return; }
        btnVerificar.disabled = true;
        btnVerificar.textContent = 'Verificando…';
        passError.textContent = '';

        fetch(urlVerificar, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ password: pass })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btnVerificar.disabled = false;
            btnVerificar.textContent = 'Continuar →';
            if (data.ok) {
                document.getElementById('hidden-password').value = pass;
                document.getElementById('pago-paso1').style.display = 'none';
                document.getElementById('pago-paso2').style.display = 'flex';
            } else {
                passError.textContent = data.message || 'Contraseña incorrecta.';
                passInput.select();
            }
        })
        .catch(function() {
            btnVerificar.disabled = false;
            btnVerificar.textContent = 'Continuar →';
            passError.textContent = 'Error de conexión. Intenta de nuevo.';
        });
    }

    btnVerificar.addEventListener('click', verificarContrasena);
    passInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); verificarContrasena(); }
    });

    // ── Consulta de servicios ────────────────────────────────────
    function consultarServicios() {
        var empleadoId = document.getElementById('pago-empleado').value;
        var desde      = document.getElementById('pago-desde').value;
        var hasta      = document.getElementById('pago-hasta').value;
        if (!empleadoId || !desde || !hasta) return;

        var resultado = document.getElementById('servicios-resultado');
        var loading   = document.getElementById('servicios-loading');
        var tablaWrap = document.getElementById('servicios-tabla-wrap');
        var emptyMsg  = document.getElementById('servicios-empty');
        var tbody     = document.getElementById('servicios-tbody');

        resultado.style.display = 'block';
        loading.style.display   = 'block';
        tablaWrap.style.display = 'none';
        emptyMsg.style.display  = 'none';

        fetch(urlServicios + '?empleado_id=' + empleadoId + '&desde=' + desde + '&hasta=' + hasta, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            loading.style.display = 'none';
            if (data.cantidad === 0) {
                emptyMsg.style.display = 'block';
                document.getElementById('servicios-count').textContent  = '0';
                document.getElementById('total-precio').textContent     = '0.00';
                document.getElementById('total-comisiones').textContent = '0.00';
                document.getElementById('btn-comision-val').textContent = '0.00';
                return;
            }
            tbody.innerHTML = data.servicios.map(function(s) {
                return '<tr>'
                    + '<td style="white-space:nowrap;color:var(--text-light);">' + s.fecha + '</td>'
                    + '<td>' + s.cliente + '</td>'
                    + '<td>' + s.servicio + '</td>'
                    + '<td style="text-align:right;">Bs. ' + fmt(s.precio_neto) + '</td>'
                    + '<td style="text-align:right;color:var(--text-light);">' + fmt(s.pct_comision) + '%</td>'
                    + '<td style="text-align:right;color:var(--primary-color);font-weight:500;">Bs. ' + fmt(s.monto_comision) + '</td>'
                    + '</tr>';
            }).join('');
            document.getElementById('servicios-count').textContent     = data.cantidad;
            document.getElementById('total-precio').textContent        = fmt(data.total_precio);
            document.getElementById('total-comisiones').textContent    = fmt(data.total_comisiones);
            document.getElementById('btn-comision-val').textContent    = fmt(data.total_comisiones);
            tablaWrap.style.display = 'block';
        })
        .catch(function() {
            loading.style.display = 'none';
            emptyMsg.style.display = 'block';
            emptyMsg.textContent = 'Error al consultar servicios.';
        });
    }

    document.getElementById('btn-consultar-servicios').addEventListener('click', consultarServicios);
    document.getElementById('btn-usar-comisiones').addEventListener('click', function() {
        document.getElementById('pago-monto').value = document.getElementById('total-comisiones').textContent;
    });
});
</script>

@endsection
