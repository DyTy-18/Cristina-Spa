{{--
    Modal para eliminar un producto con contraseña de administrador (mismo diseño que
    eliminar cliente). Los botones llaman a abrirEliminarProducto(url, nombre).
--}}
<div class="del-overlay" id="delProductoOverlay">
    <div class="del-modal">
        <h4>Eliminar producto</h4>
        <p>
            ¿Estás seguro de que deseas eliminar permanentemente
            <strong id="delProductoNombre"></strong>?
            <br>
            Se borrarán también todas sus entradas y salidas (técnico y reventa) y su uso como material de servicios.
            Se guardará un backup en "Ver Backups". Ingresa la contraseña de administrador para continuar.
        </p>
        <form id="delProductoForm" method="POST" style="display:block;">
            @csrf
            @method('DELETE')
            <input type="password" name="password" id="delProductoPassword"
                   placeholder="Contraseña" autocomplete="current-password" required
                   style="width:100%;height:40px;padding:0 0.75rem;border:1px solid rgba(0,0,0,0.18);border-radius:4px;font-size:0.9rem;outline:none;margin-bottom:0.5rem;box-sizing:border-box;">
            <div class="modal-error" id="delProductoPasswordError" style="font-size:0.8rem;color:#842029;margin-bottom:0.75rem;min-height:1rem;"></div>
            <div class="del-modal-actions">
                <button type="button" class="btn btn-outline" onclick="cerrarEliminarProducto()">Cancelar</button>
                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirEliminarProducto(url, nombre) {
        document.getElementById('delProductoNombre').textContent = nombre;
        document.getElementById('delProductoForm').action = url;
        document.getElementById('delProductoPassword').value = '';
        document.getElementById('delProductoPasswordError').textContent = '';
        document.getElementById('delProductoOverlay').classList.add('open');
        setTimeout(() => document.getElementById('delProductoPassword').focus(), 50);
    }

    function cerrarEliminarProducto() {
        document.getElementById('delProductoOverlay').classList.remove('open');
    }

    document.getElementById('delProductoOverlay').addEventListener('click', function (e) {
        if (e.target === this) cerrarEliminarProducto();
    });

    // Contraseña incorrecta: se vuelve a abrir el modal con el error.
    @if (session('confirmar_eliminar_producto_id'))
        document.addEventListener('DOMContentLoaded', function () {
            abrirEliminarProducto(
                @json(route('admin.inventario.productos.destroy', session('confirmar_eliminar_producto_id'))),
                @json(session('confirmar_eliminar_producto_nombre', 'este producto'))
            );
            document.getElementById('delProductoPasswordError').textContent = @json($errors->first('password'));
        });
    @endif
</script>
@endpush
