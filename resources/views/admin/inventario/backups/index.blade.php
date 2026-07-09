@extends('admin.layouts.app')

@section('title', 'Backups de Inventario')
@section('page-title', 'Backups de Inventario')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Backups de Inventario</h3>
            <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline btn-sm">Ver Stock</a>
        </div>

        @if ($backups->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha de eliminación</th>
                        <th>Eliminado por</th>
                        <th style="text-align:right;">Productos</th>
                        <th style="text-align:right;">Entradas</th>
                        <th style="text-align:right;">Salidas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backups as $backup)
                        <tr>
                            <td>{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $backup->user->name ?? 'Usuario eliminado' }}</td>
                            <td style="text-align:right;">{{ $backup->productos_count }}</td>
                            <td style="text-align:right;">{{ $backup->entradas_count }}</td>
                            <td style="text-align:right;">{{ $backup->salidas_count }}</td>
                            <td>
                                <a href="{{ route('admin.inventario.backups.show', $backup) }}" class="btn btn-outline btn-sm">Ver detalle</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:1rem 1.5rem;">
                {{ $backups->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">🗂</div>
                <p class="empty-state-text">Todavía no se ha limpiado el inventario, no hay backups.</p>
            </div>
        @endif
    </div>
@endsection
