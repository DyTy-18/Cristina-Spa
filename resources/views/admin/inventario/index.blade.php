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

    /* Barra superior: pestañas a la izquierda, acciones a la derecha */
    .inv-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.9rem; }
    .inv-tabs { display: flex; gap: 0.5rem; }
    .inv-acciones { display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }

    /* Enlaces secundarios: consultas y herramientas, con menos peso visual que los botones */
    .inv-subnav {
        display: flex; align-items: center; gap: 0.35rem 1.5rem; flex-wrap: wrap;
        padding: 0.65rem 0; margin-bottom: 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.06); border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .inv-subnav a, .inv-subnav button {
        font-family: inherit; font-size: 0.72rem; font-weight: 400; letter-spacing: 1px; text-transform: uppercase;
        color: var(--text-light); text-decoration: none; background: none; border: none; padding: 0.2rem 0; cursor: pointer;
    }
    .inv-subnav a:hover { color: var(--accent-color); }
    .inv-subnav .inv-subnav-peligro { margin-left: auto; color: #c0392b; opacity: 0.75; }
    .inv-subnav .inv-subnav-peligro:hover { opacity: 1; }

    .inv-minimo { display: block; font-size: 0.7rem; color: #aaa; margin-top: 0.2rem; }
    .inv-cero { color: #ccc; }
</style>
@endpush

@section('content')
@php
    $tipoStock = $tipoStock ?? 'tecnico';
    $esTecnico = $tipoStock === 'tecnico';
@endphp

<div class="inv-toolbar">
    <div class="inv-tabs">
        <a href="{{ route('admin.inventario.index') }}"
           class="btn btn-sm {{ $esTecnico ? 'btn-primary' : 'btn-outline' }}">Inventario Técnico</a>
        <a href="{{ route('admin.inventario.index', ['tipo' => 'reventa']) }}"
           class="btn btn-sm {{ $esTecnico ? 'btn-outline' : 'btn-primary' }}">Inventario Reventa</a>
    </div>

    {{-- Acciones frecuentes: botones. Lo demás va en la barra de enlaces de abajo. --}}
    <div class="inv-acciones">
        @if ($esTecnico)
            <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary btn-sm">+ Registrar Entrada</a>
            <a href="{{ route('admin.inventario.salidas.create') }}" class="btn btn-accent btn-sm">+ Registrar Salida</a>
            <a href="{{ route('admin.inventario.reventa.transferir.create') }}" class="btn btn-outline btn-sm">⇄ Transferir a Reventa</a>
        @else
            <a href="{{ route('admin.inventario.reventa.entrada.create') }}" class="btn btn-primary btn-sm">+ Registrar Entrada</a>
            <a href="{{ route('admin.inventario.reventa.salida.create') }}" class="btn btn-accent btn-sm">+ Registrar Venta</a>
            <a href="{{ route('admin.inventario.reventa.transferir_tecnico.create') }}" class="btn btn-outline btn-sm">⇄ Transferir a Técnico</a>
        @endif
        <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-outline btn-sm">+ Nuevo Producto</a>
    </div>
</div>

<nav class="inv-subnav">
    <a href="{{ route('admin.inventario.entradas', ['tipo' => $tipoStock]) }}">Historial Entradas</a>
    <a href="{{ route('admin.inventario.salidas', ['tipo' => $tipoStock]) }}">Historial {{ $esTecnico ? 'Salidas' : 'Ventas' }}</a>
    <a href="{{ route('admin.inventario.productos', ['tipo' => $tipoStock]) }}">Catálogo</a>
    <a href="{{ route('admin.inventario.analisis') }}">Análisis</a>
    <a href="{{ route('admin.inventario.importar') }}">Importar Excel</a>
    <a href="{{ route('admin.inventario.backups') }}">Backups</a>
    <button type="button" class="inv-subnav-peligro"
            onclick="document.getElementById('modalLimpiarInventario').classList.add('active')">
        Limpiar inventario
    </button>
</nav>

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
            <h3 class="table-title">Stock Actual</h3>
        </div>

        <form method="GET" action="{{ route('admin.inventario.index') }}" class="table-filter">
            <input type="hidden" name="tipo" value="{{ $tipoStock }}">
            <input type="text" name="q" class="search-input"
                   placeholder="Buscar por nombre o código..."
                   value="{{ $q ?? '' }}">

            <select name="marca" class="filter-date"
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

            @include('admin.inventario._filtro_tipo')

            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            @if ($q || $marca || $linea || $tipoProducto)
                <a href="{{ route('admin.inventario.index', ['tipo' => $tipoStock]) }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif
        </form>

        @if ($stock->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        @if ($modoGlobal ?? false)
                            <th>Sucursal</th>
                        @endif
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        @if ($esTecnico)
                            <th style="text-align:right;">Costo</th>
                        @endif
                        <th style="text-align:right;">Entradas</th>
                        <th style="text-align:right;">Salidas</th>
                        <th style="text-align:right;" title="Saldo de transferencias entre técnico y reventa: + recibido, − enviado">Transf.</th>
                        <th style="text-align:right;">Stock</th>
                        <th style="width:1%;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stock as $item)
                        @php
                            $bajoMinimo = $esTecnico && $item->stock_actual < $item->stock_minimo;
                        @endphp
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $item->codigo_barras }}</code></td>
                            @if ($modoGlobal ?? false)
                                <td>{{ $item->sucursal_nombre ?? '—' }}</td>
                            @endif
                            <td>
                                {{ $item->nombre }}
                                {{-- El tipo (vitrina, seguimiento...) solo tiene sentido en el inventario de reventa. --}}
                                @if (! $esTecnico && $item->es_reventa)
                                    @include('admin.inventario.productos._badge_tipo', ['p' => $item])
                                @endif
                            </td>
                            <td>{{ $item->marca ?? '—' }}</td>
                            <td>{{ $item->linea ?? '—' }}</td>
                            @if ($esTecnico)
                                <td style="text-align:right; white-space:nowrap;">Bs. {{ number_format($item->costo, 2) }}</td>
                            @endif
                            <td style="text-align:right;" class="{{ $item->total_entradas ? '' : 'inv-cero' }}">{{ $item->total_entradas }}</td>
                            <td style="text-align:right;" class="{{ $item->total_salidas ? '' : 'inv-cero' }}">{{ $item->total_salidas }}</td>
                            <td style="text-align:right;">
                                @if ($item->total_transferencias > 0)
                                    <span style="color:#15803d;">+{{ $item->total_transferencias }}</span>
                                @elseif ($item->total_transferencias < 0)
                                    <span style="color:#b45309;">−{{ abs($item->total_transferencias) }}</span>
                                @else
                                    <span class="inv-cero">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <span class="badge {{ $bajoMinimo ? 'badge-danger' : 'badge-success' }}">{{ $item->stock_actual }}</span>
                                @if ($esTecnico)
                                    <span class="inv-minimo">mín. {{ $item->stock_minimo }}</span>
                                @endif
                            </td>
                            <td>
                                @include('admin.inventario.productos._acciones_fila', [
                                    'id'               => $item->id,
                                    'nombre'           => $item->nombre,
                                    'transferirUrl'    => $item->stock_actual > 0
                                        ? route($esTecnico ? 'admin.inventario.reventa.transferir.create' : 'admin.inventario.reventa.transferir_tecnico.create', ['producto_id' => $item->id])
                                        : null,
                                    'transferirTitulo' => $esTecnico ? 'Transferir a reventa' : 'Transferir a técnico',
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <p class="empty-state-text">
                    @if ($q || $marca || $linea || $tipoProducto)
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

    @include('admin.inventario.productos._modal_eliminar')
@endsection

@include('admin.inventario._filtro_live')

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
