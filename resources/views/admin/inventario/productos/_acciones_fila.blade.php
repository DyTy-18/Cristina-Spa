{{--
    Iconos de acción de una fila de producto (stock y catálogo).
    Espera $id y $nombre. Opcionales: $transferirUrl y $transferirTitulo (sin URL no se muestra).
--}}
<div class="fila-acciones">
    <a href="{{ route('admin.inventario.productos.edit', $id) }}" class="btn-accion" title="Editar" aria-label="Editar {{ $nombre }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
    </a>
    @if (! empty($transferirUrl))
        <a href="{{ $transferirUrl }}" class="btn-accion" title="{{ $transferirTitulo }}" aria-label="{{ $transferirTitulo }}: {{ $nombre }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4 3 8l4 4"/><path d="M3 8h14"/><path d="m17 20 4-4-4-4"/><path d="M21 16H7"/></svg>
        </a>
    @endif
    <button type="button" class="btn-accion peligro" title="Eliminar (requiere contraseña)" aria-label="Eliminar {{ $nombre }}"
            onclick="abrirEliminarProducto(@js(route('admin.inventario.productos.destroy', $id)), @js($nombre))">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
    </button>
</div>
