{{--
    Filtro por tipo de producto. Va dentro del form GET de filtros.
    Solo en el catálogo de reventa: los productos técnicos no tienen tipo.
--}}
@if (($tipoStock ?? 'tecnico') === 'reventa')
    <select name="tipo_producto" class="filter-date" style="min-width:140px;">
        <option value="">Todos los tipos</option>
        @foreach (\App\Models\Producto::TIPOS as $valor => $etiqueta)
            <option value="{{ $valor }}" {{ ($tipoProducto ?? '') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
        @endforeach
    </select>
@endif
