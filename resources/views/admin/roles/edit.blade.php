@extends('admin.layouts.app')

@section('title', 'Editar Rol')
@section('page-title', 'Editar Rol')

@section('content')
    <div style="max-width:860px">
        <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                <h3 class="card-title" style="text-transform:capitalize">{{ $rol->name }}</h3>
                <span class="badge badge-info">
                    {{ count($permisosDelRol) }} permisos asignados
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.roles.update', $rol) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @foreach ($permisos as $recurso => $lista)
                        <div style="margin-bottom:1.5rem">
                            <p style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-light);margin-bottom:.75rem;border-bottom:1px solid rgba(0,0,0,.06);padding-bottom:.4rem">
                                {{ $recurso }}
                            </p>
                            <div style="display:flex;flex-wrap:wrap;gap:.5rem 2rem">
                                @foreach ($lista->sortBy('name') as $permiso)
                                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;font-weight:300">
                                        <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                            {{ in_array($permiso->name, $permisosDelRol) ? 'checked' : '' }}
                                            style="accent-color:var(--accent-color);width:15px;height:15px">
                                        {{ $permiso->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div style="display:flex;gap:1rem;margin-top:1.5rem;border-top:1px solid rgba(0,0,0,.06);padding-top:1.5rem">
                        <button type="submit" class="btn btn-primary">Guardar permisos</button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
