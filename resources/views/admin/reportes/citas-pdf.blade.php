<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Citas</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2c2c2c; margin: 0; padding: 20px; }
        h1 { font-size: 18px; color: #2c2c2c; margin: 0 0 4px; letter-spacing: 1px; }
        .subtitle { font-size: 9px; color: #888; margin-bottom: 16px; }
        .summary { display: flex; gap: 16px; margin-bottom: 16px; }
        .summary-card { border: 1px solid #e0d5c5; padding: 8px 14px; min-width: 100px; }
        .summary-val { font-size: 16px; color: #2c2c2c; font-weight: bold; }
        .summary-lbl { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c2c2c; color: #fff; padding: 5px 8px; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        td { padding: 5px 8px; border-bottom: 1px solid #f0ebe2; font-size: 9px; }
        tr:nth-child(even) td { background: #faf8f5; }
        .total-row td { background: #c9a96e; color: #fff; font-weight: bold; }
        .footer { margin-top: 14px; font-size: 8px; color: #aaa; text-align: right; }
    </style>
</head>
<body>

<h1>Cristina Spa — Reporte de Citas</h1>
<div class="subtitle">
    Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
    &nbsp;·&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}
</div>

<table style="width:auto;margin-bottom:16px;border-collapse:collapse;">
    <tr>
        <td style="padding:6px 14px;border:1px solid #e0d5c5;font-size:10px;"><strong>{{ $totalCitas }}</strong><br><span style="font-size:8px;color:#888;">Citas</span></td>
        <td style="padding:6px 14px;border:1px solid #e0d5c5;font-size:10px;"><strong>Bs. {{ number_format($totalIngresos, 2) }}</strong><br><span style="font-size:8px;color:#888;">Total ingresos</span></td>
        @if($totalCitas > 0)
        <td style="padding:6px 14px;border:1px solid #e0d5c5;font-size:10px;"><strong>Bs. {{ number_format($totalIngresos / $totalCitas, 2) }}</strong><br><span style="font-size:8px;color:#888;">Promedio/cita</span></td>
        @endif
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Cliente</th>
            <th>Servicios</th>
            <th>Total (Bs.)</th>
            <th>Pago</th>
            <th>Sucursal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($citas as $cita)
        <tr>
            <td>{{ $cita->fecha->format('d/m/Y') }}</td>
            <td>{{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('H:i') : '—' }}</td>
            <td>{{ $cita->cliente ? $cita->cliente->nombre . ' ' . $cita->cliente->apellido : '—' }}</td>
            <td>{{ $cita->citaServicios->map(fn($cs) => $cs->servicio?->nombre)->filter()->implode(' · ') ?: '—' }}</td>
            <td style="text-align:right;">{{ number_format($cita->precio_final, 2) }}</td>
            <td>{{ ucfirst($cita->tipo_pago ?? '—') }}</td>
            <td>{{ $cita->sucursal?->nombre ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="4"><strong>TOTAL</strong></td>
            <td style="text-align:right;"><strong>Bs. {{ number_format($totalIngresos, 2) }}</strong></td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

<div class="footer">Cristina Spa · Reporte de Citas · {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
