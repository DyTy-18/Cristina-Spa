@extends('admin.layouts.app')

@section('title', 'Inventario - Stock Actual')
@section('page-title', 'Inventario')

@push('styles')
<style>
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: var(--white);
        border-radius: 8px;
        padding: 2rem;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    .modal-box h4 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--primary-color);
        margin-bottom: 0.35rem;
        font-weight: 400;
    }
    .modal-box p { font-size: 0.85rem; color: var(--text-light); margin-bottom: 1.25rem; }
    .modal-actions { display: flex; gap: 0.6rem; justify-content: flex-end; margin-top: 0.5rem; }
</style>
@endpush

@section('content')
@php $tipoStock = $tipoStock ?? 'tecnico'; @endphp
<div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
    <a href="{{ route('admin.inventario.index') }}"
       class="btn btn-sm {{ $tipoStock === 'tecnico' ? 'btn-primary' : 'btn-outline' }}">Inventario Técnico</a>
    <a href="{{ route('admin.inventario.index', ['tipo' => 'reventa']) }}"
       class="btn btn-sm {{ $tipoStock === 'reventa' ? 'btn-primary' : 'btn-outline' }}">Inventario Reventa</a>
</div>
@if($alertas->isNotEmpty())
    <div style="margin-bottom:1.5rem;">
        @foreach($alertas as $alerta)
        <div id="alerta-{{ $alerta->id }}"
             style="background:#fff8e1;border:1px solid #f59e0b;border-radius:6px;padding:.75rem 1rem;margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div>
                <strong>⚠️ {{ $alerta->producto->nombre }}</strong>
                @if($alerta->producto->marca)
                    <span style="color:#888;">({{ $alerta->producto->marca }})</span>
                @endif
                — Stock actual: <strong>{{ $alerta->stock_actual }}</strong> uds.
                &nbsp;·&nbsp; Mínimo: {{ $alerta->producto->stock_minimo }}
            </div>
            <div style="display:flex;gap:.5rem;flex-shrink:0;">
                <button type="button" class="btn btn-outline btn-sm"
                        onclick="copiarMensajeWpp({{ json_encode($alerta->mensaje) }})">
                    Copiar msg WPP
                </button>
                <button type="button" class="btn btn-outline btn-sm"
                        onclick="marcarLeida({{ $alerta->id }})">
                    Marcar leída
                </button>
            </div>
        </div>
        @endforeach
    </div>
