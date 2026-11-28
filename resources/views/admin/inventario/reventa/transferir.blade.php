@extends('admin.layouts.app')

@section('title', 'Transferir a Reventa')
@section('page-title', 'Transferir a Reventa')

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transferir stock Técnico → Reventa</h3>
        </div>
        <div class="card-body">
            <p style="color:#888; font-size:0.85rem; margin-top:-0.5rem; margin-bottom:1.25rem;">
                Mueve unidades del inventario técnico al inventario de reventa del mismo producto.
                Esto registra una salida en el stock técnico y una entrada en el stock de reventa.
                Cualquier producto técnico con stock en esta sucursal puede transferirse.
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
                        No hay productos con stock técnico en esta sucursal para transferir.
                    </p>
                    <a href="{{ route('admin.inventario.entradas.create') }}" class="btn btn-primary">Registrar entrada técnica</a>
                </div>
            @else
                <form action="{{ route('admin.inventario.reventa.transferir.store') }}" method="POST" id="form_transferir">
                    @csrf
                    @include('admin.partials.sucursal_selector')

                    @include('admin.inventario._filtro_producto')

                    <div class="form-group">
                        <label class="form-label" for="producto_id">Producto (con stock técnico) *</label>
                        <select id="producto_id" name="producto_id" class="form-control" required>
                            <option value="">— Seleccionar producto —</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}"
                                        data-nombre="{{ $producto->nombre }}"
                                        data-codigo="{{ $producto->codigo_barras }}"
                                        data-marca="{{ $producto->marca ?? '' }}"
                                        data-linea="{{ $producto->linea ?? '' }}"
                                        data-tipo="{{ $producto->tipo ?? '' }}"
                                        data-reventa="{{ $producto->es_reventa ? 1 : 0 }}"
                                        data-peso="{{ $producto->peso_gramos ?? '' }}"
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

                    @include('admin.inventario.productos._tipo_selector', [
                        'etiqueta'   => 'Transferir como',
                        'tipoActual' => old('tipo', \App\Models\Producto::TIPO_VITRINA),
                        'pesoActual' => old('peso_gramos'),
                    ])

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="unidades">Unidades a transferir *</label>
                            <input type="number" id="unidades" name="unidades"
                                   class="form-control" value="{{ old('unidades', 1) }}"
                                   min="1" required>
                            <span id="stock_warning" style="display:none; color:#dc2626; font-size:0.8rem;">
                                La cantidad supera el stock técnico disponible.
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
                        <button type="submit" class="btn btn-primary" id="btn_transferir">Transferir a Reventa</button>
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
        info.textContent = 'Stock técnico disponible: ' + stock + ' uds.';
        info.style.color = stock <= 0 ? '#dc2626' : '#555';
        info.style.display = 'inline-block';
    }

    validarUnidades();
}

function validarUnidades() {
    const stock    = stockSeleccionado();
    const unidades = parseInt(document.getElementById('unidades').value, 10) || 0;
    const warning  = document.getElementById('stock_warning');

    const excede = stock !== null && unidades > stock;
    warning.style.display = excede ? 'block' : 'none';
    document.getElementById('btn_transferir').disabled = excede;
}

const TIPOS = @json(\App\Models\Producto::TIPOS);

// Al elegir un producto que ya es de reventa se propone su tipo y peso actuales.
function cargarTipoDelProducto(proponer = true) {
    const select = document.getElementById('producto_id');
    const opt    = select.options[select.selectedIndex];
    const info   = document.getElementById('tipo_info');

    if (opt && opt.value && opt.dataset.reventa === '1' && opt.dataset.tipo) {
        if (proponer) {
            const radio = document.querySelector('input[type="radio"][name="tipo"][value="' + opt.dataset.tipo + '"]');
            if (radio) radio.checked = true;
            if (opt.dataset.peso) document.getElementById('peso_gramos').value = opt.dataset.peso;
        }
        info.dataset.actual = opt.dataset.tipo;
    } else {
        delete info.dataset.actual;
    }
    actualizarPesoTipo();
}

function actualizarTipo() {
    const tipo   = tipoSeleccionado();
    const info   = document.getElementById('tipo_info');
    const actual = info.dataset.actual;

    // Un producto tiene un solo tipo: cambiarlo afecta también a su stock de reventa actual.
    if (actual && tipo && actual !== tipo) {
        info.textContent = 'Este producto ya es de reventa como ' + (TIPOS[actual] || actual)
            + '; al transferir pasará a ' + TIPOS[tipo] + '.';
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }
}

document.getElementById('producto_id').addEventListener('change', mostrarStock);
document.getElementById('producto_id').addEventListener('change', () => cargarTipoDelProducto());
// Las tarjetas avisan cada cambio de tipo (ver _tipo_selector).
document.addEventListener('tipo-producto-cambiado', actualizarTipo);
document.getElementById('unidades').addEventListener('input', validarUnidades);
// Evita el doble envío (dos transferencias por un doble clic).
document.getElementById('form_transferir').addEventListener('submit', function () {
    document.getElementById('btn_transferir').disabled = true;
});

mostrarStock();
// Tras un error de validación se respeta lo que el usuario había elegido.
cargarTipoDelProducto({{ old('tipo') ? 'false' : 'true' }});
</script>
@endpush
@endsection
