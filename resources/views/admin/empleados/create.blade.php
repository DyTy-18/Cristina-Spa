@extends('admin.layouts.app')

@section('title', 'Nuevo Empleado')
@section('page-title', 'Nuevo Empleado')

@section('content')

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.empleados.index') }}" class="btn btn-sm btn-outline">← Volver a empleados</a>
    </div>

    <div class="card" style="max-width:720px;">
        <div class="card-header">
            <h3 class="card-title">Registrar Empleado</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.empleados.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre <span style="color:var(--error-color)">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre') }}" placeholder="Nombre" autofocus>
                        @error('nombre')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control"
                               value="{{ old('apellido') }}" placeholder="Apellido">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cargo <span style="color:var(--error-color)">*</span></label>
                        <select name="cargo" class="form-control @error('cargo') is-invalid @enderror" required>
                            <option value="">— Seleccionar cargo —</option>
                            @foreach(['estilista','colorista','manicurista','esteticista','recepcionista','cajera','masajista','otro'] as $c)
                                <option value="{{ $c }}" {{ old('cargo') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                        @error('cargo')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ old('telefono') }}" placeholder="Ej: 70000000">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Especialidad</label>
                    <input type="text" name="especialidad" class="form-control"
                           value="{{ old('especialidad') }}"
                           placeholder="Ej: Coloración, Balayage, Keratinas...">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha de contratación</label>
                        <input type="date" name="fecha_contratacion" class="form-control"
                               value="{{ old('fecha_contratacion') }}">
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;padding-top:1.6rem;">
                        <input type="checkbox" name="activo" id="activo" value="1"
                               {{ old('activo', '1') ? 'checked' : '' }}
                               style="width:auto;margin:0;">
                        <label for="activo" class="form-label" style="margin:0;cursor:pointer;">Empleado activo</label>
                    </div>
                </div>

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Registrar empleado</button>
                    <a href="{{ route('admin.empleados.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
