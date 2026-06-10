{{-- Partial: desglose agrupado (por día o por mes) --}}
{{-- Variables: $porPeriodo (array), $formatoLabel ('dia'|'mes'), $totalGeneral, $porTipoPago, $citas, $periodo, $anio (opcional), $mes (opcional) --}}
<table class="ingresos-table">
    <thead>
        <tr>
            <th>{{ $formatoLabel === 'dia' ? 'Día' : 'Mes' }}</th>
            <th>Citas</th>
            <th><span class="tipo-dot dot-efectivo" style="vertical-align:middle;margin-right:3px;"></span>Efectivo</th>
            <th><span class="tipo-dot dot-tarjeta"  style="vertical-align:middle;margin-right:3px;"></span>Tarjeta</th>
            <th><span class="tipo-dot dot-qr"       style="vertical-align:middle;margin-right:3px;"></span>QR</th>
            <th><span class="tipo-dot dot-sin"      style="vertical-align:middle;margin-right:3px;"></span>Sin esp.</th>
            <th>Distribución</th>
            <th style="text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($porPeriodo as $key => $fila)
        <tr>
            <td>
                @if($formatoLabel === 'dia')
                    @php $dt = \Carbon\Carbon::parse($key); @endphp
                    <a href="{{ route('admin.ingresos.index', array_merge(request()->except('periodo','fecha'), ['periodo'=>'diario','fecha'=>$key])) }}"
                       style="color:var(--text-dark);text-decoration:none;font-weight:400;font-size:0.84rem;">
                        {{ $dt->isoFormat('ddd D') }}
                    </a>
                @else
                    {{-- $key es número de mes, $fila['nombre'] es el nombre --}}
                    <a href="{{ route('admin.ingresos.index', array_merge(request()->except('periodo','mes'), ['periodo'=>'mensual','mes'=>$key,'anio'=>$anio ?? now()->year])) }}"
                       style="color:var(--text-dark);text-decoration:none;font-weight:400;font-size:0.84rem;">
                        {{ $fila['nombre'] }} {{ $anio ?? '' }}
                    </a>
                @endif
            </td>
            <td style="color:var(--text-light);">{{ $fila['cantidad'] }}</td>
            <td style="color:{{ $fila['efectivo'] > 0 ? '#1a7a35' : 'var(--text-light)' }};">
                {{ $fila['efectivo'] > 0 ? 'Bs. '.number_format($fila['efectivo'],2) : '—' }}
            </td>
            <td style="color:{{ $fila['tarjeta'] > 0 ? '#0056b3' : 'var(--text-light)' }};">
                {{ $fila['tarjeta'] > 0 ? 'Bs. '.number_format($fila['tarjeta'],2) : '—' }}
            </td>
            <td style="color:{{ $fila['qr'] > 0 ? '#7a5c0f' : 'var(--text-light)' }};">
                {{ $fila['qr'] > 0 ? 'Bs. '.number_format($fila['qr'],2) : '—' }}
            </td>
            <td style="color:var(--text-light);">
                {{ $fila['sin_especificar'] > 0 ? 'Bs. '.number_format($fila['sin_especificar'],2) : '—' }}
            </td>
            <td>
                @if($fila['total'] > 0)
                <div class="bar-visual">
                    @foreach(['efectivo'=>'ef','tarjeta'=>'tar','qr'=>'qr','sin_especificar'=>'sin'] as $tipo => $cls)
                        @if($fila[$tipo] > 0)
                        <div class="bar-seg bar-{{ $cls }}" style="width:{{ $fila[$tipo]/$fila['total']*100 }}%;"></div>
                        @endif
                    @endforeach
                </div>
                @endif
            </td>
            <td style="text-align:right;font-weight:500;font-family:'Cormorant Garamond',serif;font-size:0.98rem;color:{{ $fila['total']>0?'var(--secondary-color)':'var(--text-light)' }};white-space:nowrap;">
                {{ $fila['total'] > 0 ? 'Bs. '.number_format($fila['total'],2) : '—' }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="tfoot-row">
            <td>Total</td>
            <td style="color:var(--text-light);">{{ $citas->count() }}</td>
            <td style="color:{{ $porTipoPago['efectivo']['total']>0?'#1a7a35':'var(--text-light)' }};">
                {{ $porTipoPago['efectivo']['total']>0 ? 'Bs. '.number_format($porTipoPago['efectivo']['total'],2) : '—' }}
            </td>
            <td style="color:{{ $porTipoPago['tarjeta']['total']>0?'#0056b3':'var(--text-light)' }};">
                {{ $porTipoPago['tarjeta']['total']>0 ? 'Bs. '.number_format($porTipoPago['tarjeta']['total'],2) : '—' }}
            </td>
            <td style="color:{{ $porTipoPago['qr']['total']>0?'#7a5c0f':'var(--text-light)' }};">
                {{ $porTipoPago['qr']['total']>0 ? 'Bs. '.number_format($porTipoPago['qr']['total'],2) : '—' }}
            </td>
            <td style="color:var(--text-light);">
                {{ $porTipoPago['sin_especificar']['total']>0 ? 'Bs. '.number_format($porTipoPago['sin_especificar']['total'],2) : '—' }}
            </td>
            <td></td>
            <td style="text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.05rem;color:var(--primary-color);white-space:nowrap;">
                Bs. {{ number_format($totalGeneral, 2) }}
            </td>
        </tr>
    </tfoot>
</table>
