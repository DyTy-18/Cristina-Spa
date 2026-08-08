@extends('admin.layouts.app')

@section('title', 'Seguimiento — ' . ($cita->cliente?->nombre_completo ?? 'Sin cliente'))
@section('page-title', 'Seguimiento de Cita')

@push('styles')
<style>
    .seg-tabs {
        display: flex;
        border-bottom: 1px solid rgba(0,0,0,0.08);
    }
    .seg-tab {
        padding: 0.6rem 1.1rem;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.8rem;
        font-weight: 300;
        color: var(--text-light);
        background: var(--light-bg);
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: var(--transition);
    }
    .seg-tab:hover:not(.active) { color: var(--text-dark); }
    .seg-tab.active { color: var(--primary-color); border-bottom-color: var(--accent-color); background: var(--white); font-weight: 500; }
</style>
@endpush

@section('content')

    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <a href="{{ route('admin.citas.show', $cita) }}" class="btn btn-sm btn-outline">← Volver a la cita</a>
        @if($cita->cliente)
            <a href="{{ route('admin.clientes.show', $cita->cliente) }}" class="btn btn-sm btn-outline">Ver ficha del cliente</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">
                {{ $cita->cliente?->nombre_completo ?? 'Sin cliente' }}
                <span style="font-size:0.8rem;font-weight:300;color:var(--text-light);">
                    · {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                </span>
            </h3>
        </div>
        <div class="card-body">
            <div style="font-size:0.9rem;color:var(--text-dark);">
                {{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' · ') ?: 'Sin servicios' }}
            </div>
        </div>
    </div>

    {{-- Productos de los servicios (informativo) --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Productos de los servicios</h3>
        </div>
        <div class="card-body">
            @if($productosDeServicios->isEmpty())
                <p style="color:var(--text-light);font-size:0.88rem;font-style:italic;margin:0;">
                    Los servicios de esta cita no tienen productos asociados.
                </p>
            @else
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                    @foreach($productosDeServicios as $p)
                        <span style="display:inline-block;background:var(--light-bg);border:1px solid rgba(0,0,0,0.08);padding:0.35rem 0.7rem;font-size:0.82rem;">
                            {{ $p->nombre }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Productos en seguimiento --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Productos en seguimiento</h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="toggleAddProductoForm()">+ Añadir producto</button>
        </div>
        <div class="card-body">
            @if($cita->seguimientoProductos->isEmpty())
                <p id="segProductosEmpty" style="color:var(--text-light);font-size:0.88rem;font-style:italic;">
                    Aún no se agregaron productos a este seguimiento.
                </p>
            @endif

            <table class="data-table" id="segProductosTable" style="{{ $cita->seguimientoProductos->isEmpty() ? 'display:none;' : '' }}">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Origen</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cita->seguimientoProductos as $sp)
                        <tr>
                            <td>{{ $sp->nombre }}</td>
                            <td>
                                @if($sp->producto_id || $sp->producto_catalogo_id)
                                    <span style="font-size:0.75rem;color:var(--text-light);">Inventario</span>
                                @else
                                    <span style="font-size:0.75rem;color:var(--accent-color);">Exclusivo de este cliente</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.citas.seguimiento.productos.destroy', [$cita, $sp]) }}" method="POST"
                                      style="display:inline;" onsubmit="return confirm('¿Quitar este producto del seguimiento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Formulario agregar producto --}}
            <div id="addProductoForm" style="display:none;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid rgba(0,0,0,0.06);">
                <div class="seg-tabs" style="margin-bottom:1rem;">
                    <button type="button" class="seg-tab active" id="segTabExistente" onclick="segSwitchModo('existente')">Producto existente</button>
                    <button type="button" class="seg-tab" id="segTabNuevo" onclick="segSwitchModo('nuevo')">Crear nuevo</button>
                </div>

                <form action="{{ route('admin.citas.seguimiento.productos.store', $cita) }}" method="POST">
                    @csrf
                    <input type="hidden" name="modo" id="segModoInput" value="existente">

                    <div id="segPanelExistente" class="form-group">
                        <label class="form-label">Producto de inventario (reventa)</label>
                        <select name="producto_id" class="form-control">
                            <option value="">— seleccionar producto —</option>
                            @foreach($catalogoProductos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} (Stock: {{ $p->stock_actual }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="segPanelNuevo" class="form-group" style="display:none;">
                        <label class="form-label">Nombre del producto</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Mascarilla capilar de prueba">
                        <p style="font-size:0.78rem;color:var(--text-light);margin-top:0.5rem;">
                            Es una nota exclusiva de este cliente, no crea un producto en el catálogo. Para vender un producto
                            real, primero debe existir en Inventario.
                        </p>
                    </div>

                    <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAddProductoForm()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Notas de seguimiento --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Notas de seguimiento</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.citas.seguimiento.notas.store', $cita) }}" method="POST" style="margin-bottom:1.5rem;">
                @csrf
                <div class="form-group">
                    <textarea name="nota" class="form-control" rows="3" placeholder="Ej: Se llamó al cliente, usó el producto recomendado, etc." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Agregar nota</button>
            </form>

            @if($cita->seguimientoNotas->isEmpty())
                <p style="color:var(--text-light);font-size:0.88rem;font-style:italic;margin:0;">
                    Sin notas registradas todavía.
                </p>
            @else
                <div>
                    @foreach($cita->seguimientoNotas as $nota)
                        <div style="padding:0.85rem 0;border-bottom:1px solid rgba(0,0,0,0.05);">
                            <div style="font-size:0.9rem;color:var(--text-dark);white-space:pre-line;">{{ $nota->nota }}</div>
                            <div style="font-size:0.75rem;color:var(--text-light);margin-top:0.3rem;">
                                {{ $nota->user?->name ?? 'Sistema' }} · {{ $nota->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function toggleAddProductoForm() {
        const form = document.getElementById('addProductoForm');
        form.style.display = form.style.display === 'none' ? '' : 'none';
    }

    function segSwitchModo(modo) {
        document.getElementById('segModoInput').value = modo;
        document.getElementById('segTabExistente').classList.toggle('active', modo === 'existente');
        document.getElementById('segTabNuevo').classList.toggle('active', modo === 'nuevo');
        document.getElementById('segPanelExistente').style.display = modo === 'existente' ? '' : 'none';
        document.getElementById('segPanelNuevo').style.display     = modo === 'nuevo' ? '' : 'none';
    }
</script>
@endpush
