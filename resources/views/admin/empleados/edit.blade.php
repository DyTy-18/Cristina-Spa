@extends('admin.layouts.app')

@section('title', 'Editar — ' . $empleado->nombre_completo)
@section('page-title', 'Editar Empleado')

@section('content')

    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;">
        <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-sm btn-outline">← Volver al perfil</a>
    </div>

    <div class="card" style="max-width:720px;">
        <div class="card-header" style="display:flex;align-items:center;gap:1rem;">
            <div style="width:40px;height:40px;background:var(--accent-color);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--white);flex-shrink:0;">
                {{ mb_substr($empleado->nombre, 0, 1) }}
            </div>
            <div>
                <h3 class="card-title">{{ $empleado->nombre_completo }}</h3>
                <span style="font-size:0.75rem;color:var(--text-light);font-weight:300;text-transform:capitalize;">
                    {{ $empleado->cargo }}{{ $empleado->especialidad ? ' · ' . $empleado->especialidad : '' }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.empleados.update', $empleado) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre <span style="color:var(--error-color)">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $empleado->nombre) }}" autofocus>
                        @error('nombre')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control"
                               value="{{ old('apellido', $empleado->apellido) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cargo <span style="color:var(--error-color)">*</span></label>
                        <select name="cargo" class="form-control @error('cargo') is-invalid @enderror" required>
                            @foreach(['estilista','colorista','manicurista','esteticista','recepcionista','cajera','masajista','otro'] as $c)
                                <option value="{{ $c }}" {{ old('cargo', $empleado->cargo) === $c ? 'selected' : '' }}>
                                    {{ ucfirst($c) }}
                                </option>
                            @endforeach
                        </select>
                        @error('cargo')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono', $empleado->telefono) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Especialidad</label>
                    <input type="text" name="especialidad" class="form-control"
                           value="{{ old('especialidad', $empleado->especialidad) }}"
                           placeholder="Ej: Coloración, Balayage, Keratinas...">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha de contratación</label>
                        <input type="date" name="fecha_contratacion" class="form-control"
                               value="{{ old('fecha_contratacion', $empleado->fecha_contratacion?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;padding-top:1.6rem;">
                        <input type="checkbox" name="activo" id="activo" value="1"
                               {{ old('activo', $empleado->activo) ? 'checked' : '' }}
                               style="width:auto;margin:0;">
                        <label for="activo" class="form-label" style="margin:0;cursor:pointer;">Empleado activo</label>
                    </div>
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid var(--border-color);">

                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--text-color);">Acceso al sistema</h4>

                @if($empleado->user)
                    <div style="background:var(--bg-secondary,#f8f8f6);border:1px solid var(--border-color);border-radius:6px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.82rem;color:var(--text-light);">
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;">
                            <span style="color:#22c55e;font-size:1rem;">●</span>
                            Login: teléfono <strong style="color:var(--text-color);">{{ $empleado->telefono }}</strong>
                            &nbsp;·&nbsp; Rol: <strong style="color:var(--text-color);">{{ $empleado->user->getRoleNames()->first() ?? '—' }}</strong>
                        </div>
                        @role('admin')
                            @if($empleado->clave_acceso)
                                <div style="margin-top:0.25rem;">
                                    Contraseña actual:
                                    <code style="background:#e8e4df;padding:0.1rem 0.4rem;border-radius:3px;font-size:0.85rem;color:var(--text-color);">{{ $empleado->clave_acceso }}</code>
                                </div>
                            @endif
                        @endrole
                    </div>

                    <div class="form-group" style="max-width:340px;">
                        <label class="form-label">Nueva contraseña <span style="font-size:0.75rem;color:var(--text-light);">(dejar vacío para no cambiar)</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Nueva contraseña">
                        @error('password')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                @else
                    <p style="font-size:0.78rem;color:var(--text-light);margin-bottom:1rem;">
                        Sin acceso al sistema. Ingresa una contraseña para habilitarlo —
                        el empleado usará su <strong>teléfono ({{ $empleado->telefono ?? 'sin teléfono' }})</strong> para ingresar.
                    </p>
                    <div class="form-group" style="max-width:340px;">
                        <label class="form-label">Contraseña <span style="font-size:0.75rem;color:var(--text-light);">(mín. 8 caracteres)</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Contraseña de acceso">
                        @error('password')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                        @error('telefono')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
