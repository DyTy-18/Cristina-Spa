@extends('admin.layouts.app')

@section('title', 'Editar Sucursal')
@section('page-title', 'Editar Sucursal')

@section('content')

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.sucursales.index') }}" class="btn btn-sm btn-outline">← Volver a sucursales</a>
    </div>

    <div class="card" style="max-width:720px;">
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

@endsection
