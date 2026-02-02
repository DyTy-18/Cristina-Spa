@extends('admin.layouts.app')

@section('title', 'Servicios')
@section('page-title', 'Gestión de Servicios')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Catálogo de Servicios</h3>
            <a href="{{ route('admin.servicios.create') }}" class="btn btn-primary">+ Nuevo Servicio</a>
        </div>

        @if ($servicios->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Categoría</th>
                        <th>Duración</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicios as $servicio)
                        <tr>
                            <td>
                                <strong>{{ $servicio->nombre }}</strong>
                                @if ($servicio->descripcion)
                                    <br><small
                                        style="color: var(--text-light);">{{ Str::limit($servicio->descripcion, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($servicio->categoria ?? 'General') }}</span>
                            </td>
                            <td>{{ $servicio->duracion_formateada }}</td>
                            <td><strong>${{ number_format($servicio->precio, 2) }}</strong></td>
                            <td>
                                @if ($servicio->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.servicios.edit', $servicio) }}"
                                        class="btn btn-outline btn-sm">Editar</a>
                                    <form action="{{ route('admin.servicios.destroy', $servicio) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar este servicio?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">✂️</div>
                <p class="empty-state-text">Aún no hay servicios registrados</p>
                <a href="{{ route('admin.servicios.create') }}" class="btn btn-primary">Crear Primer Servicio</a>
            </div>
        @endif
    </div>
@endsection
