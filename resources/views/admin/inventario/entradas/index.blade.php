@extends('admin.layouts.app')

@section('title', 'Historial de Entradas')
@section('page-title', 'Entradas de Mercancía')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Historial de Entradas</h3>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline btn-sm">Ver Stock</a>
                <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary btn-sm">+ Registrar Entrada</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.inventario.entradas') }}" class="table-filter">
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

            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            @if ($q || $marca || $linea || $desde || $hasta)
                <a href="{{ route('admin.inventario.entradas') }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($entradas->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        <th style="text-align:right;">Unidades</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entradas as $entrada)
                        <tr>
                            <td>{{ $entrada->fecha->format('d/m/Y') }}</td>
                            <td><code style="font-size:0.8rem;">{{ $entrada->codigo_barras }}</code></td>
                            <td>{{ $entrada->producto->nombre ?? '—' }}</td>
                            <td>{{ $entrada->producto->marca ?? '—' }}</td>
                            <td>{{ $entrada->producto->linea ?? '—' }}</td>
                            <td style="text-align:right;">
                                <span class="badge badge-success">+{{ $entrada->unidades }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:1rem 1.5rem;">
                {{ $entradas->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📥</div>
                <p class="empty-state-text">
                    @if ($q || $marca || $linea || $desde || $hasta)
                        No se encontraron entradas con los filtros aplicados
                    @else
                        No hay entradas registradas
                    @endif
                </p>
                @if (!$q && !$marca && !$linea && !$desde && !$hasta)
                    <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary">Registrar Primera Entrada</a>
                @endif
            </div>
        @endif
    </div>
@endsection
