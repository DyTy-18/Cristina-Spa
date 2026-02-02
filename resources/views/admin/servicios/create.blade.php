@extends('admin.layouts.app')

@section('title', 'Nuevo Servicio')
@section('page-title', 'Crear Nuevo Servicio')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información del Servicio</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.servicios.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nombre del Servicio *</label>
                    <input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <small style="color: var(--error-color);">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio ($) *</label>
                        <input type="number" class="form-control" name="precio" step="0.01" min="0"
                            value="{{ old('precio') }}" required>
                        @error('precio')
                            <small style="color: var(--error-color);">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración (minutos) *</label>
                        <input type="number" class="form-control" name="duracion_minutos" min="5" step="5"
                            value="{{ old('duracion_minutos', 30) }}" required>
                        @error('duracion_minutos')
                            <small style="color: var(--error-color);">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select class="form-control" name="categoria">
                        <option value="">Sin categoría</option>
                        <option value="cortes" {{ old('categoria') == 'cortes' ? 'selected' : '' }}>Cortes</option>
                        <option value="coloracion" {{ old('categoria') == 'coloracion' ? 'selected' : '' }}>Coloración
                        </option>
                        <option value="peinados" {{ old('categoria') == 'peinados' ? 'selected' : '' }}>Peinados</option>
                        <option value="tratamientos" {{ old('categoria') == 'tratamientos' ? 'selected' : '' }}>
                            Tratamientos</option>
                        <option value="spa" {{ old('categoria') == 'spa' ? 'selected' : '' }}>Spa</option>
                        <option value="eventos" {{ old('categoria') == 'eventos' ? 'selected' : '' }}>Eventos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Guardar Servicio</button>
                    <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
