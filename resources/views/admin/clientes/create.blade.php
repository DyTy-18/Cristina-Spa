@extends('admin.layouts.app')

@section('title', 'Nuevo Cliente')
@section('page-title', 'Nuevo Cliente')

@section('content')

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.clientes.index') }}" class="btn btn-sm btn-outline">← Volver a clientes</a>
    </div>

    <div class="card" style="max-width:720px;">
        <div class="card-header">
            <h3 class="card-title">Registrar Cliente</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clientes.store') }}" method="POST">
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
                        <label class="form-label">Teléfono <span style="color:var(--error-color)">*</span></label>
                        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono') }}" placeholder="Ej: 70000000">
                        @error('telefono')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                        @error('email')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="{{ old('fecha_nacimiento') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               value="{{ old('direccion') }}" placeholder="Zona, ciudad">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas internas</label>
                    <textarea name="notas" class="form-control" rows="3"
                              placeholder="Preferencias, alergias, observaciones...">{{ old('notas') }}</textarea>
                </div>

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Registrar cliente</button>
                    <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
