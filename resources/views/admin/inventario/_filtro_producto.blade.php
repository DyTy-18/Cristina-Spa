{{--
    Buscador de producto por nombre/código, marca, línea y tipo (solo cliente, no afecta el POST).
    Filtra las opciones del <select id="producto_id">, que deben llevar
    data-nombre, data-codigo, data-marca, data-linea y data-tipo.
    Recibe $productos.
--}}
@php
    $fpMarcas    = $productos->pluck('marca')->filter()->unique()->sort()->values();
    $fpLineas    = $productos->pluck('linea')->filter()->unique()->sort()->values();
    $fpMarcaLineas = $productos->whereNotNull('marca')->whereNotNull('linea')
        ->groupBy('marca')->map(fn ($p) => $p->pluck('linea')->unique()->sort()->values());
    $fpTipos     = $productos->pluck('tipo')->filter()->unique()
        ->mapWithKeys(fn ($t) => [$t => \App\Models\Producto::TIPOS[$t] ?? $t])->sort();
@endphp

<div class="form-group">
    <label class="form-label">Buscar producto</label>
    <div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-bottom:0.5rem;">
        <input type="text" id="fp_q" class="form-control" placeholder="Nombre o código..."
               style="flex:1; min-width:180px;" autocomplete="off">

        <select id="fp_marca" class="form-control" style="min-width:140px; flex:none;">
            <option value="">Todas las marcas</option>
            @foreach ($fpMarcas as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>

        <select id="fp_linea" class="form-control" style="min-width:140px; flex:none;">
            <option value="">Todas las líneas</option>
            @foreach ($fpLineas as $l)
                <option value="{{ $l }}">{{ $l }}</option>
            @endforeach
        </select>

        @if ($fpTipos->count() > 1)
            <select id="fp_tipo" class="form-control" style="min-width:130px; flex:none;">
                <option value="">Todos los tipos</option>
                @foreach ($fpTipos as $valor => $etiqueta)
                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                @endforeach
            </select>
        @endif

        <button type="button" class="btn btn-outline btn-sm" id="fp_limpiar">Limpiar</button>
    </div>
    <span id="fp_count" style="font-size:0.75rem; color:#888;"></span>
</div>

@push('scripts')
<script>
(function () {
    const marcaLineas = @json($fpMarcaLineas);
    const todasLineas = @json($fpLineas);
    const $ = id => document.getElementById(id);
    const select = $('producto_id');
    if (!select) return;

    function filtrar() {
        const q     = $('fp_q').value.toLowerCase().trim();
        const marca = $('fp_marca').value;
        const linea = $('fp_linea').value;
        const tipo  = $('fp_tipo') ? $('fp_tipo').value : '';
        let visibles = 0, unica = null;

        Array.from(select.options).forEach(opt => {
            if (!opt.value) return;
            const d = opt.dataset;
            const ok = (!q || (d.nombre || '').toLowerCase().includes(q) || (d.codigo || '').toLowerCase().includes(q))
                && (!marca || d.marca === marca)
                && (!linea || d.linea === linea)
                && (!tipo || d.tipo === tipo);
            opt.hidden = !ok;
            if (ok) { visibles++; unica = opt; }
        });

        const sel = select.options[select.selectedIndex];
        if (sel && sel.value && sel.hidden) {
            select.value = '';
            select.dispatchEvent(new Event('change'));
        }
        // Si el filtro deja un solo producto, se selecciona solo.
        if (visibles === 1 && select.value !== unica.value) {
            select.value = unica.value;
            select.dispatchEvent(new Event('change'));
        }

        $('fp_count').textContent = visibles === 0 ? 'Sin resultados'
            : visibles + ' producto' + (visibles !== 1 ? 's' : '');
    }

    function actualizarLineas() {
        const marca = $('fp_marca').value;
        const actual = $('fp_linea').value;
        $('fp_linea').innerHTML = '<option value="">Todas las líneas</option>';
        (marca ? (marcaLineas[marca] || []) : todasLineas).forEach(l => {
            const o = new Option(l, l, false, l === actual);
            $('fp_linea').appendChild(o);
        });
        filtrar();
    }

    $('fp_q').addEventListener('input', filtrar);
    $('fp_marca').addEventListener('change', actualizarLineas);
    $('fp_linea').addEventListener('change', filtrar);
    if ($('fp_tipo')) $('fp_tipo').addEventListener('change', filtrar);
    $('fp_limpiar').addEventListener('click', () => {
        $('fp_q').value = '';
        $('fp_marca').value = '';
        if ($('fp_tipo')) $('fp_tipo').value = '';
        actualizarLineas();
    });

    filtrar();
})();
</script>
@endpush
