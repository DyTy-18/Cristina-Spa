@extends('admin.layouts.app')

@section('title', 'Editar Producto')
@section('page-title', 'Editar Producto')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $producto->nombre }}</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div style="padding:0.75rem 1rem; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; margin-bottom:1rem;">
                    <ul style="margin:0; padding-left:1.2rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.inventario.productos.update', $producto) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="codigo_barras">Código de Barras *</label>
                        <input type="text" id="codigo_barras" name="codigo_barras"
                               class="form-control" value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control" value="{{ old('nombre', $producto->nombre) }}"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="marca">Marca</label>
                        <input type="text" id="marca" name="marca"
                               class="form-control" value="{{ old('marca', $producto->marca) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="linea">Línea</label>
                        <input type="text" id="linea" name="linea"
                               class="form-control" value="{{ old('linea', $producto->linea) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="costo">Costo (Bs.) *</label>
                        <input type="number" id="costo" name="costo"
                               class="form-control" value="{{ old('costo', $producto->costo) }}"
                               step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stock_minimo">Stock Mínimo (alerta) *</label>
                        <input type="number" id="stock_minimo" name="stock_minimo"
                               class="form-control" value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                               min="0" required>
                        <small style="color:#888; font-size:0.75rem;">
                            Alerta cuando el stock sea menor a este número.
                        </small>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="{{ route('admin.inventario.productos') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
