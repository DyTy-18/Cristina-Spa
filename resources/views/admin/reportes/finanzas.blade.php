@extends('admin.layouts.app')

@section('title', 'Reporte de Finanzas')
@section('page-title', 'Reporte de Finanzas')

@push('styles')
<style>
.rpt-filter-bar { background:var(--white);border:1px solid rgba(0,0,0,0.06);padding:1.25rem 1.5rem;margin-bottom:1.25rem; }
.rpt-filter-row { display:flex;align-items:flex-end;gap:.75rem;flex-wrap:wrap; }
.rpt-filter-group { display:flex;flex-direction:column;gap:.3rem; }
.rpt-filter-label { font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);font-weight:300; }
.rpt-filter-bar input[type="date"] { width:152px;padding:.45rem .65rem;border:1px solid rgba(0,0,0,.12);background:var(--white);font-family:'Montserrat',sans-serif;font-size:.83rem;font-weight:300;color:var(--text-dark); }
.rpt-filter-bar input[type="date"]:focus { outline:none;border-color:var(--accent-color); }
.fin-resumen { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem; }
.fin-card { background:var(--white);border:1px solid rgba(0,0,0,.06);padding:1.1rem 1.25rem; }
.fin-card.green { border-left:3px solid #2d7a46; }
.fin-card.red   { border-left:3px solid #c0392b; }
.fin-card.gold  { border-left:3px solid var(--accent-color); }
.fin-valor { font-family:'Cormorant Garamond',serif;font-size:1.85rem;line-height:1; }
.fin-label { font-size:.65rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);margin-top:.25rem; }
.rpt-section-title { font-family:'Cormorant Garamond',serif;font-size:1rem;letter-spacing:1.5px;color:var(--primary-color);padding:.9rem 1.25rem;border-bottom:1px solid rgba(0,0,0,.05); }
.rpt-table { width:100%;border-collapse:collapse;background:var(--white); }
.rpt-table th { padding:.6rem .9rem;font-size:.67rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);font-weight:300;border-bottom:1px solid rgba(0,0,0,.07);background:var(--light-bg);text-align:left; }
.rpt-table td { padding:.7rem .9rem;font-size:.83rem;border-bottom:1px solid rgba(0,0,0,.04); }
.rpt-table tbody tr:hover td { background:rgba(201,169,110,.04); }
.rpt-footer { padding:.6rem .9rem;font-size:.75rem;color:var(--text-light);border-top:1px solid rgba(0,0,0,.04); }
</style>
@endpush

@section('content')

<form method="GET" action="{{ route('admin.reportes.finanzas') }}" id="filterForm">
    <div class="rpt-filter-bar">
        <div class="rpt-filter-row">
            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}">
            </div>
            <div class="rpt-filter-group">
                <label class="rpt-filter-label">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}">
            </div>
            <div class="rpt-filter-group">
                <label class="rpt-filter-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </div>
            <div class="rpt-filter-group" style="margin-left:auto;">
                <label class="rpt-filter-label">Exportar</label>
                <div style="display:flex;gap:.4rem;">
                    <a href="{{ route('admin.reportes.finanzas.pdf', request()->query()) }}"
                       class="btn btn-sm btn-outline" target="_blank">↓ PDF</a>
                    <a href="{{ route('admin.reportes.finanzas.excel', request()->query()) }}"
                       class="btn btn-sm btn-outline">↓ Excel</a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- ── Resumen ── --}}
<div class="fin-resumen">
    <div class="fin-card green">
        <div class="fin-valor" style="color:#2d7a46;">Bs. {{ number_format($ingresosCitas, 2) }}</div>
        <div class="fin-label">Ingresos (citas)</div>
    </div>
    <div class="fin-card red">
        <div class="fin-valor" style="color:#c0392b;">Bs. {{ number_format($totalGastos, 2) }}</div>
        <div class="fin-label">Gastos operativos</div>
    </div>
    <div class="fin-card red">
        <div class="fin-valor" style="color:#c0392b;">Bs. {{ number_format($totalPagos, 2) }}</div>
        <div class="fin-label">Pagos a empleados</div>
    </div>
    <div class="fin-card gold">
        <div class="fin-valor" style="color:{{ $neto >= 0 ? '#2d7a46' : '#c0392b' }};">
            Bs. {{ number_format($neto, 2) }}
        </div>
        <div class="fin-label">Neto del período</div>
    </div>
</div>

{{-- ── Gastos ── --}}
<div class="table-container" style="margin-bottom:1.25rem;">
    <div class="rpt-section-title">Gastos operativos
        <span style="font-size:.75rem;font-family:'Montserrat',sans-serif;color:var(--text-light);margin-left:.5rem;">{{ $gastos->count() }} registro(s)</span>
    </div>
    @if($gastos->isEmpty())
        <div style="padding:1.5rem;text-align:center;color:var(--text-light);font-size:.83rem;">Sin gastos en el período</div>
    @else
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Método</th>
                    <th style="text-align:right;">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gastos as $g)
                <tr>
                    <td>{{ $g->fecha->format('d/m/Y') }}</td>
                    <td>{{ $g->descripcion }}</td>
                    <td style="font-size:.8rem;color:var(--text-light);">{{ \App\Models\Gasto::categorias()[$g->categoria] ?? $g->categoria }}</td>
                    <td style="font-size:.8rem;color:var(--text-light);">{{ ucfirst($g->metodo_pago ?? '—') }}</td>
                    <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1rem;color:#c0392b;">
                        {{ number_format($g->monto, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="rpt-footer">Total gastos: <strong>Bs. {{ number_format($totalGastos, 2) }}</strong></div>
    @endif
</div>

{{-- ── Pagos a Empleados ── --}}
<div class="table-container">
    <div class="rpt-section-title">Pagos a empleados
        <span style="font-size:.75rem;font-family:'Montserrat',sans-serif;color:var(--text-light);margin-left:.5rem;">{{ $pagos->count() }} registro(s)</span>
    </div>
    @if($pagos->isEmpty())
        <div style="padding:1.5rem;text-align:center;color:var(--text-light);font-size:.83rem;">Sin pagos en el período</div>
    @else
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Método</th>
                    <th style="text-align:right;">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $p)
                <tr>
                    <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
                    <td>{{ $p->empleado ? $p->empleado->nombre . ' ' . $p->empleado->apellido : '—' }}</td>
                    <td style="font-size:.8rem;color:var(--text-light);">{{ \App\Models\PagoSueldo::tipos()[$p->tipo] ?? $p->tipo }}</td>
                    <td style="font-size:.8rem;color:var(--text-light);">{{ ucfirst($p->metodo_pago ?? '—') }}</td>
                    <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1rem;color:#c0392b;">
                        {{ number_format($p->monto, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="rpt-footer">Total pagos: <strong>Bs. {{ number_format($totalPagos, 2) }}</strong></div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('#filterForm input[type="date"]').forEach(input => {
    input.addEventListener('change', () => document.getElementById('filterForm').submit());
});
</script>
@endpush
