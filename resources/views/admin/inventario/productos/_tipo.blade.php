{{-- Selector de tipo de producto (create/edit). Espera $tipoActual y $pesoActual. --}}
@php
    $opcionesTipo = [
        'vitrina'     => ['Vitrina', 'Se vende al cliente. Aparece en el módulo Productos y tiene su propio stock de venta.'],
        'seguimiento' => ['Seguimiento', 'Se aplica al cliente en el servicio. Se registra en gramos en el seguimiento y descuenta del inventario.'],
        'generico'    => ['Genérico', 'Solo se ve en inventario. No aparece en vitrina ni en seguimiento.'],
    ];
@endphp

<div class="form-group">
    <label class="form-label">Tipo de producto *</label>
    <div class="tipo-producto-opciones">
        @foreach ($opcionesTipo as $valor => [$titulo, $desc])
            <label class="tipo-producto-opcion">
                <input type="radio" name="tipo" value="{{ $valor }}"
                       {{ $tipoActual === $valor ? 'checked' : '' }}
                       onchange="toggleTipoProducto()" required>
                <span class="tipo-producto-card">
                    <strong>{{ $titulo }}</strong>
                    <small>{{ $desc }}</small>
                </span>
            </label>
        @endforeach
    </div>
</div>

<div class="form-row" id="pesoGramosRow" style="{{ $tipoActual === 'seguimiento' ? '' : 'display:none;' }}">
    <div class="form-group">
        <label class="form-label" for="peso_gramos">Peso del producto (gramos) *</label>
        <input type="number" id="peso_gramos" name="peso_gramos"
               class="form-control" value="{{ $pesoActual }}"
               step="0.01" min="0.01" placeholder="Ej: 100">
        <small style="color:#888; font-size:0.75rem;">
            Contenido de una unidad. Al registrar en seguimiento los gramos usados, cada vez que se acumule
            este peso se descuenta 1 unidad del inventario.
        </small>
    </div>
</div>

<style>
    .tipo-producto-opciones { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:0.75rem; }
    .tipo-producto-opcion { cursor:pointer; margin:0; }
    .tipo-producto-opcion input { position:absolute; opacity:0; pointer-events:none; }
    .tipo-producto-card { display:flex; flex-direction:column; gap:0.3rem; height:100%; padding:0.85rem 1rem;
        border:1px solid rgba(0,0,0,0.12); background:#fff; transition:border-color .15s, background .15s; }
    .tipo-producto-card strong { font-size:0.9rem; }
    .tipo-producto-card small { color:#888; font-size:0.75rem; line-height:1.35; }
    .tipo-producto-opcion input:checked + .tipo-producto-card { border-color:var(--accent-color, #b8860b); background:var(--light-bg, #faf7f2); box-shadow:inset 0 0 0 1px var(--accent-color, #b8860b); }
    .tipo-producto-opcion input:focus-visible + .tipo-producto-card { outline:2px solid var(--accent-color, #b8860b); outline-offset:2px; }
</style>

<script>
    function toggleTipoProducto() {
        const sel = document.querySelector('input[name="tipo"]:checked');
        const esSeguimiento = sel && sel.value === 'seguimiento';
        document.getElementById('pesoGramosRow').style.display = esSeguimiento ? '' : 'none';
        document.getElementById('peso_gramos').required = esSeguimiento;
    }
    document.addEventListener('DOMContentLoaded', toggleTipoProducto);
</script>
