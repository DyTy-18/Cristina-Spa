@extends('admin.layouts.app')

@section('title', 'Historial de Salidas')
@section('page-title', 'Salidas de Mercancía')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Historial de Salidas</h3>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline btn-sm">Ver Stock</a>
                <a href="{{ route('admin.inventario.salidas.create') }}" class="btn btn-primary btn-sm">+ Registrar Salida</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.inventario.salidas') }}" class="table-filter">
            <input type="text" name="q" class="search-input"
                   placeholder="Buscar por nombre o código..."
                   value="{{ $q ?? '' }}">

            <select name="marca" class="filter-date" onchange="this.form.submit()"
                    style="min-width:130px;">
                <option value="">Todas las marcas</option>
                @foreach ($marcas as $m)
                    <option value="{{ $m }}" {{ ($marca ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>

            <select name="linea" class="filter-date" style="min-width:130px;">
                <option value="">Todas las líneas</option>
                @foreach ($lineas as $l)
                    <option value="{{ $l }}" {{ ($linea ?? '') === $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            <div class="filter-group">
                <label>Desde</label>
                <input type="date" name="desde" class="filter-date" value="{{ $desde ?? '' }}">
            </div>
            <div class="filter-group">
                <label>Hasta</label>
                <input type="date" name="hasta" class="filter-date" value="{{ $hasta ?? '' }}">
            </div>

            <input type="text" name="destino" class="filter-date"
                   placeholder="Destino..." value="{{ $destino ?? '' }}"
                   style="min-width:120px; max-width:160px;">

            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            @if ($q || $marca || $linea || $desde || $hasta || $destino)
                <a href="{{ route('admin.inventario.salidas') }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($salidas->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        <th style="text-align:right;">Unidades</th>
                        <th>Destino</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salidas as $salida)
                        <tr>
                            <td>{{ $salida->fecha->format('d/m/Y') }}</td>
                            <td><code style="font-size:0.8rem;">{{ $salida->codigo_barras }}</code></td>
                            <td>{{ $salida->producto->nombre ?? '—' }}</td>
                            <td>{{ $salida->producto->marca ?? '—' }}</td>
                            <td>{{ $salida->producto->linea ?? '—' }}</td>
                            <td style="text-align:right;">
                                <span class="badge badge-danger">&minus;{{ $salida->unidades }}</span>
                            </td>
                            <td>{{ $salida->destino ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:1rem 1.5rem;">
                {{ $salidas->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📤</div>
                <p class="empty-state-text">
                    @if ($q || $marca || $linea || $desde || $hasta || $destino)
                        No se encontraron salidas con los filtros aplicados
                    @else
                        No hay salidas registradas
                    @endif
                </p>
                @if (!$q && !$marca && !$linea && !$desde && !$hasta && !$destino)
                    <a href="{{ route('admin.inventario.salidas.create') }}" class="btn btn-primary">Registrar Primera Salida</a>
                @endif
            </div>
        @endif
    </div>
@endsection
