@extends('admin.layouts.app')

@section('title', 'Nuevo Producto')
@section('page-title', 'Nuevo Producto')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar Producto</h3>
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

            <form action="{{ route('admin.productos.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre"
                           class="form-control" value="{{ old('nombre') }}"
                           placeholder="Ej: Shampoo Hidratante" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="precio">Precio (Bs.)</label>
                    <input type="number" id="precio" name="precio"
                           class="form-control" value="{{ old('precio') }}"
                           step="0.01" min="0" placeholder="Opcional">
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                        <span>Producto activo</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Servicios en los que se usa</label>
                    @if ($servicios->isEmpty())
                        <p style="color: var(--text-light); font-size: 0.85rem;">No hay servicios registrados aún.</p>
                    @else
                        <input type="text" id="filtro-servicios" class="form-control"
                               placeholder="Buscar servicio..." style="margin-bottom:0.5rem;" autocomplete="off">
                        <div id="grid-servicios" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:0.5rem; max-height:260px; overflow-y:auto; padding:0.75rem; border:1px solid rgba(0,0,0,0.1);">
                            @foreach ($servicios as $servicio)
                                <label class="servicio-check-label" data-nombre="{{ Str::lower($servicio->nombre) }}"
                                       style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.85rem; font-weight:300;">
                                    <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}"
                                           {{ in_array($servicio->id, old('servicios', [])) ? 'checked' : '' }}>
                                    <span>{{ $servicio->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    <a href="{{ route('admin.productos.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const filtro = document.getElementById('filtro-servicios');
    if (!filtro) return;
    filtro.addEventListener('input', () => {
        const q = filtro.value.trim().toLowerCase();
        document.querySelectorAll('#grid-servicios .servicio-check-label').forEach(label => {
            label.style.display = label.dataset.nombre.includes(q) ? '' : 'none';
        });
    });
})();
</script>
@endpush
