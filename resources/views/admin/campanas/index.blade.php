@extends('admin.layouts.app')

@section('title', 'Campañas')
@section('page-title', 'Campañas')

@section('content')

    <div style="margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
        <div>
            <input type="text" id="searchInput" class="form-control"
                   placeholder="Buscar campaña..." style="width:260px;display:inline-block;">
        </div>
        <a href="{{ route('admin.campanas.create') }}" class="btn btn-accent">+ Nueva Campaña</a>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Todas las Campañas</h3>
            <span style="font-size:0.82rem;color:var(--text-light);font-weight:300;">
                {{ $campanas->count() }} {{ $campanas->count() === 1 ? 'campaña' : 'campañas' }}
            </span>
        </div>

        @if($campanas->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📣</div>
                <p class="empty-state-text">No hay campañas registradas aún</p>
                <a href="{{ route('admin.campanas.create') }}" class="btn btn-accent">Crear primera campaña</a>
            </div>
        @else
            <table class="data-table" id="campanaTable">
                <thead>
                    <tr>
                        <th>Campaña</th>
                        <th>Período</th>
                        <th style="text-align:center;">Visitas vinculadas</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campanas as $campana)
                        <tr class="campana-row" data-nombre="{{ strtolower($campana->nombre) }}">
                            <td>
                                <div style="font-weight:400;color:var(--text-dark);">{{ $campana->nombre }}</div>
                                @if($campana->descripcion)
                                    <div style="font-size:0.78rem;color:var(--text-light);font-weight:300;margin-top:0.1rem;">
                                        {{ Str::limit($campana->descripcion, 70) }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:0.85rem;color:var(--text-light);white-space:nowrap;">
                                @if($campana->fecha_inicio || $campana->fecha_fin)
                                    {{ $campana->fecha_inicio?->format('d/m/Y') ?? '—' }}
                                    &nbsp;→&nbsp;
                                    {{ $campana->fecha_fin?->format('d/m/Y') ?? '∞' }}
                                @else
                                    <span style="font-style:italic;">Sin límite de fechas</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:var(--secondary-color);line-height:1;">
                                    {{ $campana->citas_count }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                @if($campana->activo)
                                    <span style="color:var(--success-color,#28a745);font-size:0.82rem;">● Activa</span>
                                @else
                                    <span style="color:var(--text-light);font-size:0.82rem;">● Inactiva</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.campanas.edit', $campana) }}"
                                   class="btn btn-sm btn-outline">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#campanaTable .campana-row').forEach(row => {
            row.style.display = row.dataset.nombre.includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
