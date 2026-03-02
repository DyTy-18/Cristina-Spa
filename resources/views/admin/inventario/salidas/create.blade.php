@extends('admin.layouts.app')

@section('title', 'Registrar Salida')
@section('page-title', 'Registrar Salida')

@section('content')

@php
    $marcasDisponibles = $productos->pluck('marca')->filter()->unique()->sort()->values();
    $todasLineas       = $productos->pluck('linea')->filter()->unique()->sort()->values();
    $marcaLineasMap    = $productos
        ->whereNotNull('marca')->whereNotNull('linea')
        ->groupBy('marca')
        ->map(fn($p) => $p->pluck('linea')->unique()->sort()->values());
@endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nueva Salida de Mercancía</h3>
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

            <form action="{{ route('admin.inventario.salidas.store') }}" method="POST">
                @csrf

                {{-- ── Filtros de producto (solo cliente, no afectan el POST) ── --}}
                <div class="form-group">
                    <label class="form-label">Filtrar producto</label>
                    <div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                        <input type="text" id="filtro_q" class="form-control"
                               placeholder="Buscar por nombre o código..."
                               style="flex:1; min-width:180px;"
                               oninput="filtrarProductos()">

                        <select id="filtro_marca" class="form-control" style="min-width:140px; flex:none;"
                                onchange="actualizarLineas()">
                            <option value="">Todas las marcas</option>
                            @foreach ($marcasDisponibles as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>

                        <select id="filtro_linea" class="form-control" style="min-width:140px; flex:none;"
                                onchange="filtrarProductos()">
                            <option value="">Todas las líneas</option>
                            @foreach ($todasLineas as $l)
                                <option value="{{ $l }}">{{ $l }}</option>
                            @endforeach
                        </select>

                        <button type="button" class="btn btn-outline btn-sm"
                                onclick="limpiarFiltros()">Limpiar</button>
                    </div>
                    <span id="producto_count" style="font-size:0.75rem; color:#888;"></span>
                </div>

                {{-- ── Select de producto (el que se envía) ── --}}
                <div class="form-group">
                    <label class="form-label" for="codigo_barras">Producto * <small style="text-transform:none; letter-spacing:0;">(selecciona después de filtrar)</small></label>
                    <select id="codigo_barras" name="codigo_barras" class="form-control" required
                            style="max-height:220px; overflow-y:auto;">
                        <option value="">— Seleccionar producto —</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->codigo_barras }}"
                                    data-nombre="{{ $producto->nombre }}"
                                    data-marca="{{ $producto->marca ?? '' }}"
                                    data-linea="{{ $producto->linea ?? '' }}"
                                    data-codigo="{{ $producto->codigo_barras }}"
                                {{ old('codigo_barras') == $producto->codigo_barras ? 'selected' : '' }}>
                                {{ $producto->nombre }}
                                @if ($producto->marca) — {{ $producto->marca }} @endif
                                @if ($producto->linea) / {{ $producto->linea }} @endif
                                ({{ $producto->codigo_barras }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="unidades">Unidades *</label>
                        <input type="number" id="unidades" name="unidades"
                               class="form-control" value="{{ old('unidades', 1) }}"
                               min="1" required>
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
                           placeholder="Ej: Sucursal Centro, Uso interno, Cliente...">
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">Registrar Salida</button>
                    <a href="{{ route('admin.inventario.salidas') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
const marcaLineasMap = @json($marcaLineasMap);
const todasLineas    = @json($todasLineas);

function filtrarProductos() {
    const q     = document.getElementById('filtro_q').value.toLowerCase().trim();
    const marca = document.getElementById('filtro_marca').value;
    const linea = document.getElementById('filtro_linea').value;
    const select = document.getElementById('codigo_barras');
    let visible = 0;

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return;
        const matchQ     = !q     || opt.dataset.nombre.toLowerCase().includes(q) || opt.dataset.codigo.toLowerCase().includes(q);
        const matchMarca = !marca || opt.dataset.marca === marca;
        const matchLinea = !linea || opt.dataset.linea === linea;
        opt.hidden = !(matchQ && matchMarca && matchLinea);
        if (!opt.hidden) visible++;
    });

    // Si el seleccionado quedó oculto, limpiarlo
    const selected = select.querySelector('option:checked');
    if (selected && selected.value && selected.hidden) select.value = '';

    document.getElementById('producto_count').textContent =
        visible === 0 ? 'Sin resultados' : visible + ' producto' + (visible !== 1 ? 's' : '');
}

function actualizarLineas() {
    const marcaSel    = document.getElementById('filtro_marca').value;
    const lineaSel    = document.getElementById('filtro_linea');
    const lineaActual = lineaSel.value;

    lineaSel.innerHTML = '<option value="">Todas las líneas</option>';
    const lineas = marcaSel ? (marcaLineasMap[marcaSel] || []) : todasLineas;
    lineas.forEach(l => {
        const opt = document.createElement('option');
        opt.value = l;
        opt.textContent = l;
        if (l === lineaActual) opt.selected = true;
        lineaSel.appendChild(opt);
    });

    filtrarProductos();
}

function limpiarFiltros() {
    document.getElementById('filtro_q').value     = '';
    document.getElementById('filtro_marca').value = '';
    actualizarLineas();
}

// Inicializar contador
filtrarProductos();
</script>
@endpush
@endsection
