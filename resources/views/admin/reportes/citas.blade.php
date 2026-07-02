@extends('admin.layouts.app')

@section('title', 'Reporte de Citas')
@section('page-title', 'Reporte de Citas')

@push('styles')
<style>
.rpt-filter-bar { background:var(--white);border:1px solid rgba(0,0,0,0.06);padding:1.25rem 1.5rem;margin-bottom:1.25rem; }
.rpt-filter-row { display:flex;align-items:flex-end;gap:.75rem;flex-wrap:wrap; }
.rpt-filter-group { display:flex;flex-direction:column;gap:.3rem; }
.rpt-filter-label { font-size:.68rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);font-weight:300; }
.rpt-filter-bar input[type="date"] { width:152px;padding:.45rem .65rem;border:1px solid rgba(0,0,0,.12);background:var(--white);font-family:'Montserrat',sans-serif;font-size:.83rem;font-weight:300;color:var(--text-dark); }
.rpt-filter-bar input[type="date"]:focus { outline:none;border-color:var(--accent-color); }
.rpt-resumen { display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.25rem; }
.rpt-card { background:var(--white);border:1px solid rgba(0,0,0,.06);padding:1rem 1.25rem; }
.rpt-card.rpt-card-accent { border-left:3px solid var(--accent-color); }
.rpt-card.rpt-card-green  { border-left:3px solid #2d7a46; }
.rpt-valor { font-family:'Cormorant Garamond',serif;font-size:1.9rem;color:var(--primary-color);line-height:1; }
.rpt-label { font-size:.65rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);margin-top:.25rem; }
.rpt-table { width:100%;border-collapse:collapse;background:var(--white); }
.rpt-table th { padding:.6rem .9rem;font-size:.67rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-light);font-weight:300;border-bottom:1px solid rgba(0,0,0,.07);background:var(--light-bg);text-align:left; }
.rpt-table td { padding:.7rem .9rem;font-size:.83rem;border-bottom:1px solid rgba(0,0,0,.04); }
.rpt-table tbody tr:hover td { background:rgba(201,169,110,.04); }
.rpt-monto { font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--secondary-color); }
.rpt-footer { padding:.6rem .9rem;font-size:.75rem;color:var(--text-light);border-top:1px solid rgba(0,0,0,.04); }
</style>
@endpush

@section('content')

<form method="GET" action="{{ route('admin.reportes.citas') }}" id="filterForm">
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
                    <a href="{{ route('admin.reportes.citas.pdf', request()->query()) }}"
                       class="btn btn-sm btn-outline" target="_blank">↓ PDF</a>
                    <a href="{{ route('admin.reportes.citas.excel', request()->query()) }}"
                       class="btn btn-sm btn-outline">↓ Excel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="rpt-resumen">
    <div class="rpt-card rpt-card-accent">
        <div class="rpt-valor">{{ $totalCitas }}</div>
        <div class="rpt-label">Citas completadas</div>
    </div>
    <div class="rpt-card rpt-card-green">
        <div class="rpt-valor" style="color:var(--accent-color);">Bs. {{ number_format($totalIngresos, 2) }}</div>
        <div class="rpt-label">Total ingresos</div>
    </div>
    @if($totalCitas > 0)
    <div class="rpt-card">
        <div class="rpt-valor">Bs. {{ number_format($totalIngresos / $totalCitas, 2) }}</div>
        <div class="rpt-label">Promedio por cita</div>
    </div>
    @endif
</div>

<div class="table-container">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.9rem 1.25rem;border-bottom:1px solid rgba(0,0,0,.05);">
        <span style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;letter-spacing:1.5px;color:var(--primary-color);">
            Detalle
            <span style="font-size:.75rem;color:var(--text-light);font-family:'Montserrat',sans-serif;margin-left:.5rem;">{{ $totalCitas }} cita(s)</span>
        </span>
        <span style="font-size:.75rem;color:var(--text-light);">
            {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        </span>
    </div>

    @if($citas->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p class="empty-state-text">No hay citas completadas en el período seleccionado</p>
        </div>
    @else
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Servicios</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Sucursal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($citas as $cita)
                <tr>
                    <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                    <td style="color:var(--text-light);font-size:.8rem;">
                        {{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('H:i') : '—' }}
                    </td>
                    <td>
                        @if($cita->cliente)
                            {{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}
                        @else
                            <span style="color:#ccc;">—</span>
                        @endif
                    </td>
                    <td style="max-width:220px;font-size:.8rem;color:var(--text-light);">
                        {{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' · ') ?: '—' }}
                    </td>
                    <td>
                        <span class="rpt-monto">Bs. {{ number_format($cita->precio_final, 2) }}</span>
                    </td>
                    <td style="font-size:.8rem;">
                        @switch($cita->tipo_pago)
                            @case('efectivo') <span style="color:#2d7a46;">Efectivo</span> @break
                            @case('tarjeta')  <span style="color:#2980b9;">Tarjeta</span>  @break
                            @case('qr')       <span style="color:#8e44ad;">QR</span>        @break
                            @default          <span style="color:#ccc;">—</span>
                        @endswitch
                    </td>
                    <td style="font-size:.8rem;color:var(--text-light);">
                        {{ $cita->sucursal?->nombre ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="rpt-footer">
            {{ $totalCitas }} cita(s) &nbsp;·&nbsp; Total: <strong>Bs. {{ number_format($totalIngresos, 2) }}</strong>
        </div>
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
