@extends('admin.layouts.app')

@section('title', 'Seguimiento — ' . ($cita->cliente?->nombre_completo ?? 'Sin cliente'))
@section('page-title', 'Seguimiento de Cita')

@push('styles')
<style>
    .seg-fotos-form { display: grid; grid-template-columns: minmax(220px, 300px) minmax(0, 1fr) auto; gap: 0.75rem;
                      align-items: end; margin-bottom: 1.25rem; }
    .seg-fotos-form .form-label { display: block; }
    .seg-fotos-form .btn { white-space: nowrap; }
    @media (max-width: 760px) { .seg-fotos-form { grid-template-columns: 1fr; } }

    .seg-file-input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .seg-file-picker { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; margin: 0; min-width: 0; }
    .seg-file-btn { flex: none; font-size: 0.7rem; letter-spacing: 1px; text-transform: uppercase;
                    color: var(--primary-color); border: 1px solid var(--primary-color); padding: 0.2rem 0.6rem; }
    .seg-file-picker:hover .seg-file-btn { background: var(--primary-color); color: var(--white); }
    .seg-file-nombre { font-size: 0.82rem; color: var(--text-light); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .seg-file-nombre.elegido { color: var(--text-dark); }
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
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">
                {{ $cita->cliente?->nombre_completo ?? 'Sin cliente' }}
                <span style="font-size:0.8rem;font-weight:300;color:var(--text-light);">
                    · {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                    @if($sucursalCita) · {{ $sucursalCita->nombre }} @endif
                </span>
            </h3>
        </div>
        <div class="card-body">
            <div style="font-size:0.9rem;color:var(--text-dark);">
                {{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->unique()->implode(' · ') ?: 'Sin servicios' }}
            </div>
        </div>
    </div>

    {{-- Productos usados en la cita: se registran a mano, uno por uno (nada se aplica solo) --}}
    @php
        $fmtG = fn ($g) => rtrim(rtrim(number_format((float) $g, 2, '.', ''), '0'), '.');
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Productos usados en esta cita</h3>
        </div>
        <div class="card-body">
            @if($productosDeServicios->isNotEmpty())
                <div style="font-size:0.8rem;color:var(--text-light);margin-bottom:1rem;">
                    Sugeridos por los servicios:
                    @foreach($productosDeServicios as $p)
                        <span style="display:inline-block;background:var(--light-bg);border:1px solid rgba(0,0,0,0.08);padding:0.15rem 0.55rem;margin:0.15rem 0.2rem 0 0;font-size:0.78rem;color:var(--text-dark);">{{ $p->nombre }}</span>
                    @endforeach
                </div>
            @endif

            {{-- Registrar el uso de un producto de seguimiento --}}
            @if($catalogoProductos->isEmpty())
                @if($seguimientoEnOtras->isNotEmpty())
                    <div style="background:#fff8e1;border:1px solid #f59e0b;padding:0.75rem 1rem;font-size:0.85rem;margin:0 0 1rem;">
                        Esta cita está registrada en <strong>{{ $sucursalCita?->nombre ?? 'otra sucursal' }}</strong>,
                        que no tiene productos de Seguimiento en su inventario.
                        Sí hay en:
                        {{ $seguimientoEnOtras->map(fn ($n, $nombre) => "{$nombre} ({$n})")->implode(', ') }}.
                        <br>
                        Cada sucursal descuenta de su propio inventario: registra el producto en el inventario de
                        {{ $sucursalCita?->nombre ?? 'esta sucursal' }}, o revisa si la cita se cargó en la sucursal correcta.
                    </div>
                @else
                    <p style="color:var(--text-light);font-size:0.85rem;font-style:italic;margin:0 0 1rem;">
                        No hay productos marcados como "Seguimiento" en el inventario
                        de {{ $sucursalCita?->nombre ?? 'esta sucursal' }}.
                    </p>
                @endif
            @else
                <form action="{{ route('admin.citas.seguimiento.productos.store', $cita) }}" method="POST"
                      style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:0.5rem;">
                    @csrf
                    <input type="hidden" name="modo" value="existente">
                    <div class="form-group" style="margin:0;flex:1;min-width:240px;">
                        <label class="form-label">Producto usado</label>
                        <select name="producto_id" id="segProductoSelect" class="form-control" required onchange="segActualizarPeso()">
                            <option value="">— Elegir producto —</option>
                            @foreach($catalogoProductos as $p)
                                <option value="{{ $p->id }}" data-peso="{{ (float) $p->peso_gramos }}"
                                        {{ old('producto_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }}@if($p->marca) — {{ $p->marca }}@endif ({{ $fmtG($p->peso_gramos) }} g · stock {{ $p->stock_actual }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Gramos usados</label>
                        <input type="number" name="gramos" class="form-control" step="0.01" min="0.01" required
                               value="{{ old('gramos') }}" placeholder="Ej: 50" style="max-width:130px;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Registrar uso</button>
                </form>
                <p style="font-size:0.75rem;color:var(--text-light);margin:0 0 1.25rem;">
                    <span id="segPesoHint"></span>
                    Los gramos se acumulan: cada vez que se completa el peso de una unidad, se descuenta 1 del inventario.
                </p>
            @endif

            {{-- Lo registrado en esta cita --}}
            @if($cita->seguimientoProductos->isEmpty())
                <p style="color:var(--text-light);font-size:0.88rem;font-style:italic;margin:0;">
                    Todavía no se registró ningún producto en esta cita.
                </p>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad usada</th>
                            <th>Origen</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cita->seguimientoProductos as $sp)
                            <tr>
                                <td>{{ $sp->nombre }}</td>
                                <td>
                                    @if($sp->gramos)
                                        {{ $fmtG($sp->gramos) }} g
                                        @if($sp->producto?->peso_gramos)
                                            <span style="font-size:0.75rem;color:var(--text-light);">de {{ $fmtG($sp->producto->peso_gramos) }} g</span>
                                        @endif
                                    @else
                                        <span style="color:var(--text-light);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sp->producto_id || $sp->producto_catalogo_id)
                                        <span style="font-size:0.75rem;color:var(--text-light);">Inventario</span>
                                    @else
                                        <span style="font-size:0.75rem;color:var(--accent-color);">Exclusivo de este cliente</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.citas.seguimiento.productos.destroy', [$cita, $sp]) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('¿Quitar este producto? Si descontó stock, se devuelve.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm">Quitar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Producto exclusivo del cliente (nota, no descuenta inventario) --}}
            <div style="margin-top:1rem;">
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleAddProductoForm()">+ Producto exclusivo de la clienta</button>
            </div>
            <div id="addProductoForm" style="display:none;margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(0,0,0,0.06);">
                <form action="{{ route('admin.citas.seguimiento.productos.store', $cita) }}" method="POST"
                      style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap;">
                    @csrf
                    <input type="hidden" name="modo" value="nuevo">
                    <div class="form-group" style="margin:0;flex:1;min-width:240px;">
                        <label class="form-label">Nombre del producto</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Mascarilla capilar de prueba">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleAddProductoForm()">Cancelar</button>
                </form>
                <p style="font-size:0.75rem;color:var(--text-light);margin:0.5rem 0 0;">
                    Queda como nota en el seguimiento de la clienta; no descuenta inventario.
                </p>
            </div>
        </div>
    </div>

    {{-- Fotos del resultado (álbum de la clienta) --}}
    @if($cita->cliente)
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Fotos del resultado</h3>
            <a href="{{ route('admin.clientes.album', $cita->cliente) }}" class="btn btn-outline btn-sm">Ver álbum de la clienta</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.citas.seguimiento.fotos.store', $cita) }}" method="POST" enctype="multipart/form-data"
                  class="seg-fotos-form">
                @csrf
                <div>
                    <label class="form-label">Fotos (hasta 10)</label>
                    <label class="form-control seg-file-picker" for="segFotosInput">
                        <span class="seg-file-btn">Elegir fotos</span>
                        <span id="segFotosNombre" class="seg-file-nombre">Ninguna seleccionada</span>
                    </label>
                    <input type="file" id="segFotosInput" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple required
                           class="seg-file-input" onchange="segFotosElegidas(this)">
                </div>
                <div>
                    <label class="form-label" for="segFotosDesc">Descripción (opcional)</label>
                    <input type="text" id="segFotosDesc" name="descripcion" class="form-control" maxlength="255" placeholder="Ej: Balayage terminado, vista lateral">
                </div>
                <button type="submit" class="btn btn-primary">Subir fotos</button>
            </form>

            @if($cita->seguimientoFotos->isEmpty())
                <p style="color:var(--text-light);font-size:0.88rem;font-style:italic;margin:0;">
                    Aún no se subieron fotos de esta cita.
                </p>
            @else
                @include('admin.clientes._fotos_grid', ['fotos' => $cita->seguimientoFotos])
            @endif
        </div>
    </div>
    @endif

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

    function segFotosElegidas(input) {
        const n = input.files.length;
        const el = document.getElementById('segFotosNombre');
        el.textContent = n === 0 ? 'Ninguna seleccionada' : (n === 1 ? input.files[0].name : `${n} fotos seleccionadas`);
        el.classList.toggle('elegido', n > 0);
    }

    function segActualizarPeso() {
        const sel = document.getElementById('segProductoSelect');
        const opt = sel ? sel.selectedOptions[0] : null;
        const peso = opt ? parseFloat(opt.dataset.peso || 0) : 0;
        const hint = document.getElementById('segPesoHint');
        if (hint) hint.textContent = peso ? `1 unidad = ${peso} g. ` : '';
    }
    document.addEventListener('DOMContentLoaded', segActualizarPeso);

    @if($errors->has('nombre'))
        document.addEventListener('DOMContentLoaded', toggleAddProductoForm);
    @endif
</script>
@endpush
