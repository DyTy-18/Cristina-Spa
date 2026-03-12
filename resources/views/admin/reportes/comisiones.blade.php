@extends('admin.layouts.app')

@section('title', 'Reporte de Comisiones')
@section('page-title', 'Reporte de Comisiones')

@push('styles')
<style>
    .filter-bar {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }
    .filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
    .filter-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); font-weight: 300; }
    .filter-bar input[type="date"], .filter-bar select { width: 180px; }

    .export-btns { margin-left: auto; display: flex; gap: 0.5rem; align-items: center; }

    .resumen-global { display: flex; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .resumen-card { background: var(--white); border: 1px solid rgba(0,0,0,0.05); padding: 1.2rem 1.8rem; flex: 1; min-width: 180px; }
    .resumen-valor { font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: var(--primary-color); line-height: 1; }
    .resumen-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); margin-top: 0.3rem; }

    .empleado-block { margin-bottom: 2rem; }
    .empleado-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.9rem 1.2rem; background: var(--primary-color); color: var(--white); flex-wrap: wrap; gap: 0.5rem;
    }
    .empleado-nombre { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; letter-spacing: 1px; font-weight: 400; }
    .empleado-totales { display: flex; gap: 2rem; font-size: 0.82rem; font-weight: 300; letter-spacing: 0.5px; }
    .empleado-totales strong { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 400; }

    .reporte-table { width: 100%; border-collapse: collapse; background: var(--white); border: 1px solid rgba(0,0,0,0.05); border-top: none; }
    .reporte-table th { padding: 0.65rem 1rem; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); font-weight: 300; border-bottom: 1px solid rgba(0,0,0,0.07); background: var(--light-bg); text-align: left; }
    .reporte-table td { padding: 0.75rem 1rem; font-size: 0.85rem; border-bottom: 1px solid rgba(0,0,0,0.04); vertical-align: middle; }
    .reporte-table tbody tr:last-child td { border-bottom: none; }
    .reporte-table tbody tr:hover td { background: var(--light-bg); }
    .reporte-table tfoot td { padding: 0.75rem 1rem; font-size: 0.85rem; border-top: 2px solid rgba(0,0,0,0.08); background: var(--light-bg); font-weight: 400; }

    .com-activa   { color: var(--success-color, #28a745); font-size: 0.78rem; }
    .com-inactiva { color: var(--text-light); font-size: 0.78rem; font-style: italic; }
    .com-sin      { color: var(--text-light); font-size: 0.78rem; font-style: italic; }
    .monto-com    { font-family: 'Cormorant Garamond', serif; font-size: 1.05rem; color: var(--accent-color); }
    .monto-cero   { color: var(--text-light); font-size: 0.82rem; }

    .badge-split {
        display: inline-block; background: rgba(201,169,110,0.15); color: var(--accent-color);
        font-size: 0.7rem; padding: 0.1rem 0.4rem; letter-spacing: 0.3px;
    }

    @media print {
        .filter-bar, .export-btns, .btn { display: none !important; }
        .empleado-block { page-break-inside: avoid; }
    }
</style>
@endpush

@section('content')

    {{-- Filtros + exportar --}}
    <form method="GET" action="{{ route('admin.reportes.comisiones') }}" class="filter-bar">
        <div class="filter-group">
            <span class="filter-label">Desde</span>
            <input type="date" name="desde" class="form-control" value="{{ $desde }}">
        </div>
        <div class="filter-group">
            <span class="filter-label">Hasta</span>
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
        </div>
        <div class="filter-group">
            <span class="filter-label">Empleado</span>
            <select name="empleado_id" class="form-control">
                <option value="">— Todos —</option>
                @foreach($empleados as $emp)
                    <option value="{{ $emp->id }}" {{ $empleadoId == $emp->id ? 'selected' : '' }}>
                        {{ trim($emp->nombre . ' ' . $emp->apellido) }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Generar</button>

        <div class="export-btns">
            <a href="{{ route('admin.reportes.comisiones.pdf', request()->query()) }}"
               class="btn btn-outline btn-sm" target="_blank">
                ↓ PDF
            </a>
            <a href="{{ route('admin.reportes.comisiones.excel', request()->query()) }}"
               class="btn btn-outline btn-sm">
                ↓ Excel
            </a>
        </div>
    </form>

    @if($porEmpleado->isEmpty())
        <div class="table-container">
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p class="empty-state-text">No hay servicios completados con profesional asignado en este período</p>
            </div>
        </div>
    @else

        {{-- Resumen global --}}
        <div class="resumen-global">
            <div class="resumen-card">
                <div class="resumen-valor">{{ $porEmpleado->count() }}</div>
                <div class="resumen-label">Profesionales con actividad</div>
            </div>
            <div class="resumen-card">
                <div class="resumen-valor">Bs. {{ number_format($totalGlobal, 2) }}</div>
                <div class="resumen-label">Total facturado en período</div>
            </div>
            <div class="resumen-card" style="border-left: 3px solid var(--accent-color);">
                <div class="resumen-valor" style="color:var(--accent-color);">Bs. {{ number_format($comisionGlobal, 2) }}</div>
                <div class="resumen-label">Total comisiones a pagar</div>
            </div>
        </div>

        @foreach($porEmpleado as $bloque)
            @php $emp = $bloque['empleado']; @endphp
            <div class="empleado-block">
                <div class="empleado-header">
                    <div>
                        <div class="empleado-nombre">{{ $emp?->nombre_completo ?? '—' }}</div>
                        @if($emp?->cargo)
                            <div style="font-size:0.75rem;opacity:0.75;margin-top:0.1rem;">{{ $emp->cargo }}</div>
                        @endif
                    </div>
                    <div class="empleado-totales">
                        <div>Servicios facturados<br><strong>Bs. {{ number_format($bloque['total_precio'], 2) }}</strong></div>
                        <div>Comisión total<br><strong>Bs. {{ number_format($bloque['total_comision'], 2) }}</strong></div>
                    </div>
                </div>

                <table class="reporte-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th style="text-align:right;">Precio</th>
                            <th style="text-align:center;">Comisión</th>
                            <th style="text-align:center;">Co-partic.</th>
                            <th style="text-align:right;">Monto comisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bloque['filas'] as $fila)
                            <tr>
                                <td style="white-space:nowrap;color:var(--text-light);font-size:0.8rem;">
                                    {{ $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') : '—' }}
                                </td>
                                <td style="font-size:0.82rem;color:var(--text-light);">{{ $fila['cliente'] ?? '—' }}</td>
                                <td style="font-weight:400;">{{ $fila['servicio'] }}</td>
                                <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1rem;">
                                    Bs. {{ number_format($fila['precio'], 2) }}
                                </td>
                                <td style="text-align:center;">
                                    @if($fila['com_estado'] === 'activa')
                                        <span class="com-activa">● {{ number_format($fila['pct'], 2) }}%</span>
                                    @elseif($fila['com_estado'] === 'inactiva')
                                        <span class="com-inactiva">inactiva</span>
                                    @else
                                        <span class="com-sin">sin config.</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($fila['participantes'] > 1)
                                        <span class="badge-split">÷ {{ $fila['participantes'] }}</span>
                                    @else
                                        <span style="color:var(--text-light);font-size:0.78rem;">—</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if($fila['monto_comision'] > 0)
                                        <span class="monto-com">Bs. {{ number_format($fila['monto_comision'], 2) }}</span>
                                    @else
                                        <span class="monto-cero">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="color:var(--text-light);font-size:0.78rem;">
                                {{ $bloque['filas']->count() }} {{ $bloque['filas']->count() === 1 ? 'servicio' : 'servicios' }}
                            </td>
                            <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.05rem;">
                                Bs. {{ number_format($bloque['total_precio'], 2) }}
                            </td>
                            <td colspan="2"></td>
                            <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--accent-color);">
                                Bs. {{ number_format($bloque['total_comision'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

    @endif

@endsection
