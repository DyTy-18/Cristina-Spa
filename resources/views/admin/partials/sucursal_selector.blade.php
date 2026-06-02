{{--
    Incluir dentro de un <form>.
    - Admin en modo global → dropdown obligatorio (debe elegir sucursal)
    - Admin con sucursal activa → banner azul informativo + hidden input
    - Encargado / otros → banner gris + hidden input
--}}
@php
    $sid            = session('sucursal_activa_id');
    $enModoGlobal   = $modoGlobal ?? is_null($sid);
    $nombreSucursal = $sucursalActiva->nombre ?? null;
@endphp

@if($enModoGlobal && auth()->user()->hasRole('admin'))
    {{-- Admin sin sucursal seleccionada: debe elegir --}}
    <div class="form-group" style="background:#fef9ec;border:1px solid #f5d87a;border-radius:6px;padding:0.85rem 1rem;margin-bottom:1.25rem;">
        <label class="form-label" style="color:#92600a;margin-bottom:0.4rem;display:block;">
            🏢 Sucursal <span style="color:var(--error-color)">*</span>
            <span style="font-size:0.72rem;font-weight:300;"> — Estás viendo todas las sucursales. Elige dónde registrar este dato.</span>
        </label>
        <select name="sucursal_id" class="form-control @error('sucursal_id') is-invalid @enderror" required>
            <option value="">— Seleccionar sucursal —</option>
            @foreach($sucursales as $s)
                <option value="{{ $s->id }}" {{ old('sucursal_id') == $s->id ? 'selected' : '' }}>
                    {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                </option>
            @endforeach
        </select>
        @error('sucursal_id')
            <span style="color:var(--error-color);font-size:0.78rem;">{{ $message }}</span>
        @enderror
    </div>

@else
    {{-- Sucursal definida: mostrar banner y campo oculto --}}
    <div style="display:flex;align-items:center;gap:0.6rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:0.55rem 1rem;margin-bottom:1.25rem;">
        <span style="font-size:1rem;">🏢</span>
        <div style="flex:1;">
            <span style="font-size:0.72rem;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.04em;font-weight:500;">Registrando en</span>
            <div style="font-size:0.88rem;color:#1e3a8a;font-weight:500;">{{ $nombreSucursal ?? 'Sin sucursal' }}</div>
        </div>
        @if(auth()->user()->hasRole('admin'))
            <span style="font-size:0.7rem;color:#3b82f6;">
                Cambia desde el encabezado
            </span>
        @endif
    </div>
    <input type="hidden" name="sucursal_id" value="{{ $sid ?? auth()->user()->sucursal_id }}">
@endif
