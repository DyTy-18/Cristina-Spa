@extends('admin.layouts.app')

@section('title', 'Catálogo de Productos')
@section('page-title', 'Productos')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Catálogo de Productos</h3>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline btn-sm">Ver Stock</a>
                <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-primary btn-sm">+ Nuevo Producto</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.inventario.productos') }}" class="table-filter">
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
                <a href="{{ route('admin.inventario.productos') }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($productos->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código de Barras</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        <th style="text-align:right;">Costo</th>
                        <th style="text-align:right;">Stock Mínimo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $producto->codigo_barras }}</code></td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->marca ?? '—' }}</td>
                            <td>{{ $producto->linea ?? '—' }}</td>
                            <td style="text-align:right;">Bs. {{ number_format($producto->costo, 2) }}</td>
                            <td style="text-align:right;">{{ $producto->stock_minimo }}</td>
                            <td>
                                <a href="{{ route('admin.inventario.productos.edit', $producto) }}"
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
                        No hay productos registrados
                    @endif
                </p>
                @if (!$q && !$marca && !$linea)
                    <a href="{{ route('admin.inventario.productos.create') }}" class="btn btn-primary">Agregar Producto</a>
                @endif
            </div>
        @endif
    </div>
@endsection
