{{--
    Badge/filtro de sucursal para páginas índice.
    Admin → dropdown que cambia la sucursal activa al instante.
    Otros → pill con el nombre de la sucursal (solo lectura).
--}}
@if(auth()->user()->hasRole('admin'))
    <form method="POST" action="{{ route('admin.sucursal.cambiar') }}"
          style="display:inline-flex;align-items:center;gap:0.4rem;">
        @csrf
        <span style="font-size:0.7rem;color:var(--text-light);white-space:nowrap;text-transform:uppercase;letter-spacing:0.05em;">
            Sucursal:
        </span>
        <select name="sucursal_id" onchange="this.form.submit()"
                style="font-size:0.78rem;padding:0.22rem 0.5rem;border:1px solid var(--border-color);
                       border-radius:20px;background:var(--white,#fff);color:var(--text-color);
                       cursor:pointer;max-width:220px;font-family:inherit;">
            <option value="" {{ ($modoGlobal ?? false) ? 'selected' : '' }}>
                🌐 Todas las sucursales
            </option>
            @foreach($sucursales as $s)
                <option value="{{ $s->id }}"
                    {{ !($modoGlobal ?? false) && ($sucursalActiva?->id == $s->id) ? 'selected' : '' }}>
                    {{ $s->es_principal ? '★ ' : '' }}{{ $s->nombre }}
                </option>
            @endforeach
        </select>
    </form>
@elseif(isset($sucursalActiva) && $sucursalActiva)
    <span style="display:inline-flex;align-items:center;gap:0.35rem;font-size:0.78rem;
                 padding:0.22rem 0.75rem;background:#eff6ff;border:1px solid #bfdbfe;
                 border-radius:20px;color:#1d4ed8;white-space:nowrap;">
        🏢 {{ $sucursalActiva->nombre }}
    </span>
@endif
