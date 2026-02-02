@extends('admin.layouts.app')

@section('title', 'Mis Citas')
@section('page-title', 'Mis Citas de Hoy')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Citas Asignadas</h3>
        </div>

        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p class="empty-state-text">No tienes citas asignadas para hoy</p>
        </div>
    </div>
@endsection
