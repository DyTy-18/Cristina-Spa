@extends('admin.layouts.app')

@section('title', 'Editar Sucursal')
@section('page-title', 'Editar Sucursal')

@section('content')

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.sucursales.index') }}" class="btn btn-sm btn-outline">← Volver a sucursales</a>
    </div>

    {{-- Datos de la sucursal --}}
    <div class="card" style="max-width:720px;margin-bottom:2rem;">
        <div class="card-header">
            <h3 class="card-title">{{ $sucursal->nombre }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sucursales.update', $sucursal) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nombre <span style="color:var(--error-color)">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $sucursal->nombre) }}" autofocus>
                    @error('nombre')
                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono', $sucursal->telefono) }}" placeholder="Ej: 2-123456">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $sucursal->email) }}" placeholder="sucursal@cristinaspa.com">
                        @error('email')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-control"
                           value="{{ old('direccion', $sucursal->direccion) }}" placeholder="Calle, zona, ciudad">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción <span style="font-size:0.78rem;color:var(--text-light);">(opcional)</span></label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $sucursal->descripcion) }}</textarea>
                </div>

                <div class="form-row" style="align-items:center;gap:2rem;">
                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;padding-top:0.5rem;">
                        <input type="checkbox" name="es_principal" id="es_principal" value="1"
                               {{ old('es_principal', $sucursal->es_principal) ? 'checked' : '' }}
                               style="width:auto;margin:0;">
                        <label for="es_principal" class="form-label" style="margin:0;cursor:pointer;">
                            Sucursal principal
                        </label>
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;padding-top:0.5rem;">
                        <input type="checkbox" name="activo" id="activo" value="1"
                               {{ old('activo', $sucursal->activo) ? 'checked' : '' }}
                               style="width:auto;margin:0;">
                        <label for="activo" class="form-label" style="margin:0;cursor:pointer;">Sucursal activa</label>
                    </div>
                </div>

                @if($sucursal->es_principal)
                    <p style="font-size:0.75rem;color:var(--text-light);margin-top:-0.5rem;margin-bottom:1.5rem;">
                        Esta es actualmente la sucursal principal. Desmarcarla no asignará otra automáticamente.
                    </p>
                @else
                    <p style="font-size:0.75rem;color:var(--text-light);margin-top:-0.5rem;margin-bottom:1.5rem;">
                        Marcar como principal cambiará la sucursal principal actual.
                    </p>
                @endif

                <div style="display:flex;gap:1rem;">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.sucursales.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Encargado de sucursal --}}
    <div class="card" style="max-width:720px;">
        <div class="card-header">
            <h3 class="card-title">Encargado de Sucursal</h3>
        </div>
        <div class="card-body">

            @if($sucursal->encargado)
                {{-- Encargado actual --}}
                <div style="display:flex;align-items:center;gap:1.25rem;padding:1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:1.5rem;">
                    <div style="width:42px;height:42px;border-radius:50%;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:600;flex-shrink:0;">
                        {{ strtoupper(substr($sucursal->encargado->name, 0, 1)) }}
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:600;font-size:0.95rem;">{{ $sucursal->encargado->name }}</div>
                        <div style="font-size:0.82rem;color:var(--text-light);">{{ $sucursal->encargado->email }}</div>
                    </div>
                    <span style="background:#dcfce7;color:#166534;border-radius:20px;font-size:0.72rem;font-weight:600;padding:0.2rem 0.7rem;">
                        Encargado
                    </span>
                </div>

                <form action="{{ route('admin.sucursales.encargado.quitar', $sucursal) }}" method="POST"
                      onsubmit="return confirm('¿Seguro que deseas quitar a {{ $sucursal->encargado->name }} como encargado? El usuario perderá su rol pero seguirá activo en el sistema.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--error-color);border-color:var(--error-color);">
                        Quitar encargado
                    </button>
                </form>

            @else
                {{-- Sin encargado: formulario para asignar --}}
                <p style="font-size:0.85rem;color:var(--text-light);margin-bottom:1.25rem;">
                    Esta sucursal no tiene encargado asignado. El encargado tiene acceso completo al sistema pero solo puede ver y gestionar datos de esta sucursal.
                </p>

                @if(session('errors') && session('errors')->has('user_id'))
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:0.65rem 1rem;margin-bottom:1rem;font-size:0.83rem;color:#b91c1c;">
                        {{ session('errors')->first('user_id') }}
                    </div>
                @endif

                <div x-data="{ modo: '{{ old('modo', 'existente') }}' }">

                    {{-- Tabs --}}
                    <div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:1.5rem;">
                        <button type="button"
                                @click="modo = 'existente'"
                                :style="modo === 'existente' ? 'border-bottom:2px solid var(--primary-color);color:var(--primary-color);margin-bottom:-2px;' : 'color:var(--text-light);'"
                                style="background:none;border:none;padding:0.5rem 1rem;font-size:0.85rem;font-weight:500;cursor:pointer;">
                            Usuario existente
                        </button>
                        <button type="button"
                                @click="modo = 'nuevo'"
                                :style="modo === 'nuevo' ? 'border-bottom:2px solid var(--primary-color);color:var(--primary-color);margin-bottom:-2px;' : 'color:var(--text-light);'"
                                style="background:none;border:none;padding:0.5rem 1rem;font-size:0.85rem;font-weight:500;cursor:pointer;">
                            Crear nuevo usuario
                        </button>
                    </div>

                    {{-- Opción 1: usuario existente --}}
                    <div x-show="modo === 'existente'">
                        <form action="{{ route('admin.sucursales.encargado.asignar', $sucursal) }}" method="POST">
                            @csrf
                            <input type="hidden" name="modo" value="existente">

                            <div class="form-group">
                                <label class="form-label">Seleccionar usuario <span style="color:var(--error-color)">*</span></label>
                                @if($usuariosDisponibles->isEmpty())
                                    <p style="font-size:0.83rem;color:var(--text-light);padding:0.75rem;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;">
                                        No hay usuarios disponibles para asignar. Puedes crear uno nuevo en la pestaña de arriba o desde la sección de Usuarios.
                                    </p>
                                @else
                                    <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        <option value="">— Seleccionar usuario —</option>
                                        @foreach($usuariosDisponibles as $u)
                                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->name }} ({{ $u->email }})
                                                @if($u->getRoleNames()->isNotEmpty())
                                                    — {{ $u->getRoleNames()->first() }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                    <p style="font-size:0.72rem;color:var(--text-light);margin-top:0.3rem;">
                                        Al asignar, se le cambiará el rol a <strong>Encargado</strong> y se vinculará a esta sucursal.
                                    </p>

                                    <button type="submit" class="btn btn-primary" style="margin-top:0.75rem;">Asignar como encargado</button>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Opción 2: crear nuevo usuario --}}
                    <div x-show="modo === 'nuevo'">
                        <form action="{{ route('admin.sucursales.encargado.asignar', $sucursal) }}" method="POST">
                            @csrf
                            <input type="hidden" name="modo" value="nuevo">

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nombre completo <span style="color:var(--error-color)">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" placeholder="Ej: María López">
                                    @error('name')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email <span style="color:var(--error-color)">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" placeholder="encargado@cristinaspa.com">
                                    @error('email')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Contraseña <span style="color:var(--error-color)">*</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="Mín. 8 caracteres">
                                    @error('password')
                                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirmar contraseña <span style="color:var(--error-color)">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>

                            <p style="font-size:0.72rem;color:var(--text-light);margin-bottom:0.75rem;">
                                Se creará el usuario con rol <strong>Encargado</strong> vinculado a <strong>{{ $sucursal->nombre }}</strong>.
                            </p>

                            <button type="submit" class="btn btn-primary">Crear y asignar encargado</button>
                        </form>
                    </div>

                </div>
            @endif

        </div>
    </div>

@endsection
