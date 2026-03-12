<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Comisiones {{ $desde }} – {{ $hasta }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; }

    .header { margin-bottom: 20px; border-bottom: 2px solid #2c2c2c; padding-bottom: 12px; }
    .header h1 { font-size: 18px; font-weight: normal; letter-spacing: 2px; color: #2c2c2c; }
    .header p  { font-size: 9px; color: #777; margin-top: 4px; }

    .resumen { display: flex; gap: 12px; margin-bottom: 20px; }
    .resumen-box {
        border: 1px solid #ddd;
        padding: 8px 14px;
        flex: 1;
    }
    .resumen-box .val { font-size: 15px; color: #2c2c2c; }
    .resumen-box .lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-top: 2px; }

    .emp-block { margin-bottom: 18px; page-break-inside: avoid; }

    .emp-header {
        background: #2c2c2c;
        color: #fff;
        padding: 6px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .emp-header .name { font-size: 12px; letter-spacing: 1px; }
    .emp-header .totales { font-size: 9px; }
    .emp-header .totales strong { font-size: 11px; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: #f5f0e8;
        padding: 5px 8px;
        text-align: left;
        font-size: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }
    tbody td {
        padding: 5px 8px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 9px;
        vertical-align: middle;
    }
    tbody tr:nth-child(even) td { background: #fafafa; }
    tfoot td {
        padding: 5px 8px;
        border-top: 2px solid #ddd;
        font-size: 9px;
        font-weight: bold;
        background: #f5f0e8;
    }

    .com-activa  { color: #28a745; }
    .com-inactiva { color: #aaa; font-style: italic; }
    .monto-com   { color: #c9a96e; }

    .grand-total {
        margin-top: 20px;
        border-top: 2px solid #c9a96e;
        padding-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 30px;
    }
    .grand-total .item { text-align: right; }
    .grand-total .item .val { font-size: 14px; color: #c9a96e; }
    .grand-total .item .lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #888; }

    .foot { margin-top: 30px; font-size: 8px; color: #aaa; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <h1>REPORTE DE COMISIONES</h1>
    <p>Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
       &nbsp;·&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}</p>
</div>

<table style="width:100%;margin-bottom:16px;border-collapse:collapse;">
    <tr>
        <td style="border:1px solid #ddd;padding:8px 12px;">
            <div style="font-size:14px;">{{ $porEmpleado->count() }}</div>
            <div style="font-size:8px;text-transform:uppercase;letter-spacing:1px;color:#888;">Profesionales</div>
        </td>
        <td style="border:1px solid #ddd;padding:8px 12px;">
            <div style="font-size:14px;">Bs. {{ number_format($totalGlobal, 2) }}</div>
            <div style="font-size:8px;text-transform:uppercase;letter-spacing:1px;color:#888;">Total facturado</div>
        </td>
        <td style="border:1px solid #ddd;padding:8px 12px;border-left:3px solid #c9a96e;">
            <div style="font-size:14px;color:#c9a96e;">Bs. {{ number_format($comisionGlobal, 2) }}</div>
            <div style="font-size:8px;text-transform:uppercase;letter-spacing:1px;color:#888;">Total comisiones</div>
        </td>
    </tr>
</table>

@foreach($porEmpleado as $bloque)
    @php $emp = $bloque['empleado']; @endphp
    <div class="emp-block">
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;">
            <tr style="background:#2c2c2c;color:#fff;">
                <td style="padding:6px 10px;font-size:12px;letter-spacing:1px;">
                    {{ $emp?->nombre_completo ?? '—' }}
                    @if($emp?->cargo)
                        <span style="font-size:8px;opacity:0.7;margin-left:8px;">{{ $emp->cargo }}</span>
                    @endif
                </td>
                <td style="padding:6px 10px;text-align:right;font-size:9px;">
                    Facturado <strong style="font-size:11px;">Bs. {{ number_format($bloque['total_precio'], 2) }}</strong>
                    &nbsp;&nbsp;
                    Comisión <strong style="font-size:11px;">Bs. {{ number_format($bloque['total_comision'], 2) }}</strong>
                </td>
            </tr>
        </table>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th style="text-align:right;">Precio</th>
                    <th style="text-align:center;">Comisión</th>
                    <th style="text-align:center;">Part.</th>
                    <th style="text-align:right;">Monto comisión</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloque['filas'] as $fila)
                    <tr>
                        <td>{{ $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') : '—' }}</td>
                        <td>{{ $fila['cliente'] ?? '—' }}</td>
                        <td>{{ $fila['servicio'] }}</td>
                        <td style="text-align:right;">Bs. {{ number_format($fila['precio'], 2) }}</td>
                        <td style="text-align:center;">
                            @if($fila['com_estado'] === 'activa')
                                <span class="com-activa">{{ number_format($fila['pct'], 2) }}%</span>
                            @else
                                <span class="com-inactiva">{{ $fila['com_estado'] === 'inactiva' ? 'inactiva' : 'sin config.' }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;color:#888;">
                            {{ $fila['participantes'] > 1 ? $fila['participantes'] . '×' : '—' }}
                        </td>
                        <td style="text-align:right;">
                            @if($fila['monto_comision'] > 0)
                                <span class="monto-com">Bs. {{ number_format($fila['monto_comision'], 2) }}</span>
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="font-size:8px;color:#888;">{{ $bloque['filas']->count() }} servicios</td>
                    <td style="text-align:right;">Bs. {{ number_format($bloque['total_precio'], 2) }}</td>
                    <td></td><td></td>
                    <td style="text-align:right;color:#c9a96e;">Bs. {{ number_format($bloque['total_comision'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endforeach

<div class="grand-total">
    <div class="item">
        <div class="lbl">Total facturado</div>
        <div class="val">Bs. {{ number_format($totalGlobal, 2) }}</div>
    </div>
    <div class="item">
        <div class="lbl">Total comisiones a pagar</div>
        <div class="val">Bs. {{ number_format($comisionGlobal, 2) }}</div>
    </div>
</div>

<div class="foot">Cristina Spa · Sistema de gestión interna · {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
