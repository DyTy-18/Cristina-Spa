@extends('admin.layouts.app')

@section('title', 'Comisiones')
@section('page-title', 'Comisiones por Servicio')

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

    .sin-comision { color: var(--text-light); font-style: italic; font-size: 0.8rem; }

    .badge-activa   { color: var(--success-color, #28a745); font-size: 0.78rem; }
    .badge-inactiva { color: var(--text-light); font-size: 0.78rem; }
</style>
@endpush

@section('content')

    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Comisiones por servicio</h3>
            <span style="font-size:0.82rem;color:var(--text-light);font-weight:300;">
                Configura el porcentaje de comisión y actívalo o desactívalo por servicio
            </span>
        </div>

        <table class="data-table comision-table">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th style="text-align:center;">Activo</th>
                    <th style="text-align:center;">Porcentaje</th>
                    <th style="text-align:right;">Guardar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($servicios as $servicio)
                    @php
                        $com = $servicio->comision;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:400;color:var(--text-dark);">{{ $servicio->nombre }}</div>
                            @if(!$servicio->activo)
                                <div style="font-size:0.75rem;color:var(--text-light);font-weight:300;">Servicio inactivo</div>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <form action="{{ route('admin.comisiones.update', $servicio) }}"
                                  method="POST"
                                  id="form-{{ $servicio->id }}"
                                  style="display:contents;">
                                @csrf
                                @method('PUT')

                                <div class="toggle-wrap" style="justify-content:center;">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="activo" value="1"
                                               {{ $com?->activo ?? true ? 'checked' : '' }}
                                               onchange="document.getElementById('form-{{ $servicio->id }}').requestSubmit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </form>
                        </td>
                        <td style="text-align:center;">
                            <form action="{{ route('admin.comisiones.update', $servicio) }}"
                                  method="POST"
                                  id="form-pct-{{ $servicio->id }}"
                                  style="display:flex;justify-content:center;">
                                @csrf
                                @method('PUT')
                                {{-- pass activo mirror --}}
                                <input type="hidden" name="activo"
                                       value="{{ $com?->activo ?? true ? '1' : '0' }}"
                                       class="activo-mirror-{{ $servicio->id }}">

                                <div class="porcentaje-wrap">
                                    <input type="number"
                                           name="porcentaje"
                                           class="form-control"
                                           value="{{ $com ? number_format((float)$com->porcentaje, 2, '.', '') : '0.00' }}"
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
    // Keep activo mirrors in sync with the toggle checkboxes
    document.querySelectorAll('.toggle-switch input[name="activo"]').forEach(chk => {
        chk.addEventListener('change', function () {
            const sid   = this.closest('form').id.replace('form-', '');
            const mirrors = document.querySelectorAll('.activo-mirror-' + sid);
            mirrors.forEach(m => m.value = this.checked ? '1' : '0');
        });
    });
</script>
@endpush
