@extends('admin.layouts.app')

@section('title', 'Registrar Venta de Reventa')
@section('page-title', 'Registrar Venta de Reventa')

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nueva Salida del Inventario de Reventa</h3>
        </div>
        <div class="card-body">
            <p style="color:#888; font-size:0.85rem; margin-top:-0.5rem; margin-bottom:1.25rem;">
                Registra una venta o salida de stock desde el inventario de reventa (no afecta el stock técnico).
            </p>

            @if ($errors->any())
                <div style="padding:0.75rem 1rem; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; margin-bottom:1rem;">
                    <ul style="margin:0; padding-left:1.2rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($productos->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p class="empty-state-text">
                        No hay productos marcados como "producto de reventa" todavía.
                    </p>
                    <a href="{{ route('admin.inventario.productos') }}" class="btn btn-primary">Ir al Catálogo</a>
                </div>
            @else
                <form action="{{ route('admin.inventario.reventa.salida.store') }}" method="POST">
                    @csrf
                    @include('admin.partials.sucursal_selector')

                    <div class="form-group">
                        <label class="form-label" for="codigo_barras">Producto (reventa) *</label>
                        <select id="codigo_barras" name="codigo_barras" class="form-control" required>
                            <option value="">— Seleccionar producto —</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->codigo_barras }}"
                                        data-stock="{{ $producto->stock_actual }}"
                                    {{ old('codigo_barras') == $producto->codigo_barras ? 'selected' : '' }}>
                                    {{ $producto->nombre }}
                                    @if ($producto->marca) — {{ $producto->marca }} @endif
                                    ({{ $producto->codigo_barras }})
                                </option>
                            @endforeach
                        </select>
                        <span id="stock_actual_info" style="display:none; margin-top:0.4rem; font-size:0.85rem;"></span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="unidades">Unidades *</label>
                            <input type="number" id="unidades" name="unidades"
                                   class="form-control" value="{{ old('unidades', 1) }}"
                                   min="1" required>
                            <span id="stock_warning" style="display:none; color:#dc2626; font-size:0.8rem;">
                                La cantidad supera el stock de reventa disponible.
                            </span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fecha">Fecha *</label>
                            <input type="date" id="fecha" name="fecha"
                                   class="form-control" value="{{ old('fecha', date('Y-m-d')) }}"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="destino">Destino</label>
                        <input type="text" id="destino" name="destino"
                               class="form-control" value="{{ old('destino') }}"
                               placeholder="Ej: Cliente, mostrador...">
                    </div>

                    <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                        <button type="submit" class="btn btn-primary">Registrar Venta</button>
                        <a href="{{ route('admin.inventario.index', ['tipo' => 'reventa']) }}" class="btn btn-outline">Cancelar</a>
                    </div>
                </form>
            @endif
        </div>
    </div>

@push('scripts')
<script>
function stockSeleccionado() {
    const select = document.getElementById('codigo_barras');
    const opt    = select.options[select.selectedIndex];
    return (opt && opt.value) ? (parseInt(opt.dataset.stock, 10) || 0) : null;
}

function mostrarStock() {
    const info  = document.getElementById('stock_actual_info');
    const stock = stockSeleccionado();

    if (stock === null) {
        info.style.display = 'none';
    } else {
        info.textContent = 'Stock reventa disponible: ' + stock + ' uds.';
        info.style.color = stock <= 0 ? '#dc2626' : '#555';
        info.style.display = 'inline-block';
    }

    validarUnidades();
}

function validarUnidades() {
    const stock    = stockSeleccionado();
    const unidades = parseInt(document.getElementById('unidades').value, 10) || 0;
    const warning  = document.getElementById('stock_warning');

    warning.style.display = (stock !== null && unidades > stock) ? 'block' : 'none';
}

document.getElementById('codigo_barras').addEventListener('change', mostrarStock);
document.getElementById('unidades').addEventListener('input', validarUnidades);

mostrarStock();
</script>
@endpush
@endsection
