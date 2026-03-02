@extends('admin.layouts.app')

@section('title', 'Inventario - Stock Actual')
@section('page-title', 'Inventario')

@section('content')
    <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary">+ Registrar Entrada</a>
        <a href="{{ route('admin.inventario.salidas.create') }}" class="btn btn-accent">+ Registrar Salida</a>
        <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-outline">+ Nuevo Producto</a>
        <a href="{{ route('admin.inventario.entradas') }}" class="btn btn-outline btn-sm">Historial Entradas</a>
        <a href="{{ route('admin.inventario.salidas') }}" class="btn btn-outline btn-sm">Historial Salidas</a>
        <a href="{{ route('admin.inventario.productos') }}" class="btn btn-outline btn-sm">Catálogo</a>
        <a href="{{ route('admin.inventario.importar') }}" class="btn btn-outline btn-sm">Importar Excel</a>
        <a href="{{ route('admin.inventario.analisis') }}" class="btn btn-outline btn-sm">📊 Análisis</a>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Stock Actual</h3>
            <span style="font-size:0.8rem; color:#888;">Entradas &minus; Salidas por producto</span>
        </div>

        <form method="GET" action="{{ route('admin.inventario.index') }}" class="table-filter">
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
                <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
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
                        <th>Costo</th>
                        <th style="text-align:right;">Entradas</th>
                        <th style="text-align:right;">Salidas</th>
                        <th style="text-align:right;">Stock</th>
                        <th style="text-align:right;">Mínimo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stock as $item)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $item->codigo_barras }}</code></td>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->marca ?? '—' }}</td>
                            <td>{{ $item->linea ?? '—' }}</td>
                            <td>Bs. {{ number_format($item->costo, 2) }}</td>
                            <td style="text-align:right;">{{ $item->total_entradas }}</td>
                            <td style="text-align:right;">{{ $item->total_salidas }}</td>
                            <td style="text-align:right;">
                                @if ($item->stock_actual < $item->stock_minimo)
                                    <span class="badge badge-danger">{{ $item->stock_actual }}</span>
                                @else
                                    <span class="badge badge-success">{{ $item->stock_actual }}</span>
                                @endif
                            </td>
                            <td style="text-align:right;">{{ $item->stock_minimo }}</td>
                            <td>
                                <a href="{{ route('admin.inventario.productos.edit', $item->id) }}"
                                   class="btn btn-outline btn-sm">Editar</a>
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
