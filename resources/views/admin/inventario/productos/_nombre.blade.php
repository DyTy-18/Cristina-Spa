{{--
    Nombre del producto con sugerencias: mientras se escribe se listan los nombres que ya
    existen en el sistema (todas las sucursales y catálogos) con su marca y línea. Se puede
    elegir uno o seguir escribiendo un nombre nuevo. El valor va en el input name="nombre".
    Al elegir una sugerencia, si la marca/línea están vacías se completan con las de ese
    producto (ver _marca_linea). Recibe opcionalmente $nombreActual.
--}}
@php
    // Un nombre puede repetirse (varias sucursales / códigos): se toma su marca y línea más reciente.
    $nbProductos = \App\Models\Producto::whereNotNull('nombre')->where('nombre', '<>', '')
        ->orderByDesc('id')->get(['nombre', 'marca', 'linea'])
        ->unique('nombre')->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
        ->map(fn ($p) => ['nombre' => $p->nombre, 'marca' => $p->marca, 'linea' => $p->linea])
        ->values();
@endphp

<div class="form-group nb-combo">
    <label class="form-label" for="nombre">Nombre del Producto *</label>
    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ $nombreActual ?? '' }}"
           placeholder="Escribe para buscar o crear uno nuevo" maxlength="255" required autocomplete="off"
           role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="nombre_sugerencias">
    <ul id="nombre_sugerencias" class="nb-sugerencias" role="listbox" hidden></ul>
    <small id="nombre_estado" style="display:block; margin-top:0.3rem; font-size:0.75rem; color:#888; min-height:1em;"></small>
</div>

@once
@push('styles')
<style>
    .nb-combo { position: relative; }
    .nb-sugerencias {
        position: absolute; left: 0; right: 0; z-index: 50; margin: 2px 0 0; padding: 0.3rem 0;
        list-style: none; max-height: 260px; overflow-y: auto;
        background: var(--white); border: 1px solid rgba(0,0,0,0.12); box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .nb-sugerencias li { padding: 0.5rem 0.85rem; cursor: pointer; font-size: 0.88rem; }
    .nb-sugerencias li small { display: block; font-size: 0.72rem; color: #999; }
    .nb-sugerencias li[aria-selected="true"], .nb-sugerencias li:hover { background: var(--light-bg); }
    .nb-sugerencias mark { background: none; color: var(--accent-color); font-weight: 500; padding: 0; }
</style>
@endpush
@endonce

@push('scripts')
<script>
(function () {
    const productos = @json($nbProductos);
    const original  = @json($nombreActual ?? '');
    const input  = document.getElementById('nombre');
    const lista  = document.getElementById('nombre_sugerencias');
    const estado = document.getElementById('nombre_estado');
    const MAX = 30;
    let visibles = [], activo = -1;

    const normalizar = s => (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
    const escapar = s => (s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

    // Resalta la parte que coincide (sin distinguir mayúsculas ni tildes).
    function resaltar(texto, q) {
        const i = normalizar(texto).indexOf(q);
        if (!q || i < 0) return escapar(texto);
        return escapar(texto.slice(0, i)) + '<mark>' + escapar(texto.slice(i, i + q.length)) + '</mark>' + escapar(texto.slice(i + q.length));
    }

    function actualizarEstado() {
        const q = normalizar(input.value);
        // Sin texto, o el mismo nombre que ya tenía el producto (al editar): sin aviso.
        if (!q || q === normalizar(original)) { estado.textContent = ''; return; }
        const existe = productos.some(p => normalizar(p.nombre) === q);
        estado.textContent = existe ? 'Ya existe un producto con este nombre.' : 'Nombre nuevo: se creará tal como lo escribiste.';
    }

    function mostrar() {
        const q = normalizar(input.value);
        // Primero los que empiezan con lo escrito, luego los que lo contienen.
        const empiezan = [], contienen = [];
        productos.forEach(p => {
            const n = normalizar(p.nombre), m = normalizar(p.marca) + ' ' + normalizar(p.linea);
            if (!q || n.startsWith(q)) empiezan.push(p);
            else if (n.includes(q) || m.includes(q)) contienen.push(p);
        });
        visibles = empiezan.concat(contienen).slice(0, MAX);
        activo = -1;

        lista.innerHTML = visibles.map((p, i) => {
            const meta = [p.marca, p.linea].filter(Boolean).join(' · ');
            return '<li role="option" data-i="' + i + '" aria-selected="false">' + resaltar(p.nombre, q)
                + (meta ? '<small>' + escapar(meta) + '</small>' : '') + '</li>';
        }).join('');

        const abierta = visibles.length > 0 && document.activeElement === input;
        lista.hidden = !abierta;
        input.setAttribute('aria-expanded', abierta ? 'true' : 'false');
        actualizarEstado();
    }

    function cerrar() {
        lista.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    }

    function marcar(i) {
        activo = i;
        Array.from(lista.children).forEach((li, j) => li.setAttribute('aria-selected', j === i ? 'true' : 'false'));
        if (lista.children[i]) lista.children[i].scrollIntoView({ block: 'nearest' });
    }

    function elegir(p) {
        input.value = p.nombre;
        cerrar();
        actualizarEstado();
        completarMarcaLinea(p);
    }

    // Completa marca y línea del producto elegido, solo si el usuario aún no las puso.
    function completarMarcaLinea(p) {
        const marcaSel = document.getElementById('marca_sel'), lineaSel = document.getElementById('linea_sel');
        if (!marcaSel || marcaSel.value) return;
        if (p.marca) {
            marcaSel.value = p.marca;
            marcaSel.dispatchEvent(new Event('change'));
        }
        if (p.linea && lineaSel && !lineaSel.value) {
            lineaSel.value = p.linea;
            lineaSel.dispatchEvent(new Event('change'));
        }
    }

    input.addEventListener('input', mostrar);
    input.addEventListener('focus', mostrar);
    input.addEventListener('blur', () => setTimeout(cerrar, 120));

    input.addEventListener('keydown', e => {
        if (lista.hidden) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); marcar(Math.min(activo + 1, visibles.length - 1)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); marcar(Math.max(activo - 1, 0)); }
        else if (e.key === 'Enter' && activo >= 0) { e.preventDefault(); elegir(visibles[activo]); }
        else if (e.key === 'Escape') { cerrar(); }
    });

    // mousedown (no click) para elegir antes de que el blur cierre la lista.
    lista.addEventListener('mousedown', e => {
        const li = e.target.closest('li');
        if (!li) return;
        e.preventDefault();
        elegir(visibles[+li.dataset.i]);
    });

    actualizarEstado();
})();
</script>
@endpush
