@extends('admin.layouts.app')

@section('title', 'Editar — ' . $cliente->nombre_completo)
@section('page-title', 'Editar Cliente')

@section('content')

    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;">
        <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-sm btn-outline">← Volver al historial</a>
    </div>

    <div class="card" style="max-width:720px;">
        <div class="card-header" style="display:flex;align-items:center;gap:1rem;">
            <div style="width:40px;height:40px;background:var(--accent-color);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--white);flex-shrink:0;">
                {{ mb_substr($cliente->nombre, 0, 1) }}
            </div>
            <div>
                <h3 class="card-title">{{ $cliente->nombre_completo }}</h3>
                @if($cliente->citas()->where('estado','completada')->count() > 0)
                    <span style="font-size:0.75rem;color:var(--text-light);font-weight:300;">
                        {{ $cliente->citas()->where('estado','completada')->count() }} visitas completadas
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clientes.update', $cliente) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre <span style="color:var(--error-color)">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $cliente->nombre) }}" autofocus>
                        @error('nombre')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control"
                               value="{{ old('apellido', $cliente->apellido) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono <span style="color:var(--error-color)">*</span></label>
                        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono', $cliente->telefono) }}">
                        @error('telefono')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $cliente->email) }}">
                        @error('email')
                            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               value="{{ old('direccion', $cliente->direccion) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas internas</label>
                    <textarea name="notas" class="form-control" rows="3"
                              placeholder="Preferencias, alergias, observaciones...">{{ old('notas', $cliente->notas) }}</textarea>
                </div>

                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

@endsection
