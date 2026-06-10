{{-- Partial: lista de citas individuales --}}
{{-- Variables: $citas (Collection), $showFecha (bool) --}}
<table class="ingresos-table">
    <thead>
        <tr>
            @if($showFecha) <th>Fecha</th> @endif
            <th>Hora</th>
            <th>Cliente</th>
            <th>Servicios</th>
            <th>Tipo de pago</th>
            <th style="text-align:right;">Total</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($citas as $cita)
        <tr>
            @if($showFecha)
            <td style="color:var(--text-light);font-size:0.78rem;white-space:nowrap;">
                {{ $cita->fecha->isoFormat('D MMM') }}
            </td>
            @endif
            <td style="color:var(--text-light);font-size:0.78rem;white-space:nowrap;">
                {{ $cita->hora->format('H:i') }}
            </td>
            <td>
                <a href="{{ route('admin.clientes.show', $cita->cliente) }}"
                   style="color:var(--text-dark);text-decoration:none;font-weight:400;">
                    {{ $cita->cliente->nombre }} {{ $cita->cliente->apellido }}
                </a>
            </td>
            <td style="color:var(--text-light);font-size:0.78rem;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $cita->citaServicios->pluck('servicio.nombre')->filter()->implode(', ') ?: '—' }}
            </td>
            <td>
                @if($cita->tipo_pago === 'efectivo')
                    <span class="pago-pill pago-efectivo">Efectivo</span>
                @elseif($cita->tipo_pago === 'tarjeta')
                    <span class="pago-pill pago-tarjeta">Tarjeta</span>
                @elseif($cita->tipo_pago === 'qr')
                    <span class="pago-pill pago-qr">QR</span>
                @else
                    <span class="pago-pill pago-sin">—</span>
                @endif
            </td>
            <td style="text-align:right;font-weight:500;font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--secondary-color);white-space:nowrap;">
                Bs. {{ number_format($cita->precio_final, 2) }}
            </td>
            <td style="text-align:right;">
                <a href="{{ route('admin.citas.show', $cita) }}"
                   style="font-size:0.73rem;color:var(--text-light);text-decoration:none;">Ver →</a>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="tfoot-row">
            <td colspan="{{ $showFecha ? 5 : 4 }}">Total</td>
            <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.05rem;color:var(--primary-color);white-space:nowrap;">
                Bs. {{ number_format($citas->sum('precio_final'), 2) }}
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>
