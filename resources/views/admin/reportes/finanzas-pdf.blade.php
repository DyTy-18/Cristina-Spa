<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Finanzas</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2c2c2c; margin: 0; padding: 20px; }
        h1 { font-size: 17px; color: #2c2c2c; margin: 0 0 4px; }
        .subtitle { font-size: 9px; color: #888; margin-bottom: 14px; }
        .resumen-row { margin-bottom: 16px; }
        .resumen-row table { border-collapse: collapse; }
        .resumen-row td { padding: 7px 14px; border: 1px solid #e0d5c5; text-align: center; }
        .resumen-row .lbl { font-size: 8px; color: #999; text-transform: uppercase; display: block; margin-top: 2px; }
        .resumen-row .val { font-size: 13px; font-weight: bold; }
        .val-green { color: #2d7a46; }
        .val-red   { color: #c0392b; }
        .val-gold  { color: #c9a96e; }
        h2 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #555; margin: 14px 0 6px; border-bottom: 1px solid #e0d5c5; padding-bottom: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th { background: #2c2c2c; color: #fff; padding: 4px 7px; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; text-align: left; }
        table.data td { padding: 4px 7px; border-bottom: 1px solid #f0ebe2; font-size: 9px; }
        table.data tr:nth-child(even) td { background: #faf8f5; }
        .total-row td { background: #c9a96e !important; color: #fff; font-weight: bold; }
        .footer { margin-top: 16px; font-size: 8px; color: #aaa; text-align: right; }
    </style>
</head>
<body>

<h1>Cristina Spa — Reporte de Finanzas</h1>
<div class="subtitle">
    Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
    &nbsp;·&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}
</div>

<div class="resumen-row">
    <table>
        <tr>
            <td><span class="val val-green">Bs. {{ number_format($ingresosCitas, 2) }}</span><span class="lbl">Ingresos</span></td>
            <td><span class="val val-red">Bs. {{ number_format($totalGastos, 2) }}</span><span class="lbl">Gastos operativos</span></td>
            <td><span class="val val-red">Bs. {{ number_format($totalPagos, 2) }}</span><span class="lbl">Pagos a empleados</span></td>
            <td><span class="val {{ $neto >= 0 ? 'val-green' : 'val-red' }}">Bs. {{ number_format($neto, 2) }}</span><span class="lbl">Neto</span></td>
        </tr>
    </table>
</div>

@if($gastos->isNotEmpty())
<h2>Gastos operativos</h2>
<table class="data">
    <thead>
        <tr>
            <th>Fecha</th><th>Descripción</th><th>Categoría</th><th>Monto (Bs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($gastos as $g)
        <tr>
            <td>{{ $g->fecha->format('d/m/Y') }}</td>
            <td>{{ $g->descripcion }}</td>
            <td>{{ \App\Models\Gasto::categorias()[$g->categoria] ?? $g->categoria }}</td>
            <td style="text-align:right;">{{ number_format($g->monto, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3"><strong>Total gastos</strong></td>
            <td style="text-align:right;"><strong>Bs. {{ number_format($totalGastos, 2) }}</strong></td>
        </tr>
    </tbody>
</table>
@endif

@if($pagos->isNotEmpty())
<h2>Pagos a empleados</h2>
<table class="data">
    <thead>
        <tr>
            <th>Fecha</th><th>Empleado</th><th>Tipo</th><th>Monto (Bs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pagos as $p)
        <tr>
            <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
            <td>{{ $p->empleado ? $p->empleado->nombre . ' ' . $p->empleado->apellido : '—' }}</td>
            <td>{{ \App\Models\PagoSueldo::tipos()[$p->tipo] ?? $p->tipo }}</td>
            <td style="text-align:right;">{{ number_format($p->monto, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3"><strong>Total pagos</strong></td>
            <td style="text-align:right;"><strong>Bs. {{ number_format($totalPagos, 2) }}</strong></td>
        </tr>
    </tbody>
</table>
@endif

<div class="footer">Cristina Spa · Reporte de Finanzas · {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
