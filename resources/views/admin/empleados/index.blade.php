@extends('admin.layouts.app')

@section('title', 'Empleados')
@section('page-title', 'Gestión de Empleados')

@section('content')
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todos los Empleados</h3>
            <div style="display:flex;align-items:center;gap:1rem;">
                <span class="stat-label">{{ $empleados->count() }} registrados</span>
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.reportes.comisiones') }}" class="btn btn-outline btn-sm">📈 Reporte comisiones</a>
                @endif
                <a href="{{ route('admin.empleados.create') }}" class="btn btn-primary btn-sm">+ Nuevo Empleado</a>
            </div>
        </div>

        @if($empleados->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">💼</div>
                <p class="empty-state-text">Aún no hay empleados registrados</p>
                <a href="{{ route('admin.empleados.create') }}" class="btn btn-primary">Registrar Primer Empleado</a>
            </div>
        @else
            <div class="table-filter" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Buscar por nombre, cargo o especialidad..."
                    autocomplete="off"
                >
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <a href="{{ route('admin.empleados.index') }}"
                       class="btn btn-sm {{ !$cargo ? 'btn-primary' : 'btn-outline' }}">
                        Todos
                    </a>
                    @foreach($cargos as $c)
                        <a href="{{ route('admin.empleados.index', ['cargo' => $c]) }}"
                           class="btn btn-sm {{ $cargo === $c ? 'btn-primary' : 'btn-outline' }}"
                           style="text-transform:capitalize;">
                            {{ ucfirst($c) }}
                        </a>
                    @endforeach
                </div>
            </div>

            <table class="data-table" id="empleadosTable">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th>Especialidad</th>
                        <th>Teléfono</th>
                        <th>Citas</th>
                        <th>Desde</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empleados as $empleado)
                        <tr class="empleado-row" style="{{ !$empleado->activo ? 'opacity:0.55;' : '' }}">
                            <td>
                                <div style="font-weight:400;">{{ $empleado->nombre_completo }}</div>
                            </td>
                            <td>
                                <span class="cargo-badge cargo-{{ $empleado->cargo }}">
                                    {{ ucfirst($empleado->cargo) }}
                                </span>
                            </td>
                            <td style="font-size:0.85rem;color:var(--text-light);">
                                {{ $empleado->especialidad ?? '—' }}
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $empleado->telefono ?? '—' }}
                            </td>
                            <td>
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;">
                                    {{ $empleado->cita_servicios_count }}
                                </span>
                            </td>
                            <td style="font-size:0.85rem;">
                                {{ $empleado->fecha_contratacion ? $empleado->fecha_contratacion->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                @if($empleado->activo)
                                    <span style="color:#4caf50;font-size:0.8rem;">● Activo</span>
                                @else
                                    <span style="color:var(--text-light);font-size:0.8rem;">● Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-sm btn-outline">
                                        Ver
                                    </a>
                                    <a href="{{ route('admin.empleados.edit', $empleado) }}" class="btn btn-sm btn-accent">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .cargo-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.03em;
        text-transform: capitalize;
    }
    .cargo-estilista    { background: #f3e5f5; color: #7b1fa2; }
    .cargo-colorista    { background: #fce4ec; color: #c62828; }
    .cargo-manicurista  { background: #e8f5e9; color: #2e7d32; }
    .cargo-esteticista  { background: #e3f2fd; color: #1565c0; }
    .cargo-recepcionista{ background: #fff3e0; color: #e65100; }
    .cargo-cajera       { background: #f1f8e9; color: #558b2f; }
    .cargo-masajista    { background: #e0f7fa; color: #00695c; }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#empleadosTable .empleado-row').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
@endpush
