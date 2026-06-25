<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #2c2c2c; }

        .header { margin-bottom: 18px; border-bottom: 2px solid #2c2c2c; padding-bottom: 10px; }
        .header h1 { font-size: 16pt; font-weight: 400; letter-spacing: 2px; text-transform: uppercase; }
        .header .sub { font-size: 8pt; color: #777; margin-top: 3px; }

        .resumen { display: flex; gap: 12px; margin-bottom: 16px; }
        .res-card { flex: 1; border: 1px solid #e0e0e0; padding: 7px 10px; background: #fafafa; }
        .res-val { font-size: 14pt; font-weight: 600; color: #2c2c2c; }
        .res-label { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.5px; color: #999; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #2c2c2c; color: #fff; padding: 5px 7px; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 400; text-align: left; white-space: nowrap; }
        td { padding: 4.5px 7px; font-size: 8.5pt; border-bottom: 1px solid #eeeeee; vertical-align: top; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .monto { color: #c9a96e; font-weight: 600; }
        .tag { display: inline-block; background: rgba(201,169,110,0.15); color: #9a7540; font-size: 7pt; padding: 1px 4px; margin: 1px 1px 0 0; }
        .dim { color: #aaa; }
        .footer { margin-top: 14px; font-size: 7.5pt; color: #aaa; border-top: 1px solid #e0e0e0; padding-top: 6px; }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte de Clientes</h1>
    <div class="sub">
        @if($desde || $hasta)
            Período: {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : '—' }} al {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : '—' }}
            &nbsp;·&nbsp;
        @endif
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- Resumen --}}
<div class="resumen">
    <div class="res-card">
        <div class="res-val">{{ $totales['clientes'] }}</div>
        <div class="res-label">Clientes</div>
    </div>
    <div class="res-card">
        <div class="res-val">{{ $totales['total_visitas'] }}</div>
        <div class="res-label">Visitas</div>
    </div>
    <div class="res-card">
        <div class="res-val" style="color:#c9a96e;">Bs. {{ number_format($totales['total_gastado'], 2) }}</div>
        <div class="res-label">Total generado</div>
    </div>
    <div class="res-card">
        <div class="res-val">{{ $totales['con_telefono'] }}</div>
        <div class="res-label">Con teléfono</div>
    </div>
    <div class="res-card">
        <div class="res-val">{{ $totales['con_email'] }}</div>
        <div class="res-label">Con email</div>
    </div>
    <div class="res-card">
        <div class="res-val">{{ $totales['con_fecha_nac'] }}</div>
        <div class="res-label">Con fecha nac.</div>
    </div>
</div>

{{-- Tabla --}}
<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Nacimiento</th>
            <th style="text-align:center;">Visitas</th>
            <th>Total (Bs.)</th>
            <th>Primera visita</th>
            <th>Última visita</th>
            <th>Servicios</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filas as $fila)
            @php $c = $fila['cliente']; @endphp
            <tr>
                <td>{{ $c->nombre_completo }}</td>
                <td>{{ $c->telefono ?: '—' }}</td>
                <td style="font-size:7.5pt;">{{ $c->email ?: '—' }}</td>
                <td>{{ $c->fecha_nacimiento ? $c->fecha_nacimiento->format('d/m/Y') : '—' }}</td>
                <td style="text-align:center;">{{ $fila['visitas'] ?: '—' }}</td>
                <td>
                    @if($fila['total_gastado'] > 0)
                        <span class="monto">{{ number_format($fila['total_gastado'], 2) }}</span>
                    @else
                        <span class="dim">—</span>
                    @endif
                </td>
                <td>{{ $fila['primera_visita'] ? \Carbon\Carbon::parse($fila['primera_visita'])->format('d/m/Y') : '—' }}</td>
                <td>{{ $fila['ultima_visita'] ? \Carbon\Carbon::parse($fila['ultima_visita'])->format('d/m/Y') : '—' }}</td>
                <td style="font-size:7.5pt;">
                    @foreach($fila['servicios'] as $srv)
                        <span class="tag">{{ $srv }}</span>
                    @endforeach
                    @if($fila['servicios']->isEmpty()) <span class="dim">—</span> @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;color:#aaa;padding:16px;">Sin resultados</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    {{ $filas->count() }} cliente(s) &nbsp;·&nbsp; Cristina Spa &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
