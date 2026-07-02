@extends('admin.layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Reportes')

@push('styles')
<style>
.rpt-index-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    margin-top: 0.5rem;
}
.rpt-index-card {
    background: var(--white);
    border: 1px solid rgba(0,0,0,0.07);
    padding: 1.75rem 1.5rem;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.rpt-index-card:hover {
    border-color: var(--accent-color);
    box-shadow: 0 4px 16px rgba(0,0,0,0.07);
}
.rpt-index-icon { font-size: 2rem; margin-bottom: 0.75rem; }
.rpt-index-titulo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 0.35rem;
}
.rpt-index-desc {
    font-size: 0.78rem;
    color: var(--text-light);
    line-height: 1.5;
}
.rpt-index-badges {
    margin-top: 1rem;
    display: flex;
    gap: 0.4rem;
}
.rpt-badge {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.15rem 0.5rem;
    border: 1px solid rgba(0,0,0,0.12);
    color: var(--text-light);
}
.rpt-badge.pdf  { border-color: #e74c3c; color: #e74c3c; }
.rpt-badge.excel { border-color: #27ae60; color: #27ae60; }
</style>
@endpush

@section('content')

<p style="font-size:0.82rem;color:var(--text-light);margin-bottom:1.5rem;">
    Selecciona el tipo de reporte que deseas generar. Todos permiten filtrar por rango de fechas y exportar en PDF o Excel.
</p>

<div class="rpt-index-grid">

    <a href="{{ route('admin.reportes.citas') }}" class="rpt-index-card">
        <div class="rpt-index-icon">📅</div>
        <div class="rpt-index-titulo">Reporte de Citas</div>
        <div class="rpt-index-desc">Citas completadas por período: fecha, cliente, servicios, ingresos totales y tipo de pago.</div>
        <div class="rpt-index-badges">
            <span class="rpt-badge pdf">PDF</span>
            <span class="rpt-badge excel">Excel</span>
        </div>
    </a>

    <a href="{{ route('admin.reportes.comisiones') }}" class="rpt-index-card">
        <div class="rpt-index-icon">💼</div>
        <div class="rpt-index-titulo">Reporte de Empleados</div>
        <div class="rpt-index-desc">Comisiones por empleado: servicios realizados, porcentaje y monto de comisión por período.</div>
        <div class="rpt-index-badges">
            <span class="rpt-badge pdf">PDF</span>
            <span class="rpt-badge excel">Excel</span>
        </div>
    </a>

    <a href="{{ route('admin.reportes.finanzas') }}" class="rpt-index-card">
        <div class="rpt-index-icon">💰</div>
        <div class="rpt-index-titulo">Reporte de Finanzas</div>
        <div class="rpt-index-desc">Ingresos vs Gastos: total ingresos por citas, gastos operativos, pagos a empleados y neto del período.</div>
        <div class="rpt-index-badges">
            <span class="rpt-badge pdf">PDF</span>
            <span class="rpt-badge excel">Excel</span>
        </div>
    </a>

    <a href="{{ route('admin.reportes.clientes') }}" class="rpt-index-card">
        <div class="rpt-index-icon">👥</div>
        <div class="rpt-index-titulo">Reporte de Clientes</div>
        <div class="rpt-index-desc">Listado de clientes con historial de visitas, gasto total, servicios utilizados y datos de contacto.</div>
        <div class="rpt-index-badges">
            <span class="rpt-badge pdf">PDF</span>
            <span class="rpt-badge excel">Excel</span>
        </div>
    </a>

</div>

@endsection
