@extends('admin.layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('content')
    <div style="max-width:640px">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $usuario->name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $usuario->name) }}" required>
                            @error('name')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $usuario->email) }}" required>
                            @error('email')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-control" required>
                            <option value="">— Seleccionar rol —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ old('role', $rolActual) === $role->name ? 'selected' : '' }}>
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
                                    <option value="{{ $s->id }}"
                                        {{ old('sucursal_id', $usuario->sucursal_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sucursal_id')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                        </div>
                    @endif

                    <div style="border-top:1px solid rgba(0,0,0,.06);padding-top:1.5rem;margin-top:.5rem">
                        <p style="font-size:.8rem;color:var(--text-light);margin-bottom:1rem;text-transform:uppercase;letter-spacing:1px">
                            Cambiar contraseña (dejar vacío para mantener la actual)
                        </p>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" class="form-control">
                                @error('password')<span style="color:var(--error-color);font-size:.8rem">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;margin-top:1rem">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
