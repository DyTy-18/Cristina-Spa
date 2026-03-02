@extends('admin.layouts.app')

@section('title', 'Nuevo Producto')
@section('page-title', 'Nuevo Producto')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Producto</h3>
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

            <form action="{{ route('admin.inventario.productos.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="codigo_barras">Código de Barras *</label>
                        <input type="text" id="codigo_barras" name="codigo_barras"
                               class="form-control" value="{{ old('codigo_barras') }}"
                               placeholder="Ej: 7501234567890" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control" value="{{ old('nombre') }}"
                               placeholder="Ej: Shampoo Hidratante" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="marca">Marca</label>
                        <input type="text" id="marca" name="marca"
                               class="form-control" value="{{ old('marca') }}"
                               placeholder="Ej: L'Oréal">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="linea">Línea</label>
                        <input type="text" id="linea" name="linea"
                               class="form-control" value="{{ old('linea') }}"
                               placeholder="Ej: Tratamiento Capilar">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="costo">Costo (Bs.) *</label>
                        <input type="number" id="costo" name="costo"
                               class="form-control" value="{{ old('costo', '0.00') }}"
                               step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stock_minimo">Stock Mínimo (alerta) *</label>
                        <input type="number" id="stock_minimo" name="stock_minimo"
                               class="form-control" value="{{ old('stock_minimo', 5) }}"
                               min="0" required>
                        <small style="color:#888; font-size:0.75rem;">
                            Cuando el stock caiga por debajo de este número se mostrará una alerta.
                        </small>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    <a href="{{ route('admin.inventario.productos') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
