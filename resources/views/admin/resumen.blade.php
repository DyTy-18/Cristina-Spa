@extends('admin.layouts.app')

@section('title', 'Resumen General')
@section('page-title', 'Resumen General')

@push('styles')
<style>
.resumen-section {
    margin-bottom: 2rem;
}
.resumen-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--text-light);
    margin-bottom: 0.9rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid rgba(0,0,0,0.07);
}
.resumen-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}
.resumen-card {
    background: var(--white);
    border: 1px solid rgba(0,0,0,0.06);
    padding: 1.25rem 1.4rem;
}
.resumen-card.accent { border-left: 3px solid var(--accent-color); }
.resumen-card.green  { border-left: 3px solid #2d7a46; }
.resumen-card.red    { border-left: 3px solid #c0392b; }
.resumen-card.gold   { border-left: 3px solid var(--secondary-color); }
.resumen-valor {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.2rem;
    line-height: 1;
    color: var(--primary-color);
}
.resumen-valor.color-green { color: #2d7a46; }
.resumen-valor.color-red   { color: #c0392b; }
.resumen-valor.color-gold  { color: var(--accent-color); }
.resumen-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-light);
    margin-top: 0.3rem;
}
.resumen-bienvenida {
    background: var(--white);
    border: 1px solid rgba(0,0,0,0.06);
    padding: 1.25rem 1.6rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.resumen-bienvenida-icon { font-size: 2rem; }
.resumen-bienvenida-texto h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin: 0 0 0.15rem;
}
.resumen-bienvenida-texto p {
    font-size: 0.78rem;
    color: var(--text-light);
    margin: 0;
}
.resumen-link {
    margin-top: 0.6rem;
    display: inline-block;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent-color);
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.15s;
}
.resumen-link:hover { border-color: var(--accent-color); }
</style>
@endpush

@section('content')

<div class="resumen-bienvenida">
    <div class="resumen-bienvenida-icon">👁️</div>
    <div class="resumen-bienvenida-texto">
        <h3>Hola, {{ auth()->user()->name }}</h3>
        <p>Vista de resumen ejecutivo — {{ now()->format('d \d\e F, Y') }}
            @if(isset($sucursalActiva) && $sucursalActiva)
                &nbsp;·&nbsp; 🏢 {{ $sucursalActiva->nombre }}
            @else
                &nbsp;·&nbsp; 🌐 Todas las sucursales
            @endif
        </p>
    </div>
</div>

{{-- ── CITAS ─────────────────────────────────── --}}
<div class="resumen-section">
    <div class="resumen-section-title">📅 Citas</div>
    <div class="resumen-grid">
        <div class="resumen-card accent">
            <div class="resumen-valor">{{ $citasHoy }}</div>
            <div class="resumen-label">Hoy</div>
        </div>
        <div class="resumen-card">
            <div class="resumen-valor">{{ $citasMes }}</div>
            <div class="resumen-label">Este mes (total)</div>
        </div>
        <div class="resumen-card">
            <div class="resumen-valor color-green">{{ $citasCompletadasMes }}</div>
            <div class="resumen-label">Completadas este mes</div>
        </div>
        <div class="resumen-card">
            <div class="resumen-valor" style="color:var(--secondary-color);">{{ $citasPendientes }}</div>
            <div class="resumen-label">Pendientes</div>
        </div>
    </div>
    <a href="{{ route('admin.reportes.citas') }}" class="resumen-link">Ver reporte de citas →</a>
</div>

{{-- ── FINANZAS ──────────────────────────────── --}}
<div class="resumen-section">
    <div class="resumen-section-title">💰 Finanzas — {{ now()->format('F Y') }}</div>
    <div class="resumen-grid">
        <div class="resumen-card green">
            <div class="resumen-valor color-green">Bs. {{ number_format($ingresosMes, 0) }}</div>
            <div class="resumen-label">Ingresos del mes</div>
        </div>
        <div class="resumen-card red">
            <div class="resumen-valor color-red">Bs. {{ number_format($gastosMes, 0) }}</div>
            <div class="resumen-label">Gastos operativos</div>
        </div>
        <div class="resumen-card red">
            <div class="resumen-valor color-red">Bs. {{ number_format($pagosMes, 0) }}</div>
            <div class="resumen-label">Pagos a empleados</div>
        </div>
        <div class="resumen-card gold">
            <div class="resumen-valor {{ $netoMes >= 0 ? 'color-green' : 'color-red' }}">
                Bs. {{ number_format($netoMes, 0) }}
            </div>
            <div class="resumen-label">Neto del mes</div>
        </div>
    </div>
    <a href="{{ route('admin.reportes.finanzas') }}" class="resumen-link">Ver reporte de finanzas →</a>
</div>

{{-- ── CLIENTES ──────────────────────────────── --}}
<div class="resumen-section">
    <div class="resumen-section-title">👥 Clientes</div>
    <div class="resumen-grid">
        <div class="resumen-card accent">
            <div class="resumen-valor">{{ $totalClientes }}</div>
            <div class="resumen-label">Total registrados</div>
        </div>
        <div class="resumen-card green">
            <div class="resumen-valor color-green">{{ $nuevosMes }}</div>
            <div class="resumen-label">Nuevos este mes</div>
        </div>
    </div>
    <a href="{{ route('admin.reportes.clientes') }}" class="resumen-link">Ver reporte de clientes →</a>
</div>

{{-- ── INVENTARIO ────────────────────────────── --}}
<div class="resumen-section">
    <div class="resumen-section-title">📦 Inventario</div>
    <div class="resumen-grid">
        <div class="resumen-card accent">
            <div class="resumen-valor">{{ $totalProductos }}</div>
            <div class="resumen-label">Productos activos</div>
        </div>
        <div class="resumen-card {{ $productosStockBajo > 0 ? 'red' : 'green' }}">
            <div class="resumen-valor {{ $productosStockBajo > 0 ? 'color-red' : 'color-green' }}">
                {{ $productosStockBajo }}
            </div>
            <div class="resumen-label">Stock bajo</div>
        </div>
    </div>
</div>

@endsection
