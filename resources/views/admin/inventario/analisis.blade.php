@extends('admin.layouts.app')

@section('title', 'Análisis de Inventario')
@section('page-title', 'Análisis de Inventario')

@section('content')

@php
    $temporadas = [
        ''          => 'Todo el año',
        'verano'    => 'Verano (Dic–Feb)',
        'otono'     => 'Otoño (Mar–May)',
        'invierno'  => 'Invierno (Jun–Ago)',
        'primavera' => 'Primavera (Sep–Nov)',
    ];
    $temporadaLabel = $temporadas[$temporada ?? ''] ?? 'Todo el año';
    $periodoTexto = $desde
        ? \Carbon\Carbon::parse($desde)->format('d/m/Y') . ' – ' . \Carbon\Carbon::parse($hasta)->format('d/m/Y')
        : null;
@endphp

    {{-- ── Filtros ── --}}
    <div class="table-container" style="margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.inventario.analisis') }}" class="table-filter">

            {{-- Temporada --}}
            <select name="temporada" class="filter-date" style="min-width:170px;"
                    onchange="this.form.submit()">
                @foreach ($temporadas as $val => $label)
                    <option value="{{ $val }}" {{ ($temporada ?? '') === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            {{-- Año (solo visible si hay temporada) --}}
            @if ($temporada)
            <select name="año" class="filter-date" style="min-width:80px;"
                    onchange="this.form.submit()">
                @foreach (array_reverse($años) as $a)
                    <option value="{{ $a }}" {{ (int)$año === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            @else
            <input type="hidden" name="año" value="{{ $año }}">
            @endif

            {{-- Marca --}}
            <select name="marca" class="filter-date" style="min-width:140px;"
                    onchange="this.form.submit()">
                <option value="">Todas las marcas</option>
                @foreach ($marcas as $m)
                    <option value="{{ $m }}" {{ ($marca ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>

            {{-- Línea --}}
            <select name="linea" class="filter-date" style="min-width:140px;">
                <option value="">Todas las líneas</option>
                @foreach ($lineas as $l)
                    <option value="{{ $l }}" {{ ($linea ?? '') === $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            @if ($temporada || $marca || $linea)
                <a href="{{ route('admin.inventario.analisis') }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif

            <div style="flex:1;"></div>
            <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline btn-sm">Ver Stock</a>
        </form>
    </div>

    {{-- ── Cabecera de periodo ── --}}
    @if ($periodoTexto)
    <div style="margin-bottom:1.25rem; padding:0.75rem 1rem; background:var(--light-bg); border-radius:8px; border-left:3px solid var(--primary); display:flex; align-items:center; gap:0.75rem;">
        <span style="font-weight:600; font-size:0.9rem;">{{ $temporadaLabel }} {{ $año }}</span>
        <span style="color:var(--text-muted); font-size:0.82rem;">{{ $periodoTexto }}</span>
        <span style="font-size:0.78rem; color:var(--text-muted);">— Entradas y salidas filtradas por este período · Stock siempre global</span>
    </div>
    @endif

    {{-- ── Sugerencias de compra ── --}}
    @if ($temporada && $sugerencias->count() > 0)
    @php
        $urgentes = $sugerencias->where('urgencia', 'alta');
        $medias   = $sugerencias->where('urgencia', 'media');
        $bajas    = $sugerencias->where('urgencia', 'baja');
        $oks      = $sugerencias->where('urgencia', 'ok');
        $histLabel = implode(', ', array_map(
            fn($y) => $temporadaLabel . ' ' . $y,
            array_keys($histRangos)
        ));
    @endphp

    <div class="card" style="margin-bottom:1.5rem; border-left:4px solid #f59e0b;">
        <div class="card-header" style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <h3 class="card-title" style="flex:1;">Sugerencias de compra para {{ $temporadaLabel }} {{ $año }}</h3>
            <span style="font-size:0.78rem; color:var(--text-muted);">
                Basado en: {{ $histLabel }}
            </span>
        </div>
        <div class="card-body" style="padding:0;">

            {{-- Leyenda de urgencia --}}
            <div style="padding:0.75rem 1.25rem; background:var(--light-bg); border-bottom:1px solid rgba(0,0,0,0.05); display:flex; gap:1.25rem; flex-wrap:wrap; font-size:0.78rem; color:var(--text-muted);">
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#ef4444;margin-right:4px;"></span>Alta — stock crítico o agotado</span>
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f59e0b;margin-right:4px;"></span>Media — stock menor al 50 % del promedio</span>
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#3b82f6;margin-right:4px;"></span>Baja — stock por debajo del promedio histórico</span>
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#22c55e;margin-right:4px;"></span>OK — stock suficiente para esta temporada</span>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Producto</th>
                        <th>Marca / Línea</th>
                        <th style="text-align:right;">Prom. ventas / temporada</th>
                        <th style="text-align:right;">Stock actual</th>
                        <th style="text-align:right;">Sugerido comprar</th>
                        <th style="text-align:right;">Cobertura</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sugerencias as $s)
                    @php
                        $dotColor = match($s->urgencia) {
                            'alta'  => '#ef4444',
                            'media' => '#f59e0b',
                            'baja'  => '#3b82f6',
                            default => '#22c55e',
                        };
                        // Cobertura: cuántas temporadas cubre el stock actual
                        $cobertura = $s->avg_ventas > 0
                            ? round($s->stock_actual / $s->avg_ventas * 100)
                            : 100;
                        $cobColor = $cobertura >= 100 ? '#22c55e' : ($cobertura >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr>
                        <td style="width:18px; padding-right:0;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $dotColor }};"></span>
                        </td>
                        <td>
                            <div style="font-weight:500; line-height:1.3;">{{ $s->nombre }}</div>
                            <div style="font-size:0.72rem; color:var(--text-muted);">
                                Histórico: {{ $s->hist_años }} {{ $s->hist_años === 1 ? 'año' : 'años' }} · {{ number_format($s->hist_total) }} u. en total
                            </div>
                        </td>
                        <td style="font-size:0.85rem;">
                            {{ $s->marca ?? '—' }}
                            @if ($s->linea)<span style="color:var(--text-muted);"> / {{ $s->linea }}</span>@endif
                        </td>
                        <td style="text-align:right; font-weight:600;">
                            {{ number_format($s->avg_ventas, 1) }} u.
                        </td>
                        <td style="text-align:right;">
                            <span class="badge {{ $s->stock_actual < $s->stock_minimo ? 'badge-danger' : 'badge-success' }}">
                                {{ $s->stock_actual }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            @if ($s->comprar_sugerido > 0)
                                <span style="font-weight:700; font-size:1rem; color:#ef4444;">{{ $s->comprar_sugerido }} u.</span>
                            @else
                                <span style="color:#22c55e; font-weight:600;">— suficiente</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; align-items:center; gap:0.4rem; justify-content:flex-end;">
                                <div style="width:60px; height:6px; background:rgba(0,0,0,0.08); border-radius:3px; overflow:hidden;">
                                    <div style="width:{{ min($cobertura, 100) }}%; height:100%; background:{{ $cobColor }}; border-radius:3px; transition:width .3s;"></div>
                                </div>
                                <span style="font-size:0.8rem; min-width:2.5rem; text-align:right;">{{ min($cobertura, 999) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="padding:0.75rem 1.25rem; font-size:0.78rem; color:var(--text-muted); border-top:1px solid rgba(0,0,0,0.05);">
                La columna <strong>Cobertura</strong> indica qué % del promedio histórico de ventas cubre tu stock actual.
                La columna <strong>Sugerido comprar</strong> = promedio histórico − stock actual (mínimo 0).
            </div>
        </div>
    </div>
    @elseif ($temporada && $sugerencias->count() === 0 && !empty($histRangos))
    <div style="margin-bottom:1.5rem; padding:1rem 1.25rem; background:var(--light-bg); border-radius:8px; border:1px dashed rgba(0,0,0,0.12); color:var(--text-muted); font-size:0.88rem;">
        No hay ventas registradas en {{ implode(', ', array_map(fn($y) => $temporadaLabel . ' ' . $y, array_keys($histRangos))) }} para generar sugerencias.
    </div>
    @elseif ($temporada && empty($histRangos))
    <div style="margin-bottom:1.5rem; padding:1rem 1.25rem; background:var(--light-bg); border-radius:8px; border:1px dashed rgba(0,0,0,0.12); color:var(--text-muted); font-size:0.88rem;">
        Sin historial previo para {{ $temporadaLabel }}. Las sugerencias apareceran a partir del segundo año de datos.
    </div>
    @endif

    {{-- ── Resumen global ── --}}
    @php
        $totalEntradas  = $productos->sum('total_entradas');
        $totalSalidas   = $productos->sum('total_salidas');
        $stockBajoCount = $productos->filter(fn($p) => ($p->stock_actual) < $p->stock_minimo)->count();
        $sinMovimiento  = $productos->where('total_entradas', 0)->where('total_salidas', 0)->count();
        $conActividad   = $productos->count() - $sinMovimiento;
    @endphp

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="card" style="padding:1.25rem; text-align:center;">
            <div style="font-size:1.8rem; font-weight:700; color:var(--primary);">{{ $productos->count() }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; text-transform:uppercase; letter-spacing:.05em;">Productos</div>
        </div>
        <div class="card" style="padding:1.25rem; text-align:center;">
            <div style="font-size:1.8rem; font-weight:700; color:#22c55e;">{{ number_format($totalEntradas) }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; text-transform:uppercase; letter-spacing:.05em;">Entradas{{ $periodoTexto ? ' (temp.)' : '' }}</div>
        </div>
        <div class="card" style="padding:1.25rem; text-align:center;">
            <div style="font-size:1.8rem; font-weight:700; color:#ef4444;">{{ number_format($totalSalidas) }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; text-transform:uppercase; letter-spacing:.05em;">Salidas{{ $periodoTexto ? ' (temp.)' : '' }}</div>
        </div>
        <div class="card" style="padding:1.25rem; text-align:center;">
            <div style="font-size:1.8rem; font-weight:700; color:#f59e0b;">{{ $stockBajoCount }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; text-transform:uppercase; letter-spacing:.05em;">Stock Bajo</div>
        </div>
        <div class="card" style="padding:1.25rem; text-align:center;">
            <div style="font-size:1.8rem; font-weight:700; color:var(--text-muted);">{{ $sinMovimiento }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem; text-transform:uppercase; letter-spacing:.05em;">Sin Movimiento</div>
        </div>
    </div>

    {{-- ── Gráficas ── --}}
    @if ($topEntradas->count() > 0 || $topSalidas->count() > 0)
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(360px,1fr)); gap:1.5rem; margin-bottom:1.5rem;">

        @if ($topEntradas->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top por entradas{{ $periodoTexto ? ' — ' . $temporadaLabel . ' ' . $año : '' }}</h3>
            </div>
            <div class="card-body" style="padding:1rem 1.25rem;">
                <canvas id="chartEntradas" style="max-height:260px;"></canvas>
            </div>
        </div>
        @endif

        @if ($topSalidas->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top por salidas{{ $periodoTexto ? ' — ' . $temporadaLabel . ' ' . $año : '' }}</h3>
            </div>
            <div class="card-body" style="padding:1rem 1.25rem;">
                <canvas id="chartSalidas" style="max-height:260px;"></canvas>
            </div>
        </div>
        @endif

    </div>
    @endif

    @if ($masEstancados->count() > 0)
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Productos más estancados — días sin ninguna salida (global)</h3>
        </div>
        <div class="card-body" style="padding:1rem 1.25rem;">
            <canvas id="chartEstancados" style="max-height:200px;"></canvas>
        </div>
    </div>
    @endif

    {{-- ── Tabla detallada ── --}}
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Detalle por producto</h3>
            <span style="font-size:0.8rem; color:var(--text-muted);">{{ $productos->count() }} productos · ordenado por salidas desc</span>
        </div>

        @if ($productos->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Marca / Línea</th>
                    <th style="text-align:right;">Entradas</th>
                    <th style="text-align:right;">Salidas</th>
                    <th style="text-align:right;">Stock global</th>
                    <th style="text-align:right;">Rotación</th>
                    <th>Última salida</th>
                    <th style="text-align:right;">Días sin salida</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos->sortByDesc('total_salidas') as $p)
                    @php
                        $stock      = (int) $p->stock_actual;
                        $rotacion   = $p->total_entradas > 0 ? round(($p->total_salidas / $p->total_entradas) * 100) : 0;
                        $stockBadge = $stock < $p->stock_minimo ? 'badge-danger' : 'badge-success';
                        $rotColor   = $rotacion >= 80 ? '#22c55e' : ($rotacion >= 40 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:500; line-height:1.3;">{{ $p->nombre }}</div>
                            <code style="font-size:0.72rem; color:var(--text-muted);">{{ $p->codigo_barras }}</code>
                        </td>
                        <td style="font-size:0.85rem;">
                            {{ $p->marca ?? '—' }}
                            @if ($p->linea)<span style="color:var(--text-muted);"> / {{ $p->linea }}</span>@endif
                        </td>
                        <td style="text-align:right;">
                            <span class="badge badge-success">+{{ number_format($p->total_entradas) }}</span>
                        </td>
                        <td style="text-align:right;">
                            <span class="badge badge-danger">&minus;{{ number_format($p->total_salidas) }}</span>
                        </td>
                        <td style="text-align:right;">
                            <span class="badge {{ $stockBadge }}">{{ number_format($stock) }}</span>
                        </td>
                        <td style="text-align:right;">
                            @if ($p->total_entradas > 0)
                                <div style="display:flex; align-items:center; gap:0.4rem; justify-content:flex-end;">
                                    <div style="width:56px; height:5px; background:rgba(0,0,0,0.08); border-radius:3px; overflow:hidden;">
                                        <div style="width:{{ min($rotacion, 100) }}%; height:100%; background:{{ $rotColor }}; border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:0.8rem; min-width:2.5rem; text-align:right;">{{ $rotacion }}%</span>
                                </div>
                            @else
                                <span style="color:var(--text-muted); font-size:0.8rem;">—</span>
                            @endif
                        </td>
                        <td style="font-size:0.85rem; white-space:nowrap;">
                            {{ $p->ultima_salida ? \Carbon\Carbon::parse($p->ultima_salida)->format('d/m/Y') : '—' }}
                        </td>
                        <td style="text-align:right; font-size:0.85rem;">
                            @if ($p->dias_sin_salida !== null)
                                <span style="font-weight:600; color:{{ $p->dias_sin_salida > 60 ? '#ef4444' : ($p->dias_sin_salida > 30 ? '#f59e0b' : 'inherit') }};">
                                    {{ $p->dias_sin_salida }}d
                                </span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <p class="empty-state-text">No hay datos para mostrar</p>
            </div>
        @endif
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const baseOpts = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 35, minRotation: 0 } },
        y: { beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }
    }
};

@if ($topEntradas->count() > 0)
new Chart(document.getElementById('chartEntradas'), {
    type: 'bar',
    data: {
        labels: @json($topEntradas->pluck('nombre')->map(fn($n) => strlen($n) > 22 ? substr($n, 0, 22).'…' : $n)->values()),
        datasets: [{
            data: @json($topEntradas->pluck('total_entradas')->values()),
            backgroundColor: 'rgba(34,197,94,0.65)',
            borderColor: 'rgba(34,197,94,1)',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: baseOpts
});
@endif

@if ($topSalidas->count() > 0)
new Chart(document.getElementById('chartSalidas'), {
    type: 'bar',
    data: {
        labels: @json($topSalidas->pluck('nombre')->map(fn($n) => strlen($n) > 22 ? substr($n, 0, 22).'…' : $n)->values()),
        datasets: [{
            data: @json($topSalidas->pluck('total_salidas')->values()),
            backgroundColor: 'rgba(239,68,68,0.65)',
            borderColor: 'rgba(239,68,68,1)',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: baseOpts
});
@endif

@if ($masEstancados->count() > 0)
new Chart(document.getElementById('chartEstancados'), {
    type: 'bar',
    data: {
        labels: @json($masEstancados->pluck('nombre')->map(fn($n) => strlen($n) > 22 ? substr($n, 0, 22).'…' : $n)->values()),
        datasets: [{
            data: @json($masEstancados->pluck('dias_sin_salida')->values()),
            backgroundColor: 'rgba(245,158,11,0.65)',
            borderColor: 'rgba(245,158,11,1)',
            borderWidth: 1,
            borderRadius: 4,
            label: 'Días',
        }]
    },
    options: {
        ...baseOpts,
        indexAxis: 'y',
        scales: {
            x: { beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
            y: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
@endif
</script>
@endpush
@endsection
