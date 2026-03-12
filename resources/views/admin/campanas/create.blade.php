@extends('admin.layouts.app')

@section('title', 'Nueva Campaña')
@section('page-title', 'Nueva Campaña')

@section('content')

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.campanas.index') }}" class="btn btn-sm btn-outline">← Volver a campañas</a>
    </div>

    <div class="card" style="max-width:720px;">
        <div class="card-header">
            <h3 class="card-title">Crear Campaña</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.campanas.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nombre de la campaña <span style="color:var(--error-color)">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}"
                           placeholder="Ej: RENUEVA TU COLOR Y CELEBRA QUIÉN ERES"
                           autofocus>
                    @error('nombre')
                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                              placeholder="Descripción breve de la campaña, condiciones, servicios incluidos...">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control"
                               value="{{ old('fecha_inicio') }}">
                        @error('fecha_inicio')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" name="fecha_fin" class="form-control"
                               value="{{ old('fecha_fin') }}">
                        @error('fecha_fin')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:0.75rem;">
                    <input type="checkbox" name="activo" id="activo" value="1"
                           {{ old('activo', '1') ? 'checked' : '' }}
                           style="width:auto;margin:0;">
                    <label for="activo" class="form-label" style="margin:0;cursor:pointer;">Campaña activa</label>
                </div>

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Crear campaña</button>
                    <a href="{{ route('admin.campanas.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
