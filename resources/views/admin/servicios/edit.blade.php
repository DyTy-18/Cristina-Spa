@extends('admin.layouts.app')

@section('title', 'Editar Servicio')
@section('page-title', 'Editar Servicio')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar: {{ $servicio->nombre }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.servicios.update', $servicio) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nombre del Servicio *</label>
                    <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $servicio->nombre) }}"
                        required>
                    @error('nombre')
                        <small style="color: var(--error-color);">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio ($) *</label>
                        <input type="number" class="form-control" name="precio" step="0.01" min="0"
                            value="{{ old('precio', $servicio->precio) }}" required>
                        @error('precio')
                            <small style="color: var(--error-color);">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duración (minutos) *</label>
                        <input type="number" class="form-control" name="duracion_minutos" min="5" step="5"
                            value="{{ old('duracion_minutos', $servicio->duracion_minutos) }}" required>
                        @error('duracion_minutos')
                            <small style="color: var(--error-color);">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select class="form-control" name="categoria">
                        <option value="">Sin categoría</option>
                        <option value="cortes" {{ old('categoria', $servicio->categoria) == 'cortes' ? 'selected' : '' }}>
                            Cortes</option>
                        <option value="coloracion"
                            {{ old('categoria', $servicio->categoria) == 'coloracion' ? 'selected' : '' }}>Coloración
                        </option>
                        <option value="peinados"
                            {{ old('categoria', $servicio->categoria) == 'peinados' ? 'selected' : '' }}>Peinados</option>
                        <option value="tratamientos"
                            {{ old('categoria', $servicio->categoria) == 'tratamientos' ? 'selected' : '' }}>Tratamientos
                        </option>
                        <option value="spa" {{ old('categoria', $servicio->categoria) == 'spa' ? 'selected' : '' }}>Spa
                        </option>
                        <option value="eventos"
                            {{ old('categoria', $servicio->categoria) == 'eventos' ? 'selected' : '' }}>Eventos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="activo" value="1"
                            {{ old('activo', $servicio->activo) ? 'checked' : '' }}>
                        <span>Servicio activo</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Actualizar Servicio</button>
                    <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
