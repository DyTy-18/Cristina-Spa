@extends('admin.layouts.app')

@section('title', 'Roles y Permisos')
@section('page-title', 'Roles y Permisos')

@section('content')
    <div style="display:grid;gap:1.5rem">
        @foreach ($roles as $rol)
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <div style="display:flex;align-items:center;gap:1.5rem">
                        <h3 class="card-title" style="text-transform:capitalize">{{ $rol->name }}</h3>
                        <div style="display:flex;gap:.5rem">
                            <span class="badge badge-info">
                                {{ $rol->permissions->count() }}/{{ $totalPermisos }} permisos
                            </span>
                            <span class="badge badge-success">
                                {{ $rol->users->count() }} {{ $rol->users->count() === 1 ? 'usuario' : 'usuarios' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.roles.edit', $rol) }}" class="btn btn-outline btn-sm">
                        Editar permisos
                    </a>
                </div>
                @if ($rol->permissions->isNotEmpty())
                    <div class="card-body" style="padding:1rem 1.5rem">
                        <div style="display:flex;flex-wrap:wrap;gap:.4rem">
                            @foreach ($rol->permissions->sortBy('name') as $permiso)
                                <span class="badge badge-info" style="font-size:.7rem">{{ $permiso->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endsection