@endif
    <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
        @if ($tipoStock === 'tecnico')
            <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary">+ Registrar Entrada</a>
            <a href="{{ route('admin.inventario.salidas.create') }}" class="btn btn-accent">+ Registrar Salida</a>
            <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-outline">+ Nuevo Producto</a>
            <a href="{{ route('admin.inventario.entradas') }}" class="btn btn-outline btn-sm">Historial Entradas</a>
            <a href="{{ route('admin.inventario.salidas') }}" class="btn btn-outline btn-sm">Historial Salidas</a>
        @else
            <a href="{{ route('admin.inventario.reventa.entrada.create') }}" class="btn btn-primary">+ Registrar Entrada</a>
            <a href="{{ route('admin.inventario.reventa.transferir.create') }}" class="btn btn-outline">+ Transferir a Reventa</a>
            <a href="{{ route('admin.inventario.reventa.salida.create') }}" class="btn btn-accent">+ Registrar Venta</a>
            <a href="{{ route('admin.inventario.entradas', ['tipo' => 'reventa']) }}" class="btn btn-outline btn-sm">Historial Entradas</a>
            <a href="{{ route('admin.inventario.salidas', ['tipo' => 'reventa']) }}" class="btn btn-outline btn-sm">Historial Ventas</a>
        @endif
        <a href="{{ route('admin.inventario.productos') }}" class="btn btn-outline btn-sm">Catálogo</a>
        <a href="{{ route('admin.inventario.importar') }}" class="btn btn-outline btn-sm">Importar Excel</a>
        <a href="{{ route('admin.inventario.analisis') }}" class="btn btn-outline btn-sm">📊 Análisis</a>
        <a href="{{ route('admin.inventario.backups') }}" class="btn btn-outline btn-sm">🗂 Ver Backups</a>

        <button type="button" class="btn btn-sm" style="margin-left:auto;background:#dc2626;color:#fff;border:none;"
                onclick="document.getElementById('modalLimpiarInventario').classList.add('active')">
            🗑 Limpiar inventario
        </button>
    </div>

    <div class="modal-overlay" id="modalLimpiarInventario">
        <div class="modal-box">
            <h4>Limpiar inventario</h4>
            <p>
                Esto elimina TODOS los productos, entradas y salidas. Se guardará un backup con lo eliminado
                (accesible desde "Ver Backups"), pero la acción no se puede deshacer sobre el stock actual.
                ¿Confirmás?
            </p>
            <form method="POST" action="{{ route('admin.inventario.limpiar') }}">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline btn-sm"
                            onclick="document.getElementById('modalLimpiarInventario').classList.remove('active')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-sm" style="background:#dc2626;color:#fff;border:none;">
                        Sí, eliminar todo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                <h3 class="table-title">Stock Actual {{ $tipoStock === 'reventa' ? '(Reventa)' : '(Técnico)' }}</h3>
                @include('admin.partials.sucursal_badge')
            </div>
            <span style="font-size:0.8rem; color:#888;">Entradas &minus; Salidas por producto</span>
        </div>

        <form method="GET" action="{{ route('admin.inventario.index') }}" class="table-filter">
            <input type="hidden" name="tipo" value="{{ $tipoStock }}">
            <input type="text" name="q" class="search-input"
                   placeholder="Buscar por nombre o código..."
                   value="{{ $q ?? '' }}">

            <select name="marca" class="filter-date" onchange="this.form.submit()"
                    style="min-width:140px;">
                <option value="">Todas las marcas</option>
                @foreach ($marcas as $m)
                    <option value="{{ $m }}" {{ ($marca ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>

            <select name="linea" class="filter-date" style="min-width:140px;">
                <option value="">Todas las líneas</option>
                @foreach ($lineas as $l)
                    <option value="{{ $l }}" {{ ($linea ?? '') === $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            @if ($q || $marca || $linea)
                <a href="{{ route('admin.inventario.index', ['tipo' => $tipoStock]) }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif
        </form>

        @if ($stock->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        @if ($tipoStock === 'tecnico')
                            <th>Costo</th>
                        @endif
                        <th style="text-align:right;">Entradas</th>
                        <th style="text-align:right;">Salidas</th>
                        <th style="text-align:right;">Stock</th>
                        @if ($tipoStock === 'tecnico')
                            <th style="text-align:right;">Mínimo</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stock as $item)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $item->codigo_barras }}</code></td>
                            <td>
                                {{ $item->nombre }}
                                @if ($tipoStock === 'tecnico' && $item->es_reventa)
                                    <span class="badge badge-success" style="font-size:0.65rem;">Reventa</span>
                                @endif
                            </td>
                            <td>{{ $item->marca ?? '—' }}</td>
                            <td>{{ $item->linea ?? '—' }}</td>
                            @if ($tipoStock === 'tecnico')
                                <td>Bs. {{ number_format($item->costo, 2) }}</td>
                            @endif
                            <td style="text-align:right;">{{ $item->total_entradas }}</td>
                            <td style="text-align:right;">{{ $item->total_salidas }}</td>
                            <td style="text-align:right;">
                                @if ($tipoStock === 'tecnico' && $item->stock_actual < $item->stock_minimo)
                                    <span class="badge badge-danger">{{ $item->stock_actual }}</span>
                                @else
                                    <span class="badge badge-success">{{ $item->stock_actual }}</span>
                                @endif
                            </td>
                            @if ($tipoStock === 'tecnico')
                                <td style="text-align:right;">{{ $item->stock_minimo }}</td>
                            @endif
                            <td style="display:flex; gap:0.4rem;">
                                <a href="{{ route('admin.inventario.productos.edit', $item->id) }}"
                                   class="btn btn-outline btn-sm">Editar</a>
                                @if ($tipoStock === 'tecnico' && $item->stock_actual > 0)
                                    <a href="{{ route('admin.inventario.reventa.transferir.create') }}"
                                       class="btn btn-accent btn-sm">Transferir</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <p class="empty-state-text">
                    @if ($q || $marca || $linea)
                        No se encontraron productos con los filtros aplicados
                    @else
                        No hay productos en el inventario
                    @endif
                </p>
                @if (!$q && !$marca && !$linea)
                    <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-primary">Agregar Primer Producto</a>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function copiarMensajeWpp(mensaje) {
    navigator.clipboard.writeText(mensaje).then(function () {
        alert('Mensaje copiado al portapapeles.');
    });
}
function marcarLeida(id) {
    fetch('/admin/alertas-stock/' + id + '/leer', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            var el = document.getElementById('alerta-' + id);
            if (el) el.remove();
        }
    });
}
document.getElementById('modalLimpiarInventario').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('active');
});
</script>
@endpush
