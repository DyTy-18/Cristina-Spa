@extends('admin.layouts.app')

@section('title', 'Nueva Cita')
@section('page-title', 'Crear Nueva Cita')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Cita</h3>
        </div>
        <div class="card-body">
            <form action="#" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cliente</label>
                        <select class="form-control" name="cliente_id" required>
                            <option value="">Seleccionar cliente...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Servicio</label>
                        <select class="form-control" name="servicio_id" required>
                            <option value="">Seleccionar servicio...</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora</label>
                        <input type="time" class="form-control" name="hora" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Estilista</label>
                    <select class="form-control" name="estilista_id">
                        <option value="">Seleccionar estilista...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas</label>
                    <textarea class="form-control" name="notas" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Guardar Cita</button>
                    <a href="{{ route('admin.citas.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
