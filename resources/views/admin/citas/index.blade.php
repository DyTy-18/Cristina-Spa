@extends('admin.layouts.app')

@section('title', 'Citas')
@section('page-title', 'Gestión de Citas')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todas las Citas</h3>
            <a href="{{ route('admin.citas.create') }}" class="btn btn-primary">+ Nueva Cita</a>
        </div>

        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p class="empty-state-text">Aún no hay citas registradas</p>
            <a href="{{ route('admin.citas.create') }}" class="btn btn-primary">Crear Primera Cita</a>
        </div>
    </div>
@endsection
