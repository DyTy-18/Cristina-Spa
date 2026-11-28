@extends('admin.layouts.app')

@section('title', 'Transferir a Técnico')
@section('page-title', 'Transferir a Técnico')

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transferir stock Reventa → Técnico</h3>
        </div>
        <div class="card-body">
            <p style="color:#888; font-size:0.85rem; margin-top:-0.5rem; margin-bottom:1.25rem;">
                Devuelve unidades del inventario de reventa al inventario técnico del mismo producto.
                Esto registra una salida en el stock de reventa y una entrada en el stock técnico.
                El producto sigue siendo de reventa con su tipo; para quitarlo de reventa, edítalo cuando su stock de reventa esté en 0.
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
                        No hay productos con stock de reventa en esta sucursal para transferir.
                    </p>
                    <a href="{{ route('admin.inventario.index', ['tipo' => 'reventa']) }}" class="btn btn-primary">Volver a Reventa</a>
                </div>
            @else
                <form action="{{ route('admin.inventario.reventa.transferir_tecnico.store') }}" method="POST" id="form_transferir">
                    @csrf
                    @include('admin.partials.sucursal_selector')

                    @include('admin.inventario._filtro_producto')

                    <div class="form-group">
                        <label class="form-label" for="producto_id">Producto (con stock de reventa) *</label>
                        <select id="producto_id" name="producto_id" class="form-control" required>
                            <option value="">— Seleccionar producto —</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}"
                                        data-nombre="{{ $producto->nombre }}"
                                        data-codigo="{{ $producto->codigo_barras }}"
                                        data-marca="{{ $producto->marca ?? '' }}"
                                        data-linea="{{ $producto->linea ?? '' }}"
                                        data-tipo="{{ $producto->tipo ?? '' }}"
                                        data-stock="{{ $producto->stock_actual }}"
                                    {{ old('producto_id', $productoInicial) == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->nombre }}
                                    @if ($producto->marca) — {{ $producto->marca }} @endif
                                    @if ($producto->linea) / {{ $producto->linea }} @endif
                                    ({{ $producto->codigo_barras }})@if ($modoGlobal ?? false) · {{ $producto->sucursal->nombre ?? 'Sin sucursal' }}@endif
                                </option>
                            @endforeach
                        </select>
                        <span id="stock_actual_info" style="display:none; margin-top:0.4rem; font-size:0.85rem;"></span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="unidades">Unidades a transferir *</label>
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

                    <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                        <button type="submit" class="btn btn-primary" id="btn_transferir">Transferir a Técnico</button>
                        <a href="{{ route('admin.inventario.index', ['tipo' => 'reventa']) }}" class="btn btn-outline">Cancelar</a>
                    </div>
                </form>
            @endif
        </div>
    </div>

@push('scripts')
<script>
function stockSeleccionado() {
    const select = document.getElementById('producto_id');
    const opt    = select.options[select.selectedIndex];
    return (opt && opt.value) ? (parseInt(opt.dataset.stock, 10) || 0) : null;
}

function mostrarStock() {
    const info  = document.getElementById('stock_actual_info');
    const stock = stockSeleccionado();

    if (stock === null) {
        info.style.display = 'none';
    } else {
        info.textContent = 'Stock de reventa disponible: ' + stock + ' uds.';
        info.style.color = stock <= 0 ? '#dc2626' : '#555';
        info.style.display = 'inline-block';
    }

    validarUnidades();
}

function validarUnidades() {
    const stock    = stockSeleccionado();
    const unidades = parseInt(document.getElementById('unidades').value, 10) || 0;
    const excede   = stock !== null && unidades > stock;

    document.getElementById('stock_warning').style.display = excede ? 'block' : 'none';
    document.getElementById('btn_transferir').disabled = excede;
}

if (document.getElementById('form_transferir')) {
    document.getElementById('producto_id').addEventListener('change', mostrarStock);
    document.getElementById('unidades').addEventListener('input', validarUnidades);
    // Evita el doble envío (dos transferencias por un doble clic).
    document.getElementById('form_transferir').addEventListener('submit', function () {
        document.getElementById('btn_transferir').disabled = true;
    });
    mostrarStock();
}
</script>
@endpush
@endsection
