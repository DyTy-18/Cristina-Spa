@extends('admin.layouts.app')

@section('title', 'Productos')
@section('page-title', 'Productos')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Productos</h3>
            <a href="{{ route('admin.productos.create') }}" class="btn btn-primary btn-sm">+ Nuevo Producto</a>
        </div>

        <form method="GET" action="{{ route('admin.productos.index') }}" class="table-filter">
            <input type="text" name="q" class="search-input"
                   placeholder="Buscar por nombre..."
                   value="{{ $q ?? '' }}">
            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
            @if ($q)
                <a href="{{ route('admin.productos.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            @endif
        </form>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($productos->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="text-align:right;">Precio</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        @php
                            $servicioIdsAsignados = $producto->servicios->pluck('id')->all();
                        @endphp
                        <tr>
                            <td>{{ $producto->nombre }}</td>
                            <td style="text-align:right;">
                                {{ $producto->precio !== null ? 'Bs. ' . number_format($producto->precio, 2) : '—' }}
                            </td>
                            <td>{{ $producto->activo ? 'Activo' : 'Inactivo' }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-outline btn-sm btn-toggle-servicios"
                                            data-producto-id="{{ $producto->id }}">
                                        Servicios (<span class="servicios-count-{{ $producto->id }}">{{ $producto->servicios->count() }}</span>)
                                    </button>
                                    <a href="{{ route('admin.productos.edit', $producto) }}"
                                       class="btn btn-outline btn-sm">Editar</a>
                                    <form action="{{ route('admin.productos.destroy', $producto) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('¿Eliminar este producto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="servicios-row" id="servicios-row-{{ $producto->id }}" style="display:none;">
                            <td colspan="4" style="background:var(--light-bg); padding:1.25rem 1.5rem;">
                                <strong style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-light);">
                                    Servicios que usan este producto
                                </strong>

                                @if ($servicios->isEmpty())
                                    <p style="color: var(--text-light); font-size: 0.85rem; margin-top:0.75rem;">No hay servicios registrados aún.</p>
                                @else
                                    <input type="text" class="form-control form-control-sm filtro-servicios"
                                           placeholder="Buscar servicio..." style="margin:0.75rem 0 0.5rem; max-width:260px;" autocomplete="off">
                                    <div class="grid-servicios" data-producto-id="{{ $producto->id }}"
                                         style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:0.5rem; max-height:220px; overflow-y:auto; padding:0.75rem; border:1px solid rgba(0,0,0,0.1); background:var(--white);">
                                        @foreach ($servicios as $servicio)
                                            <label class="servicio-check-label" data-nombre="{{ Str::lower($servicio->nombre) }}"
                                                   style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.85rem; font-weight:300;">
                                                <input type="checkbox" class="servicio-checkbox" value="{{ $servicio->id }}"
                                                       {{ in_array($servicio->id, $servicioIdsAsignados) ? 'checked' : '' }}>
                                                <span>{{ $servicio->nombre }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <small class="servicio-error" style="color:var(--error-color); display:none; margin-top:0.5rem;"></small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">🛍️</div>
                <p class="empty-state-text">
                    @if ($q)
                        No se encontraron productos con ese nombre
                    @else
                        No hay productos registrados
                    @endif
                </p>
                @if (!$q)
                    <a href="{{ route('admin.productos.create') }}" class="btn btn-primary">Agregar Producto</a>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';

    function baseUrl(productoId) {
        return `{{ url('admin/productos') }}/${productoId}/servicios`;
    }

    document.querySelectorAll('.btn-toggle-servicios').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.productoId;
            const row = document.getElementById(`servicios-row-${id}`);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        });
    });

    document.querySelectorAll('.servicio-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const grid = checkbox.closest('.grid-servicios');
            const productoId = grid.dataset.productoId;
            const servicioId = checkbox.value;
            const errEl = grid.parentElement.querySelector('.servicio-error');
            const countEl = document.querySelector(`.servicios-count-${productoId}`);
            errEl.style.display = 'none';

            const request = checkbox.checked
                ? fetch(baseUrl(productoId), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ servicio_id: servicioId }),
                  })
                : fetch(`${baseUrl(productoId)}/${servicioId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                  });

            request
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (!ok || (checkbox.checked === false && !data.success)) {
                        checkbox.checked = !checkbox.checked;
                        const errors = data.errors || {};
                        errEl.textContent = errors.servicio_id ? errors.servicio_id[0] : (data.message || 'Error al guardar.');
                        errEl.style.display = '';
                        return;
                    }
                    countEl.textContent = grid.querySelectorAll('.servicio-checkbox:checked').length;
                })
                .catch(() => {
                    checkbox.checked = !checkbox.checked;
                    errEl.textContent = 'Error de conexión.';
                    errEl.style.display = '';
                });
        });
    });

    document.querySelectorAll('.filtro-servicios').forEach(input => {
        const grid = input.parentElement.querySelector('.grid-servicios');
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            grid.querySelectorAll('.servicio-check-label').forEach(label => {
                label.style.display = label.dataset.nombre.includes(q) ? '' : 'none';
            });
        });
    });
})();
</script>
@endpush
