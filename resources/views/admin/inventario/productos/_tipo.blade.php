{{-- Catálogo y tipo de producto (create/edit). Espera $esReventaActual, $tipoActual y $pesoActual. --}}
@php
    $esReventaMarcado = (bool) old('es_reventa', $esReventaActual ?? false);
@endphp

{{-- Sin la casilla marcada el producto es técnico (sin tipo). --}}
<input type="hidden" name="es_reventa" value="0">

<div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
    <input type="checkbox" id="es_reventa" name="es_reventa" value="1"
           {{ $esReventaMarcado ? 'checked' : '' }} onchange="toggleTipoProducto()">
    <label class="form-label" for="es_reventa" style="margin:0;">Producto de reventa</label>
</div>

@include('admin.inventario.productos._tipo_selector', [
    'tipoActual'    => $tipoActual,
    'pesoActual'    => $pesoActual,
    'deshabilitado' => ! $esReventaMarcado,
])

<script>
    function toggleTipoProducto() {
        const reventa = document.getElementById('es_reventa').checked;
        document.getElementById('tipoProductoGroup').style.display = reventa ? '' : 'none';
        // Radios deshabilitados no se envían: un producto técnico no lleva tipo.
        document.querySelectorAll('input[type="radio"][name="tipo"]').forEach(r => r.disabled = !reventa);
        actualizarPesoTipo();
    }
</script>
