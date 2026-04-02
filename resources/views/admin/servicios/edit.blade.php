@extends('admin.layouts.app')

@section('title', 'Editar Servicio')
@section('page-title', 'Editar Servicio')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar: {{ $servicio->nombre }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.servicios.update', $servicio) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nombre del Servicio *</label>
                    <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $servicio->nombre) }}"
                        required>
                    @error('nombre')
                        <small style="color: var(--error-color);">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio ($) *</label>
                        <input type="number" class="form-control" name="precio" step="0.01" min="0"
                            value="{{ old('precio', $servicio->precio) }}" required>
                        @error('precio')
                            <small style="color: var(--error-color);">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración (minutos) *</label>
                        <input type="number" class="form-control" name="duracion_minutos" min="5" step="5"
                            value="{{ old('duracion_minutos', $servicio->duracion_minutos) }}" required>
                        @error('duracion_minutos')
                            <small style="color: var(--error-color);">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select class="form-control" name="categoria">
                        <option value="">Sin categoría</option>
                        <option value="cortes" {{ old('categoria', $servicio->categoria) == 'cortes' ? 'selected' : '' }}>
                            Cortes</option>
                        <option value="coloracion"
                            {{ old('categoria', $servicio->categoria) == 'coloracion' ? 'selected' : '' }}>Coloración
                        </option>
                        <option value="peinados"
                            {{ old('categoria', $servicio->categoria) == 'peinados' ? 'selected' : '' }}>Peinados</option>
                        <option value="tratamientos"
                            {{ old('categoria', $servicio->categoria) == 'tratamientos' ? 'selected' : '' }}>Tratamientos
                        </option>
                        <option value="spa" {{ old('categoria', $servicio->categoria) == 'spa' ? 'selected' : '' }}>Spa
                        </option>
                        <option value="eventos"
                            {{ old('categoria', $servicio->categoria) == 'eventos' ? 'selected' : '' }}>Eventos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="activo" value="1"
                            {{ old('activo', $servicio->activo) ? 'checked' : '' }}>
                        <span>Servicio activo</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
                    <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Materiales del Servicio ─────────────────────────── --}}
    <div class="card" style="margin-top: 2rem;" id="materiales-section">
        <div class="card-header">
            <h3 class="card-title">Materiales del Servicio</h3>
        </div>
        <div class="card-body">

            {{-- Tabla de materiales existentes --}}
            <table class="data-table" id="materiales-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th style="width:120px;">Cantidad</th>
                        <th style="width:100px;">Unidad</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="materiales-tbody">
                    {{-- Rows injected by JS on load --}}
                </tbody>
            </table>

            <div id="materiales-empty" style="display:none; padding: 1.5rem 0; color: var(--text-light); text-align:center;">
                Sin materiales registrados aún.
            </div>

            {{-- Formulario para agregar material --}}
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.06);">
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary-color);">
                    Agregar Material
                </h4>
                <div class="form-row" id="add-material-form">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Producto</label>
                        <select class="form-control" id="nuevo-producto-id">
                            <option value="">— seleccionar producto —</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}" data-marca="{{ $producto->marca }}">
                                    {{ $producto->nombre }} ({{ $producto->marca }})
                                </option>
                            @endforeach
                        </select>
                        <small id="add-error-producto" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="nuevo-cantidad" min="0.01" step="0.01" placeholder="ej: 30">
                        <small id="add-error-cantidad" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Unidad</label>
                        <input type="text" class="form-control" id="nuevo-unidad" list="unidades-list" placeholder="ej: ml">
                        <datalist id="unidades-list">
                            <option value="ml">
                            <option value="gr">
                            <option value="oz">
                            <option value="unidades">
                        </datalist>
                        <small id="add-error-unidad" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:0; align-self: flex-end;">
                        <button type="button" class="btn btn-primary" id="btn-agregar-material">Agregar</button>
                    </div>
                </div>
                <div id="add-general-error" style="color: var(--error-color); display:none; margin-top: 0.5rem;"></div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const SERVICIO_ID = {{ $servicio->id }};
    const BASE_URL    = '/admin/servicios/' + SERVICIO_ID + '/materiales';
    const CSRF        = '{{ csrf_token() }}';

    // State: list of materials loaded from PHP
    let materiales = @json($materiales->map(fn($m) => [
        'id'       => $m->id,
        'producto' => ['id' => $m->producto->id, 'nombre' => $m->producto->nombre, 'marca' => $m->producto->marca],
        'cantidad' => $m->cantidad,
        'unidad'   => $m->unidad,
    ]));

    // ── Render ────────────────────────────────────────────────
    function renderTable() {
        const tbody = document.getElementById('materiales-tbody');
        const empty = document.getElementById('materiales-empty');
        tbody.innerHTML = '';

        if (materiales.length === 0) {
            empty.style.display = '';
            document.getElementById('materiales-table').style.display = 'none';
        } else {
            empty.style.display = 'none';
            document.getElementById('materiales-table').style.display = '';
            materiales.forEach(m => tbody.appendChild(buildRow(m)));
        }

        refreshProductoSelect();
    }

    function buildRow(m) {
        const tr = document.createElement('tr');
        tr.dataset.id = m.id;
        tr.innerHTML = `
            <td><strong>${escHtml(m.producto.nombre)}</strong></td>
            <td>${escHtml(m.producto.marca)}</td>
            <td>
                <input type="number" class="form-control form-control-sm edit-cantidad"
                       value="${m.cantidad}" min="0.01" step="0.01" style="width:90px;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm edit-unidad"
                       value="${escHtml(m.unidad)}" list="unidades-list" style="width:80px;">
            </td>
            <td>
                <div class="actions">
                    <button type="button" class="btn btn-outline btn-sm btn-save">Guardar</button>
                    <button type="button" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                </div>
                <small class="row-error" style="color:var(--error-color); display:none;"></small>
            </td>
        `;

        tr.querySelector('.btn-save').addEventListener('click', () => saveRow(tr, m));
        tr.querySelector('.btn-delete').addEventListener('click', () => deleteRow(tr, m));
        return tr;
    }

    function refreshProductoSelect() {
        const usedIds = new Set(materiales.map(m => m.producto.id));
        document.querySelectorAll('#nuevo-producto-id option[value]').forEach(opt => {
            opt.disabled = usedIds.has(parseInt(opt.value));
        });
    }

    // ── Save inline edit ──────────────────────────────────────
    function saveRow(tr, m) {
        const cantidad = tr.querySelector('.edit-cantidad').value;
        const unidad   = tr.querySelector('.edit-unidad').value.trim();
        const errEl    = tr.querySelector('.row-error');
        errEl.style.display = 'none';

        fetch(`${BASE_URL}/${m.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ cantidad, unidad }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                errEl.textContent = Object.values(data.errors || {}).flat().join(' ');
                errEl.style.display = '';
                return;
            }
            m.cantidad = data.cantidad;
            m.unidad   = data.unidad;
        })
        .catch(() => {
            errEl.textContent = 'Error al guardar.';
            errEl.style.display = '';
        });
    }

    // ── Delete row ────────────────────────────────────────────
    function deleteRow(tr, m) {
        if (!confirm('¿Eliminar este material del servicio?')) return;

        fetch(`${BASE_URL}/${m.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                materiales = materiales.filter(x => x.id !== m.id);
                renderTable();
            }
        });
    }

    // ── Add new material ──────────────────────────────────────
    document.getElementById('btn-agregar-material').addEventListener('click', () => {
        const productoId = document.getElementById('nuevo-producto-id').value;
        const cantidad   = document.getElementById('nuevo-cantidad').value;
        const unidad     = document.getElementById('nuevo-unidad').value.trim();

        // Clear previous errors
        ['add-error-producto', 'add-error-cantidad', 'add-error-unidad', 'add-general-error']
            .forEach(id => { const el = document.getElementById(id); el.style.display = 'none'; el.textContent = ''; });

        fetch(BASE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ producto_id: productoId, cantidad, unidad }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                const errors = data.errors || {};
                if (errors.producto_id) showError('add-error-producto', errors.producto_id[0]);
                if (errors.cantidad)    showError('add-error-cantidad', errors.cantidad[0]);
                if (errors.unidad)      showError('add-error-unidad', errors.unidad[0]);
                if (!Object.keys(errors).length) showError('add-general-error', data.message || 'Error al agregar.');
                return;
            }
            materiales.push(data);
            renderTable();
            document.getElementById('nuevo-producto-id').value = '';
            document.getElementById('nuevo-cantidad').value = '';
            document.getElementById('nuevo-unidad').value = '';
        })
        .catch(() => showError('add-general-error', 'Error de conexión.'));
    });

    function showError(id, msg) {
        const el = document.getElementById(id);
        el.textContent = msg;
        el.style.display = '';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Initial render
    renderTable();
})();
</script>
@endpush
