@extends('admin.layouts.app')

@section('title', 'Empleados')
@section('page-title', 'Gestión de Empleados')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todos los Empleados</h3>
            <a href="#" class="btn btn-primary">+ Nuevo Empleado</a>
        </div>

        <div class="empty-state">
            <div class="empty-state-icon">💼</div>
            <p class="empty-state-text">Aún no hay empleados registrados</p>
            <a href="#" class="btn btn-primary">Registrar Primer Empleado</a>
        </div>
    </div>
@endsection
