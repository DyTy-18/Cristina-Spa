@extends('admin.layouts.app')

@section('title', 'Historial de Entradas')
@section('page-title', 'Entradas de Mercancía')

@section('content')
    @php $tipoStock = $tipoStock ?? 'tecnico'; @endphp
    <div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
        <a href="{{ route('admin.inventario.entradas') }}"
           class="btn btn-sm {{ $tipoStock === 'tecnico' ? 'btn-primary' : 'btn-outline' }}">Técnico</a>
        <a href="{{ route('admin.inventario.entradas', ['tipo' => 'reventa']) }}"
           class="btn btn-sm {{ $tipoStock === 'reventa' ? 'btn-primary' : 'btn-outline' }}">Reventa</a>
    </div>

    <div class="table-container">
        <div class="table-header">
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                <h3 class="table-title">Historial de Entradas {{ $tipoStock === 'reventa' ? '(Reventa)' : '(Técnico)' }}</h3>
                @include('admin.partials.sucursal_badge')
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('admin.inventario.index', ['tipo' => $tipoStock]) }}" class="btn btn-outline btn-sm">Ver Stock</a>
                @if ($tipoStock === 'tecnico')
                    <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary btn-sm">+ Registrar Entrada</a>
                @else
                    <a href="{{ route('admin.inventario.reventa.transferir.create') }}" class="btn btn-primary btn-sm">+ Transferir a Reventa</a>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('admin.inventario.entradas') }}" class="table-filter">
            <input type="hidden" name="tipo" value="{{ $tipoStock }}">
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
                <a href="{{ route('admin.inventario.entradas', ['tipo' => $tipoStock]) }}" class="btn btn-outline btn-sm">Limpiar</a>
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
                            <td>{{ $entrada->fecha?->format('d/m/Y') ?? 'Sin fecha de movimiento' }}</td>
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
                    @if ($tipoStock === 'tecnico')
                        <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary">Registrar Primera Entrada</a>
                    @else
                        <a href="{{ route('admin.inventario.reventa.transferir.create') }}" class="btn btn-primary">Transferir a Reventa</a>
                    @endif
                @endif
            </div>
        @endif
    </div>
@endsection
