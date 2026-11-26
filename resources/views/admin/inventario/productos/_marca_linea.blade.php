{{--
    Marca y línea como desplegables con lo que ya existe en el sistema (todas las
    sucursales y catálogos), con opción "+ Nueva…" para escribir una que no exista.
    Los valores se envían en los inputs name="marca" / name="linea".
    Recibe opcionalmente $marcaActual y $lineaActual.
--}}
@php
    $mlMarcas = \App\Models\Producto::whereNotNull('marca')->where('marca', '<>', '')
        ->distinct()->orderBy('marca')->pluck('marca');
    $mlLineas = \App\Models\Producto::whereNotNull('linea')->where('linea', '<>', '')
        ->distinct()->orderBy('linea')->pluck('linea');
    $mlMarcaLineas = \App\Models\Producto::whereNotNull('marca')->whereNotNull('linea')->where('linea', '<>', '')
        ->select('marca', 'linea')->distinct()->orderBy('linea')->get()
        ->groupBy('marca')->map(fn ($g) => $g->pluck('linea')->values());
    $mlMarca = $marcaActual ?? '';
    $mlLinea = $lineaActual ?? '';
@endphp

<div class="form-row">
    <div class="form-group">
        <label class="form-label" for="marca_sel">Marca</label>
        <select id="marca_sel" class="form-control">
            <option value="">— Sin marca —</option>
            @foreach ($mlMarcas as $m)
                <option value="{{ $m }}" {{ $mlMarca === $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
            <option value="__nueva__" {{ $mlMarca !== '' && ! $mlMarcas->contains($mlMarca) ? 'selected' : '' }}>+ Nueva marca…</option>
        </select>
        <input type="text" id="marca" name="marca" class="form-control" value="{{ $mlMarca }}"
               placeholder="Escribe la nueva marca" maxlength="100" style="margin-top:0.4rem;display:none;">
    </div>

    <div class="form-group">
        <label class="form-label" for="linea_sel">Línea</label>
        <select id="linea_sel" class="form-control"></select>
        <input type="text" id="linea" name="linea" class="form-control" value="{{ $mlLinea }}"
               placeholder="Escribe la nueva línea" maxlength="100" style="margin-top:0.4rem;display:none;">
    </div>
</div>

@push('scripts')
<script>
(function () {
    const marcaLineas = @json($mlMarcaLineas);
    const todasLineas = @json($mlLineas);
    const NUEVA = '__nueva__';
    const marcaSel = document.getElementById('marca_sel'), marcaIn = document.getElementById('marca');
    const lineaSel = document.getElementById('linea_sel'), lineaIn = document.getElementById('linea');

    function syncInput(sel, input, limpiar) {
        const nueva = sel.value === NUEVA;
        input.style.display = nueva ? '' : 'none';
        if (nueva) {
            if (limpiar) { input.value = ''; input.focus(); }
        } else {
            input.value = sel.value;
        }
    }

    // Líneas de la marca elegida; si la marca es nueva o no tiene líneas, todas.
    function cargarLineas() {
        const actual = lineaIn.value;
        const lista = (marcaSel.value && marcaSel.value !== NUEVA && (marcaLineas[marcaSel.value] || []).length)
            ? marcaLineas[marcaSel.value] : todasLineas;

        lineaSel.innerHTML = '';
        lineaSel.appendChild(new Option('— Sin línea —', ''));
        lista.forEach(l => lineaSel.appendChild(new Option(l, l)));
        lineaSel.appendChild(new Option('+ Nueva línea…', NUEVA));

        if (!actual) lineaSel.value = '';
        else if (lista.includes(actual)) lineaSel.value = actual;
        else if (todasLineas.includes(actual)) lineaSel.value = ''; // no pertenece a esta marca
        else lineaSel.value = NUEVA; // línea escrita a mano

        syncInput(lineaSel, lineaIn, false);
    }

    marcaSel.addEventListener('change', () => { syncInput(marcaSel, marcaIn, true); cargarLineas(); });
    lineaSel.addEventListener('change', () => syncInput(lineaSel, lineaIn, true));

    syncInput(marcaSel, marcaIn, false);
    cargarLineas();
})();
</script>
@endpush
