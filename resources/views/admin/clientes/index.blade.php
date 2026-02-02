@extends('admin.layouts.app')

@section('title', 'Clientes')
@section('page-title', 'Gestión de Clientes')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todos los Clientes</h3>
            <a href="#" class="btn btn-primary">+ Nuevo Cliente</a>
        </div>

        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <p class="empty-state-text">Aún no hay clientes registrados</p>
            <a href="#" class="btn btn-primary">Registrar Primer Cliente</a>
        </div>
    </div>
@endsection
