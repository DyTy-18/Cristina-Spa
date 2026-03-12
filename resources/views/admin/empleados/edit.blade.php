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

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
