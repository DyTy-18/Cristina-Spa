@extends('admin.layouts.app')

@section('title', 'Web: Servicios y Precios')
@section('page-title', 'Web: Servicios y Precios')

@push('styles')
<style>
    .cw-help { font-size: .8rem; color: var(--text-light); margin-bottom: 1.25rem; line-height: 1.5; }
    .cw-toolbar {
        position: sticky; top: 0; z-index: 5;
        display: flex; gap: .75rem; align-items: center; flex-wrap: wrap;
        background: var(--light-bg, #faf8f5); padding: .75rem 0; margin-bottom: 1rem;
        border-bottom: 1px solid rgba(0,0,0,.08);
    }
    .cw-dirty { font-size: .75rem; color: #b45309; display: none; }
    .cw-dirty.visible { display: inline; }

    .cw-cat { background: var(--white); border: 1px solid rgba(0,0,0,.08); margin-bottom: .75rem; }
    .cw-cat > summary {
        list-style: none; cursor: pointer; padding: .9rem 1.1rem;
        display: flex; align-items: center; gap: .6rem;
        font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; color: var(--primary-color);
    }
    .cw-cat > summary::-webkit-details-marker { display: none; }
    .cw-cat > summary .cw-count { font-family: 'Montserrat', sans-serif; font-size: .7rem; color: var(--text-light); }
    .cw-cat > summary .cw-spacer { flex: 1; }
    .cw-cat[open] > summary { border-bottom: 1px solid rgba(0,0,0,.06); }
    .cw-cat-body { padding: 1rem 1.1rem 1.25rem; }

    .cw-grid { display: grid; grid-template-columns: 90px 1fr 1fr; gap: .75rem; margin-bottom: 1rem; }
    @media (max-width: 700px) { .cw-grid { grid-template-columns: 1fr; } }

    .cw-sec { border: 1px dashed rgba(0,0,0,.15); padding: .9rem; margin-bottom: .9rem; background: rgba(0,0,0,.015); }
    .cw-sec-head { display: flex; gap: .5rem; align-items: center; margin-bottom: .75rem; flex-wrap: wrap; }
    .cw-sec-head .form-control { flex: 1; min-width: 200px; }
    .cw-sub { font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-light); margin: .5rem 0 .4rem; }

    .cw-cols { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: .75rem; }
    .cw-col { display: flex; gap: .25rem; align-items: center; background: var(--white); border: 1px solid rgba(0,0,0,.08); padding: .3rem; }
    .cw-col input { width: 120px; }

    .cw-table-wrap { overflow-x: auto; }
    .cw-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    .cw-table th { text-align: left; font-weight: 500; color: var(--text-light); padding: .3rem; white-space: nowrap; }
    .cw-table td { padding: .2rem .3rem; }
    .cw-table td.cw-name { min-width: 220px; }
    .cw-table td.cw-price { min-width: 100px; }
    .cw-table td.cw-actions { white-space: nowrap; width: 1%; }

    .cw-input { width: 100%; padding: .35rem .5rem; font-size: .8rem; border: 1px solid rgba(0,0,0,.15); background: #fff; font-family: inherit; }
    .cw-input:focus { outline: none; border-color: var(--accent-color); }
    .cw-icon-btn {
        border: 1px solid rgba(0,0,0,.12); background: #fff; cursor: pointer;
        width: 28px; height: 28px; font-size: .8rem; line-height: 1; color: var(--text-dark);
    }
    .cw-icon-btn:hover { background: rgba(0,0,0,.05); }
    .cw-icon-btn.danger:hover { background: #fee2e2; color: #b91c1c; }
    .cw-icon-btn:disabled { opacity: .3; cursor: default; }
    .cw-add { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .6rem; }
    .cw-errors { margin-bottom: 1rem; }
    .cw-errors ul { margin: .4rem 0 0 1.1rem; }
</style>
@endpush

@section('content')
    @if ($errors->any())
        <div class="alert alert-error cw-errors">
            No se pudo guardar:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="cwForm" action="{{ route('admin.contenido-web.servicios.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="contenido" id="cwContenido">

        <div class="cw-toolbar">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('home') }}#servicios" target="_blank" class="btn btn-outline">Ver en la web ↗</a>
            <span class="cw-dirty" id="cwDirty">● Hay cambios sin guardar</span>
        </div>

        <p class="cw-help">
            Aquí editas la sección <strong>“Nuestros Servicios”</strong> de la página principal: categorías, subsecciones,
            servicios y precios. Los precios son texto libre (ej. <em>250 Bs.</em>, <em>Consultar</em>, <em>310 Bs. (1cm)</em>);
            lo que esté entre paréntesis al final se muestra en letra pequeña y una celda vacía se muestra como “—”.
            Una subsección con <strong>una sola columna</strong> de precio se muestra como lista; con <strong>varias columnas</strong>, como tabla.
        </p>

        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header"><h3 class="card-title">Encabezado de la sección</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Etiqueta superior</label>
                        <input type="text" class="form-control" data-campo="etiqueta" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título *</label>
                        <input type="text" class="form-control" data-campo="titulo" maxlength="150" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" rows="2" data-campo="descripcion" maxlength="1000"></textarea>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header"><h3 class="card-title">Catálogo PDF (botón de descarga)</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Texto del botón</label>
                        <input type="text" class="form-control" data-campo="catalogo_texto" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subir nuevo PDF (opcional, máx. 20 MB)</label>
                        <input type="file" class="form-control" name="catalogo_nuevo" accept="application/pdf" id="cwPdf">
                    </div>
                </div>
                <div id="cwPdfActual" style="font-size:.8rem;"></div>
            </div>
        </div>

        <div id="cwCategorias"></div>

        <div class="cw-add" style="margin-bottom: 2rem;">
            <button type="button" class="btn btn-accent" id="cwAddCat">+ Agregar categoría</button>
        </div>
    </form>

    <form action="{{ route('admin.contenido-web.servicios.restaurar') }}" method="POST"
        onsubmit="return confirm('¿Restaurar el contenido original? Se perderán todos los cambios guardados.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline btn-sm">Restaurar contenido original</button>
    </form>
@endsection

@php
    // Si la validación falló, se reabre el editor con lo que el usuario había enviado
    $contenidoInicial = old('contenido') ? (json_decode(old('contenido'), true) ?: $contenido) : $contenido;
@endphp

@push('scripts')
<script>
(function () {
    const PRESETS = {
        lista: [{ nombre: 'Precio', detalle: '' }],
        largo: [
            { nombre: 'Corto', detalle: '' },
            { nombre: 'Mediano', detalle: 'Hasta Hombros' },
            { nombre: 'Largo', detalle: 'Hasta Media Espalda' },
            { nombre: 'Extra Largo', detalle: 'Hasta Cintura' },
        ],
    };

    const inicial = @json($contenidoInicial);
    const data = structuredClone(inicial);
    data.categorias = data.categorias || [];
    const abiertas = new Set();
    let sucio = {{ old('contenido') ? 'true' : 'false' }};

    const contenedor = document.getElementById('cwCategorias');
    const dirtyEl = document.getElementById('cwDirty');

    function marcarSucio() {
        sucio = true;
        dirtyEl.classList.add('visible');
    }
    if (sucio) dirtyEl.classList.add('visible');

    function el(tag, attrs = {}, ...hijos) {
        const n = document.createElement(tag);
        for (const [k, v] of Object.entries(attrs)) {
            if (k === 'class') n.className = v;
            else if (k.startsWith('on')) n.addEventListener(k.slice(2), v);
            else if (k === 'value') n.value = v ?? '';
            else if (v !== false && v != null) n.setAttribute(k, v === true ? '' : v);
        }
        hijos.flat().forEach(h => h != null && n.append(h));
        return n;
    }

    // Input de texto ligado a obj[campo]; no re-renderiza (mantiene el foco)
    function campo(obj, key, attrs = {}, alCambiar) {
        return el('input', Object.assign({
            type: 'text', class: 'cw-input', value: obj[key] ?? '',
            oninput: e => { obj[key] = e.target.value; marcarSucio(); alCambiar && alCambiar(); },
        }, attrs));
    }

    function boton(texto, titulo, accion, extra = {}) {
        return el('button', Object.assign({ type: 'button', class: 'cw-icon-btn', title: titulo, onclick: accion }, extra), texto);
    }

    // Acción estructural: modifica, marca y vuelve a dibujar
    function cambiar(fn) {
        return () => { fn(); marcarSucio(); render(); };
    }

    function mover(arr, i, delta) {
        const j = i + delta;
        if (j < 0 || j >= arr.length) return;
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }

    function controlesOrden(arr, i, etiqueta) {
        return [
            boton('↑', 'Subir', cambiar(() => mover(arr, i, -1)), { disabled: i === 0 }),
            boton('↓', 'Bajar', cambiar(() => mover(arr, i, 1)), { disabled: i === arr.length - 1 }),
            boton('✕', 'Eliminar', () => {
                if (!confirm('¿Eliminar ' + etiqueta + '?')) return;
                arr.splice(i, 1); marcarSucio(); render();
            }, { class: 'cw-icon-btn danger' }),
        ];
    }

    function renderSeccion(cat, sec, si) {
        sec.columnas = sec.columnas || [];
        sec.items = sec.items || [];

        const cols = el('div', { class: 'cw-cols' },
            sec.columnas.map((col, ci) => el('div', { class: 'cw-col' },
                campo(col, 'nombre', { placeholder: 'Columna', maxlength: 60 }, () => {
                    const th = contenedor.querySelector(`[data-th="${cat._id}-${si}-${ci}"]`);
                    if (th) th.textContent = col.nombre || '—';
                }),
                campo(col, 'detalle', { placeholder: 'Detalle (opcional)', maxlength: 60 }),
                boton('✕', 'Quitar columna', () => {
                    if (sec.columnas.length <= 1) { alert('Debe quedar al menos una columna de precio.'); return; }
                    if (!confirm('¿Quitar la columna "' + (col.nombre || '') + '" y sus precios?')) return;
                    sec.columnas.splice(ci, 1);
                    sec.items.forEach(it => (it.precios || []).splice(ci, 1));
                    marcarSucio(); render();
                }, { class: 'cw-icon-btn danger' }),
            )),
            el('button', { type: 'button', class: 'btn btn-outline btn-sm', onclick: cambiar(() => {
                sec.columnas.push({ nombre: 'Nueva', detalle: '' });
            }) }, '+ Columna'),
        );

        const thead = el('tr', {},
            el('th', {}, 'Servicio'),
            sec.columnas.map((c, ci) => el('th', { 'data-th': `${cat._id}-${si}-${ci}` }, c.nombre || '—')),
            el('th', {}, ''),
        );

        const filas = sec.items.map((item, ii) => {
            item.precios = item.precios || [];
            return el('tr', {},
                el('td', { class: 'cw-name' }, campo(item, 'nombre', { placeholder: 'Nombre del servicio', maxlength: 150 })),
                sec.columnas.map((_, ci) => el('td', { class: 'cw-price' },
                    el('input', {
                        type: 'text', class: 'cw-input', value: item.precios[ci] ?? '', placeholder: '—', maxlength: 60,
                        oninput: e => { item.precios[ci] = e.target.value; marcarSucio(); },
                    }))),
                el('td', { class: 'cw-actions' }, controlesOrden(sec.items, ii, 'el servicio "' + (item.nombre || '') + '"')),
            );
        });

        return el('div', { class: 'cw-sec' },
            el('div', { class: 'cw-sec-head' },
                el('input', {
                    type: 'text', class: 'form-control', value: sec.titulo ?? '', maxlength: 150,
                    placeholder: 'Subtítulo de la subsección (opcional, ej. "Cortes")',
                    oninput: e => { sec.titulo = e.target.value; marcarSucio(); },
                }),
                controlesOrden(cat.secciones, si, 'esta subsección y todos sus servicios'),
            ),
            el('div', { class: 'cw-sub' }, 'Columnas de precio'),
            cols,
            el('div', { class: 'cw-sub' }, 'Servicios'),
            el('div', { class: 'cw-table-wrap' },
                el('table', { class: 'cw-table' }, el('thead', {}, thead), el('tbody', {}, filas))),
            el('div', { class: 'cw-add' },
                el('button', { type: 'button', class: 'btn btn-outline btn-sm', onclick: () => {
                    sec.items.push({ nombre: '', precios: sec.columnas.map(() => '') });
                    marcarSucio(); render();
                    const inputs = contenedor.querySelectorAll(`[data-sec="${cat._id}-${si}"] tbody tr:last-child input`);
                    inputs[0]?.focus();
                } }, '+ Agregar servicio'),
            ),
        );
    }

    let siguienteId = 1;

    function renderCategoria(cat, i) {
        cat._id = cat._id || siguienteId++;
        cat.secciones = cat.secciones || [];
        const total = cat.secciones.reduce((n, s) => n + (s.items || []).length, 0);

        const resumenNombre = el('span', {}, `${cat.icono || ''} ${cat.nombre || 'Sin nombre'}`.trim());
        const det = el('details', { class: 'cw-cat', open: abiertas.has(cat._id) },
            el('summary', {},
                resumenNombre,
                el('span', { class: 'cw-count' }, `${total} servicio${total === 1 ? '' : 's'}`),
                el('span', { class: 'cw-spacer' }),
                el('span', { onclick: e => e.preventDefault() }, controlesOrden(data.categorias, i, 'la categoría "' + (cat.nombre || '') + '" completa')),
            ),
            el('div', { class: 'cw-cat-body' },
                el('div', { class: 'cw-grid' },
                    el('div', {}, el('label', { class: 'form-label' }, 'Ícono'),
                        campo(cat, 'icono', { maxlength: 20, placeholder: '✂️' }, actualizarResumen)),
                    el('div', {}, el('label', { class: 'form-label' }, 'Nombre de la categoría *'),
                        campo(cat, 'nombre', { maxlength: 100 }, actualizarResumen)),
                    el('div', {}, el('label', { class: 'form-label' }, 'Nota al pie (opcional)'),
                        campo(cat, 'nota', { maxlength: 255, placeholder: '* Precios previa evaluación' })),
                ),
                cat.secciones.map((sec, si) => {
                    const nodo = renderSeccion(cat, sec, si);
                    nodo.dataset.sec = `${cat._id}-${si}`;
                    return nodo;
                }),
                el('div', { class: 'cw-add' },
                    el('button', { type: 'button', class: 'btn btn-outline btn-sm', onclick: cambiar(() => {
                        cat.secciones.push({ titulo: '', columnas: structuredClone(PRESETS.lista), items: [] });
                    }) }, '+ Subsección tipo lista (un precio)'),
                    el('button', { type: 'button', class: 'btn btn-outline btn-sm', onclick: cambiar(() => {
                        cat.secciones.push({ titulo: '', columnas: structuredClone(PRESETS.largo), items: [] });
                    }) }, '+ Subsección tipo tabla (por largo de cabello)'),
                ),
            ),
        );
        det.addEventListener('toggle', () => det.open ? abiertas.add(cat._id) : abiertas.delete(cat._id));

        function actualizarResumen() {
            resumenNombre.textContent = `${cat.icono || ''} ${cat.nombre || 'Sin nombre'}`.trim();
        }
        return det;
    }

    function render() {
        contenedor.replaceChildren(...data.categorias.map(renderCategoria));
    }

    // Campos simples del encabezado / catálogo
    document.querySelectorAll('[data-campo]').forEach(input => {
        input.value = data[input.dataset.campo] ?? '';
        input.addEventListener('input', () => { data[input.dataset.campo] = input.value; marcarSucio(); });
    });

    function renderPdfActual() {
        const box = document.getElementById('cwPdfActual');
        box.replaceChildren();
        if (data.catalogo_pdf) {
            box.append(
                'PDF actual: ',
                el('a', { href: '{{ url('/') }}/' + encodeURI(data.catalogo_pdf), target: '_blank' }, data.catalogo_pdf.split('/').pop()),
                ' ',
                el('button', { type: 'button', class: 'btn btn-outline btn-sm', style: 'margin-left:.5rem', onclick: () => {
                    if (!confirm('¿Quitar el botón de catálogo de la web?')) return;
                    data.catalogo_pdf = ''; marcarSucio(); renderPdfActual();
                } }, 'Quitar catálogo'),
            );
        } else {
            box.append(el('em', { style: 'color: var(--text-light)' }, 'Sin catálogo: el botón no se muestra en la web (sube un PDF para activarlo).'));
        }
    }
    document.getElementById('cwPdf').addEventListener('change', marcarSucio);

    document.getElementById('cwAddCat').addEventListener('click', () => {
        const cat = { nombre: 'Nueva categoría', icono: '', nota: '',
            secciones: [{ titulo: '', columnas: structuredClone(PRESETS.lista), items: [] }] };
        cat._id = siguienteId++;
        abiertas.add(cat._id);
        data.categorias.push(cat);
        marcarSucio(); render();
        contenedor.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('cwForm').addEventListener('submit', () => {
        const limpio = JSON.parse(JSON.stringify(data, (k, v) => k === '_id' ? undefined : v));
        document.getElementById('cwContenido').value = JSON.stringify(limpio);
        sucio = false;
    });

    window.addEventListener('beforeunload', e => {
        if (sucio) { e.preventDefault(); e.returnValue = ''; }
    });

    renderPdfActual();
    render();
})();
</script>
@endpush
