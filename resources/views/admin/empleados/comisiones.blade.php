@extends('admin.layouts.app')

@section('title', 'Comisiones — ' . $empleado->nombre_completo)
@section('page-title', 'Comisiones del Empleado')

@push('styles')
<style>
    .comision-table td { vertical-align: middle; }

    .toggle-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
        flex-shrink: 0;
    }
    .toggle-switch input { display: none; }
    .toggle-slider {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.18);
        border-radius: 22px;
        cursor: pointer;
        transition: .25s;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .25s;
    }
    .toggle-switch input:checked + .toggle-slider { background: var(--accent-color); }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(18px); }

    .porcentaje-wrap {
        position: relative;
        width: 110px;
    }
    .porcentaje-wrap input {
        padding-right: 2rem !important;
        text-align: right;
    }
    .porcentaje-suffix {
        position: absolute;
        right: 0.6rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        color: var(--text-light);
        pointer-events: none;
    }

    .badge-personalizada {
        font-size: 0.72rem;
        background: var(--accent-color);
        color: var(--white);
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
        font-weight: 400;
    }
    .badge-heredada {
        font-size: 0.72rem;
        color: var(--text-light);
        font-style: italic;
    }

    .empleado-header-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .empleado-avatar-sm {
        width: 48px;
        height: 48px;
        background: var(--accent-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.4rem;
        color: var(--white);
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

    {{-- Back --}}
    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('admin.empleados.show', $empleado) }}" class="btn btn-sm btn-outline">← Volver al perfil</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
    @endif

    {{-- Empleado header --}}
    <div class="empleado-header-card">
        <div class="empleado-avatar-sm">{{ mb_substr($empleado->nombre, 0, 1) }}</div>
        <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--primary-color);line-height:1.1;">
                {{ $empleado->nombre_completo }}
            </div>
            <div style="font-size:0.82rem;color:var(--text-light);margin-top:0.2rem;">
                {{ ucfirst($empleado->cargo) }}
                @if($empleado->especialidad) · {{ $empleado->especialidad }}@endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Comisiones por servicio</h3>
            <span style="font-size:0.82rem;color:var(--text-light);font-weight:300;">
                Las comisiones personalizadas tienen prioridad sobre la comisión base del servicio
            </span>
        </div>

        <table class="data-table comision-table">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th style="text-align:center;">Comisión base</th>
                    <th style="text-align:center;">Activo</th>
                    <th style="text-align:center;">Comisión personal</th>
                    <th style="text-align:right;">Guardar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($servicios as $servicio)
                    @php
                        $comBase      = $servicio->comision;
                        $comPersonal  = $servicio->comisionesEmpleado->first();
                        $tienePersonal = $comPersonal !== null;
                        $pctActual    = $tienePersonal
                            ? number_format((float) $comPersonal->porcentaje, 2, '.', '')
                            : number_format((float) ($comBase?->porcentaje ?? 0), 2, '.', '');
                        $activoActual = $tienePersonal ? $comPersonal->activo : ($comBase?->activo ?? true);
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:400;color:var(--text-dark);">{{ $servicio->nombre }}</div>
                            @if(!$servicio->activo)
                                <div style="font-size:0.75rem;color:var(--text-light);font-weight:300;">Servicio inactivo</div>
                            @endif
                            @if($tienePersonal)
                                <span class="badge-personalizada">Personalizada</span>
                            @else
                                <span class="badge-heredada">Heredada del servicio</span>
                            @endif
                        </td>
                        <td style="text-align:center;color:var(--text-light);font-size:0.88rem;">
                            @if($comBase && $comBase->activo)
                                {{ number_format((float) $comBase->porcentaje, 2) }}%
                            @else
                                <span style="font-style:italic;">Sin configurar</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <form action="{{ route('admin.empleados.comisiones.update', [$empleado, $servicio]) }}"
                                  method="POST"
                                  id="form-{{ $servicio->id }}"
                                  style="display:contents;">
                                @csrf
                                @method('PUT')

                                <div class="toggle-wrap" style="justify-content:center;">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="activo" value="1"
                                               {{ $activoActual ? 'checked' : '' }}
                                               onchange="document.getElementById('form-{{ $servicio->id }}').requestSubmit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </form>
                        </td>
                        <td style="text-align:center;">
                            <form action="{{ route('admin.empleados.comisiones.update', [$empleado, $servicio]) }}"
                                  method="POST"
                                  id="form-pct-{{ $servicio->id }}"
                                  style="display:flex;justify-content:center;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="activo"
                                       value="{{ $activoActual ? '1' : '0' }}"
                                       class="activo-mirror-{{ $servicio->id }}">

                                <div class="porcentaje-wrap">
                                    <input type="number"
                                           name="porcentaje"
                                           class="form-control"
                                           value="{{ $pctActual }}"
                                           min="0" max="100" step="0.01"
                                           style="width:100%;"
                                           onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('form-pct-{{ $servicio->id }}').requestSubmit();}">
                                    <span class="porcentaje-suffix">%</span>
                                </div>
                            </form>
                        </td>
                        <td style="text-align:right;">
                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('form-pct-{{ $servicio->id }}').requestSubmit()">
                                Guardar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-switch input[name="activo"]').forEach(chk => {
        chk.addEventListener('change', function () {
            const sid = this.closest('form').id.replace('form-', '');
            document.querySelectorAll('.activo-mirror-' + sid)
                    .forEach(m => m.value = this.checked ? '1' : '0');
        });
    });
</script>
@endpush
