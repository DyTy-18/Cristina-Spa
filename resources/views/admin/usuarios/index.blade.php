@extends('admin.layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todos los Usuarios</h3>
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">+ Nuevo Usuario</a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td>
                            <strong style="font-weight:400">{{ $usuario->name }}</strong>
                            @if ($usuario->id === auth()->id())
                                <span class="badge badge-info" style="margin-left:.4rem">Tú</span>
                            @endif
                        </td>
                        <td style="color:var(--text-light)">{{ $usuario->email }}</td>
                        <td>
                            @forelse ($usuario->roles as $role)
                                <span class="badge badge-info">{{ $role->name }}</span>
                            @empty
                                <span style="font-size:.8rem;color:var(--text-light)">Sin rol</span>
                            @endforelse
                        </td>
                        <td style="color:var(--text-light);font-size:.85rem">{{ $usuario->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-outline btn-sm">Editar</a>
                                @if ($usuario->id !== auth()->id())
                                    <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar al usuario {{ $usuario->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">👤</div>
                                <p class="empty-state-text">No hay usuarios registrados.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
