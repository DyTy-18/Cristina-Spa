@extends('admin.layouts.app')

@section('title', 'Nuevo Usuario')
@section('page-title', 'Nuevo Usuario')

@section('content')
    <div style="max-width:640px">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Datos del Usuario</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.usuarios.store') }}" method="POST">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar contraseña *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rol *</label>
                        @if($esEncargado)
                            <p style="font-size:0.75rem;color:var(--text-light);margin-bottom:0.4rem;">
                                Como encargado puedes asignar: Secretario, Cajero o Estilista.
                            </p>
                        @endif
                        <select name="role" class="form-control" required>
                            <option value="">— Seleccionar rol —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                    </div>

                    @if(!$esEncargado && isset($sucursales) && $sucursales->isNotEmpty())
                        <div class="form-group">
                            <label class="form-label">Sucursal</label>
                            <select name="sucursal_id" class="form-control">
                                <option value="">— Sin sucursal asignada —</option>
                                @foreach($sucursales as $s)
                                    <option value="{{ $s->id }}" {{ old('sucursal_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <p style="font-size:0.72rem;color:var(--text-light);margin-top:0.25rem;">
                                Define qué sucursal puede ver este usuario. Requerido para todos los roles excepto admin.
                            </p>
                            @error('sucursal_id')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                    @endif

                    <div style="display:flex;gap:1rem;margin-top:1rem">
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
