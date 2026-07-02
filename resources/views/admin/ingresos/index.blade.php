@extends('admin.layouts.app')

@section('title', 'Ingresos')
@section('page-title', 'Ingresos')

@push('styles')
<style>
    /* ===== Fila superior: tabs + filtro tipo pago ===== */
    .ingresos-top-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    /* ===== Tabs de período ===== */
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

    /* ===== Filtro tipo de pago ===== */
    .tipo-filter-strip {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        margin-left: auto;
        align-items: center;
    }
    .tipo-filter-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-light);
        margin-right: 0.15rem;
    }
    .tipo-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.8rem;
        font-size: 0.72rem;
        font-weight: 400;
        letter-spacing: 0.4px;
        border: 1px solid rgba(0,0,0,0.12);
        background: var(--white);
        color: var(--text-light);
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
    }
    .tipo-chip:hover { border-color: var(--accent-color); color: var(--text-dark); }
    .tipo-chip.active { border-color: transparent; color: #fff; font-weight: 500; }
    .tipo-chip.active.chip-all      { background: var(--primary-color); }
    .tipo-chip.active.chip-efectivo { background: #28a745; }
    .tipo-chip.active.chip-tarjeta  { background: #007bff; }
    .tipo-chip.active.chip-qr       { background: #c9a96e; color: #fff; }
    .tipo-chip.active.chip-sin      { background: #888; }
    .tipo-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .dot-efectivo { background: #28a745; }
    .dot-tarjeta  { background: #007bff; }
    .dot-qr       { background: #c9a96e; }
    .dot-sin      { background: #bbb; }

    /* ===== Navegación de período ===== */
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

    /* ===== Panel de rango ===== */
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
    .kpi-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 960px) { .kpi-grid { grid-template-columns: 1fr 1fr; } .kpi-total { grid-column: 1/-1; } }
    .kpi-card { background: var(--white); border: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem; }
    .kpi-card-label {
        font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-light); margin-bottom: 0.35rem;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .kpi-card-value { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: var(--secondary-color); line-height: 1; }
    .kpi-total .kpi-card-value { font-size: 2.3rem; color: var(--primary-color); }
    .kpi-card-sub { font-size: 0.7rem; color: var(--text-light); margin-top: 0.2rem; }
    .kpi-bar { display: flex; gap: 2px; height: 5px; margin-top: 0.6rem; overflow: hidden; background: rgba(0,0,0,0.04); }
    .kpi-bar-seg { height: 100%; }

    /* ===== Sección tabla ===== */
    .ingresos-section { background: var(--white); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 1.25rem; }
    .ingresos-section-header {
        padding: 0.85rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }
    .ingresos-section-title {
        font-family: 'Cormorant Garamond', serif; font-size: 1.05rem;
        color: var(--primary-color); font-weight: 400;
    }

    /* ===== Tabla ===== */
    .ingresos-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
    .ingresos-table th {
        padding: 0.55rem 1rem; text-align: left;
        font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.8px;
        color: var(--text-light); font-weight: 400;
        border-bottom: 1px solid rgba(0,0,0,0.06); background: var(--light-bg);
    }
    .ingresos-table td {
        padding: 0.65rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.04);
        color: var(--text-dark); vertical-align: middle;
    }
    .ingresos-table tr:last-child td { border-bottom: none; }
    .ingresos-table tbody tr:hover td { background: rgba(201,169,110,0.04); }
    .ingresos-table .tfoot-row td {
        background: var(--light-bg); font-weight: 500;
        border-top: 1px solid rgba(0,0,0,0.08); border-bottom: none;
    }

    /* ===== Barra visual por tipo ===== */
    .bar-visual { display: flex; gap: 1px; height: 18px; min-width: 90px; overflow: hidden; background: rgba(0,0,0,0.04); }
    .bar-seg { height: 100%; }
    .bar-ef  { background: #28a745; }
    .bar-tar { background: #007bff; }
    .bar-qr  { background: #c9a96e; }
    .bar-sin { background: #bbb; }

    /* ===== Pill tipo de pago ===== */
    .pago-pill { display: inline-block; padding: 0.16rem 0.5rem; font-size: 0.67rem; font-weight: 500; letter-spacing: 0.4px; text-transform: uppercase; border-radius: 2px; }
    .pago-efectivo { background: rgba(40,167,69,0.1);   color: #1a7a35; }
    .pago-tarjeta  { background: rgba(0,123,255,0.1);   color: #0056b3; }
    .pago-qr       { background: rgba(201,169,110,0.18); color: #7a5c0f; }
    .pago-sin      { background: rgba(0,0,0,0.05);      color: var(--text-light); }

    /* ===== Empty state ===== */
    .empty-state { padding: 2.5rem 2rem; text-align: center; color: var(--text-light); font-size: 0.86rem; font-weight: 300; font-style: italic; }
    .empty-state-icon { font-size: 1.8rem; margin-bottom: 0.4rem; opacity: 0.35; }

    /* ===== Alerta filtro activo ===== */
    .filtro-activo-banner {
        display: flex; align-items: center; gap: 0.6rem;
        background: rgba(201,169,110,0.1); border: 1px solid rgba(201,169,110,0.3);
        padding: 0.45rem 1rem; font-size: 0.78rem; color: var(--secondary-color);
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')

{{-- ===== Barra superior: tabs + filtro tipo pago ===== --}}
<div class="ingresos-top-bar">

    {{-- Selector de sucursal --}}
    @include('admin.partials.sucursal_badge')

    {{-- Tabs de período --}}
    <div class="periodo-tabs">
        @foreach(['diario'=>'Diario','mensual'=>'Mensual','trimestral'=>'Trimestral','rango'=>'Por rango'] as $p => $label)
        <a href="{{ route('admin.ingresos.index', array_merge(request()->except('periodo'), ['periodo' => $p])) }}"
           class="periodo-tab {{ $periodo === $p ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- Filtro tipo de pago --}}
    <div class="tipo-filter-strip">
        <span class="tipo-filter-label">Tipo de pago:</span>
        @php
            $filtroBase = request()->except('tipo_pago');
            $tipos = ['' => ['label'=>'Todos','css'=>'chip-all'], 'efectivo' => ['label'=>'Efectivo','css'=>'chip-efectivo'], 'tarjeta' => ['label'=>'Tarjeta','css'=>'chip-tarjeta'], 'qr' => ['label'=>'QR','css'=>'chip-qr'], 'sin_especificar' => ['label'=>'Sin esp.','css'=>'chip-sin']];
        @endphp
        @foreach($tipos as $val => $cfg)
        <a href="{{ route('admin.ingresos.index', array_merge($filtroBase, $val !== '' ? ['tipo_pago' => $val] : [])) }}"
           class="tipo-chip {{ $cfg['css'] }} {{ $tipoPagoFiltro === $val ? 'active' : '' }}">
            @if($val !== '') <span class="tipo-dot dot-{{ $val === 'sin_especificar' ? 'sin' : $val }}"></span> @endif
            {{ $cfg['label'] }}
        </a>
        @endforeach
    </div>
</div>

{{-- Banner cuando hay filtro activo --}}
@if($tipoPagoFiltro !== '')
<div class="filtro-activo-banner">
    <span>Filtrando por:</span>
    <strong>{{ ['efectivo'=>'Efectivo','tarjeta'=>'Tarjeta','qr'=>'QR','sin_especificar'=>'Sin especificar'][$tipoPagoFiltro] }}</strong>
    <a href="{{ route('admin.ingresos.index', request()->except('tipo_pago')) }}"
       style="margin-left:auto;font-size:0.75rem;color:var(--text-light);text-decoration:none;">✕ Quitar filtro</a>
</div>
@endif

{{-- ===== PANEL DE RANGO (solo visible en modo rango) ===== --}}
@if($periodo === 'rango')
<form method="GET" action="{{ route('admin.ingresos.index') }}" class="rango-panel">
    <input type="hidden" name="periodo" value="rango">
    @if($tipoPagoFiltro !== '') <input type="hidden" name="tipo_pago" value="{{ $tipoPagoFiltro }}"> @endif

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
        {{ $labelPeriodo }} <span style="font-size:0.75rem;font-family:'Montserrat',sans-serif;">({{ isset($diasRango) ? $diasRango : '' }} días)</span>
    </span>
    @endif
</form>
@else
{{-- ===== Navegación prev/next para los otros modos ===== --}}
<div class="periodo-nav">
    @if($periodo === 'diario')
        @php $navBase = array_merge(request()->except('fecha'), ['periodo'=>'diario']); @endphp
        <a href="{{ route('admin.ingresos.index', array_merge($navBase, ['fecha'=>$fechaAnterior])) }}" class="periodo-nav-btn">← Anterior</a>
        <div class="periodo-nav-label">{{ $fecha->isoFormat('ddd D MMM YYYY') }}</div>
        <a href="{{ route('admin.ingresos.index', array_merge($navBase, ['fecha'=>$fechaSiguiente])) }}"
           class="periodo-nav-btn" style="{{ $fecha->isToday() ? 'opacity:.4;pointer-events:none;' : '' }}">Siguiente →</a>
        @if(!$fecha->isToday())
        <a href="{{ route('admin.ingresos.index', array_merge($navBase)) }}" class="periodo-nav-btn" style="margin-left:.5rem;">Hoy</a>
        @endif
        <form method="GET" action="{{ route('admin.ingresos.index') }}" style="display:flex;gap:0.5rem;align-items:center;margin-left:auto;">
            <input type="hidden" name="periodo" value="diario">
            @if($tipoPagoFiltro !== '') <input type="hidden" name="tipo_pago" value="{{ $tipoPagoFiltro }}"> @endif
            <input type="date" name="fecha" value="{{ $fecha->format('Y-m-d') }}" class="form-control" style="width:155px;" onchange="this.form.submit()">
        </form>

    @elseif($periodo === 'mensual')
        @php $navBase = array_merge(request()->except('mes','anio'), ['periodo'=>'mensual']); @endphp
        <a href="{{ route('admin.ingresos.index', array_merge($navBase, ['mes'=>$mesPrev,'anio'=>$anioPrev])) }}" class="periodo-nav-btn">← Anterior</a>
        <div class="periodo-nav-label">{{ $labelPeriodo }}</div>
        <a href="{{ route('admin.ingresos.index', array_merge($navBase, ['mes'=>$mesSig,'anio'=>$anioSig])) }}" class="periodo-nav-btn">Siguiente →</a>
        <form method="GET" action="{{ route('admin.ingresos.index') }}" style="display:flex;gap:0.5rem;align-items:center;margin-left:auto;">
            <input type="hidden" name="periodo" value="mensual">
            @if($tipoPagoFiltro !== '') <input type="hidden" name="tipo_pago" value="{{ $tipoPagoFiltro }}"> @endif
            <select name="mes" class="form-control" style="width:130px;" onchange="this.form.submit()">
                @foreach($mesesNombres as $n => $nombre)
                    <option value="{{ $n }}" {{ $n == $mes ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="{{ now()->year+1 }}" class="form-control" style="width:80px;" onchange="this.form.submit()">
        </form>

    @elseif($periodo === 'trimestral')
        @php $navBase = array_merge(request()->except('trimestre','anio'), ['periodo'=>'trimestral']); @endphp
        <a href="{{ route('admin.ingresos.index', array_merge($navBase, ['trimestre'=>$trimPrev,'anio'=>$anioPrev])) }}" class="periodo-nav-btn">← Anterior</a>
        <div class="periodo-nav-label">{{ $labelPeriodo }}</div>
        <a href="{{ route('admin.ingresos.index', array_merge($navBase, ['trimestre'=>$trimSig,'anio'=>$anioSig])) }}" class="periodo-nav-btn">Siguiente →</a>
        <form method="GET" action="{{ route('admin.ingresos.index') }}" style="display:flex;gap:0.5rem;align-items:center;margin-left:auto;">
            <input type="hidden" name="periodo" value="trimestral">
            @if($tipoPagoFiltro !== '') <input type="hidden" name="tipo_pago" value="{{ $tipoPagoFiltro }}"> @endif
            <select name="trimestre" class="form-control" style="width:130px;" onchange="this.form.submit()">
                @foreach([1=>'T1 (Ene-Mar)',2=>'T2 (Abr-Jun)',3=>'T3 (Jul-Sep)',4=>'T4 (Oct-Dic)'] as $t => $tn)
                    <option value="{{ $t }}" {{ $t == $trimestre ? 'selected' : '' }}>{{ $tn }}</option>
                @endforeach
            </select>
            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="{{ now()->year+1 }}" class="form-control" style="width:80px;" onchange="this.form.submit()">
        </form>
    @endif
</div>
@endif

{{-- ===== KPI Cards ===== --}}
<div class="kpi-grid">
    <div class="kpi-card kpi-total">
        <div class="kpi-card-label">Total ingresos</div>
        <div class="kpi-card-value">Bs. {{ number_format($totalGeneral, 2) }}</div>
        <div class="kpi-card-sub">{{ $citas->count() }} citas completadas</div>
        @if($totalGeneral > 0)
        <div class="kpi-bar">
            @foreach(['efectivo'=>'ef','tarjeta'=>'tar','qr'=>'qr','sin_especificar'=>'sin'] as $tipo => $cls)
                @php $pct = $porTipoPago[$tipo]['total'] / $totalGeneral * 100; @endphp
                @if($pct > 0) <div class="kpi-bar-seg bar-{{ $cls }}" style="width:{{ $pct }}%;"></div> @endif
            @endforeach
        </div>
        @endif
    </div>
    @foreach([
        'efectivo'       => ['Efectivo',        '#28a745', 'dot-efectivo'],
        'tarjeta'        => ['Tarjeta',          '#007bff', 'dot-tarjeta'],
        'qr'             => ['QR',               'var(--accent-color)', 'dot-qr'],
        'sin_especificar'=> ['Sin especificar',  'var(--text-light)',   'dot-sin'],
    ] as $tipo => [$nombre, $color, $dotCls])
    <div class="kpi-card {{ $tipoPagoFiltro === $tipo ? 'kpi-card--active' : '' }}"
         style="{{ $tipoPagoFiltro === $tipo ? 'border-color:'.$color.';' : '' }}">
        <div class="kpi-card-label"><span class="tipo-dot {{ $dotCls }}"></span> {{ $nombre }}</div>
        <div class="kpi-card-value" style="font-size:1.45rem;color:{{ $color }};">
            Bs. {{ number_format($porTipoPago[$tipo]['total'], 2) }}
        </div>
        <div class="kpi-card-sub">{{ $porTipoPago[$tipo]['cantidad'] }} citas
            @if($totalGeneral > 0 && $porTipoPago[$tipo]['total'] > 0)
                · {{ number_format($porTipoPago[$tipo]['total'] / $totalGeneral * 100, 1) }}%
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ====================================================================== --}}
{{-- VISTA DIARIA                                                            --}}
{{-- ====================================================================== --}}
@if($periodo === 'diario')
<div class="ingresos-section">
    <div class="ingresos-section-header">
        <span class="ingresos-section-title">Citas del día</span>
        <span style="font-size:0.76rem;color:var(--text-light);">{{ $citas->count() }} registros</span>
    </div>
    @if($citas->isEmpty())
        <div class="empty-state"><div class="empty-state-icon">💰</div>No hay citas completadas para este día.</div>
    @else
    @include('admin.ingresos._tabla_citas', ['citas' => $citas, 'showFecha' => false])
    @endif
</div>
@endif

{{-- ====================================================================== --}}
{{-- VISTA MENSUAL                                                           --}}
{{-- ====================================================================== --}}
@if($periodo === 'mensual')
<div class="ingresos-section">
    <div class="ingresos-section-header">
        <span class="ingresos-section-title">Desglose por día · {{ $labelPeriodo }}</span>
        <span style="font-size:0.76rem;color:var(--text-light);">{{ $citas->count() }} citas · {{ count($porDia) }} días</span>
    </div>
    @if(empty($porDia))
        <div class="empty-state"><div class="empty-state-icon">📅</div>No hay citas completadas en este mes.</div>
    @else
    @include('admin.ingresos._tabla_desglose', ['porPeriodo' => $porDia, 'formatoLabel' => 'dia', 'totalGeneral' => $totalGeneral, 'porTipoPago' => $porTipoPago, 'citas' => $citas, 'periodo' => $periodo, 'anio' => $anio ?? null, 'mes' => $mes ?? null])
    @endif
</div>
@endif

{{-- ====================================================================== --}}
{{-- VISTA TRIMESTRAL                                                        --}}
{{-- ====================================================================== --}}
@if($periodo === 'trimestral')
<div class="ingresos-section">
    <div class="ingresos-section-header">
        <span class="ingresos-section-title">Desglose mensual · {{ $labelPeriodo }}</span>
        <span style="font-size:0.76rem;color:var(--text-light);">{{ $citas->count() }} citas</span>
    </div>
    @if($citas->isEmpty())
        <div class="empty-state"><div class="empty-state-icon">📊</div>No hay citas completadas en este trimestre.</div>
    @else
    @include('admin.ingresos._tabla_desglose', ['porPeriodo' => $porMes, 'formatoLabel' => 'mes', 'totalGeneral' => $totalGeneral, 'porTipoPago' => $porTipoPago, 'citas' => $citas, 'periodo' => $periodo, 'anio' => $anio ?? null])

    {{-- Detalle de citas --}}
    <div style="border-top:1px solid rgba(0,0,0,0.05);">
        <div class="ingresos-section-header" style="padding:.75rem 1.5rem;">
            <span class="ingresos-section-title" style="font-size:.95rem;">Todas las citas del trimestre</span>
        </div>
        @include('admin.ingresos._tabla_citas', ['citas' => $citas, 'showFecha' => true])
    </div>
    @endif
</div>
@endif

{{-- ====================================================================== --}}
{{-- VISTA RANGO                                                             --}}
{{-- ====================================================================== --}}
@if($periodo === 'rango')
@if(isset($desde) && isset($hasta))
<div class="ingresos-section">
    <div class="ingresos-section-header">
        <span class="ingresos-section-title">Desglose por día</span>
        <span style="font-size:0.76rem;color:var(--text-light);">{{ $citas->count() }} citas · {{ count($porDia ?? []) }} días con ingresos</span>
    </div>
    @if(empty($porDia))
        <div class="empty-state"><div class="empty-state-icon">🔍</div>No hay citas completadas en el rango seleccionado.</div>
    @else
    @include('admin.ingresos._tabla_desglose', ['porPeriodo' => $porDia, 'formatoLabel' => 'dia', 'totalGeneral' => $totalGeneral, 'porTipoPago' => $porTipoPago, 'citas' => $citas, 'periodo' => $periodo])

    {{-- Detalle de citas --}}
    <div style="border-top:1px solid rgba(0,0,0,0.05);">
        <div class="ingresos-section-header" style="padding:.75rem 1.5rem;">
            <span class="ingresos-section-title" style="font-size:.95rem;">Todas las citas del período</span>
        </div>
        @include('admin.ingresos._tabla_citas', ['citas' => $citas, 'showFecha' => true])
    </div>
    @endif
</div>
@endif
@endif

@endsection
