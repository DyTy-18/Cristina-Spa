@extends('admin.layouts.app')

@section('title', 'Importar Excel')
@section('page-title', 'Importar desde Excel')

@section('content')
    <!-- Resultado de la importación -->
    @if (session('import_stats'))
        @php $s = session('import_stats'); @endphp
        <div style="padding:1.25rem 1.5rem; background:#d4edda; border:1px solid #c3e6cb; color:#155724; margin-bottom:1.5rem;">
            <strong>Importación completada</strong>
            <ul style="margin:0.5rem 0 0; padding-left:1.2rem;">
                <li>Productos nuevos creados: <strong>{{ $s['productos_nuevos'] }}</strong></li>
                <li>Entradas registradas: <strong>{{ $s['entradas'] }}</strong></li>
                <li>Salidas registradas: <strong>{{ $s['salidas'] }}</strong></li>
            </ul>
            @if (!empty($s['errores']))
                <div style="margin-top:0.75rem; padding:0.75rem; background:#fff3cd; border:1px solid #ffc107; color:#856404;">
                    <strong>Advertencias ({{ count($s['errores']) }}):</strong>
                    <ul style="margin:0.25rem 0 0; padding-left:1.2rem;">
                        @foreach ($s['errores'] as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div style="margin-top:0.75rem; display:flex; gap:0.5rem;">
                <a href="{{ route('admin.inventario.index') }}" class="btn btn-primary btn-sm">Ver Stock Actualizado</a>
                <a href="{{ route('admin.inventario.entradas') }}" class="btn btn-outline btn-sm">Ver Entradas</a>
                <a href="{{ route('admin.inventario.salidas') }}" class="btn btn-outline btn-sm">Ver Salidas</a>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div style="padding:0.75rem 1rem; background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; margin-bottom:1rem;">
            <ul style="margin:0; padding-left:1.2rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cargar archivo Excel</h3>
        </div>
        <div class="card-body">

            <!-- Instrucciones -->
            <div style="padding:1rem 1.25rem; background:#f8f6f4; border:1px solid #e0d9d0; margin-bottom:1.5rem;">
                <p style="margin:0 0 0.5rem; font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;">El archivo Excel debe tener estas hojas:</p>
                <ul style="margin:0; padding-left:1.2rem; font-size:0.85rem; line-height:1.8;">
                    <li><strong>REGISTRO</strong> — Catálogo de productos: Código de Barra | Nombre | Marca | Línea | Uso. No genera stock inicial (la columna Unidades se ignora).</li>
                    <li><strong>ENTRADA</strong> (o ENTRADAS) — Movimientos de entrada: Código de Barras | Unidades | Fecha</li>
                    <li><strong>SALIDAS</strong> — Movimientos de salida: Código de Barras | Unidades | Fecha | Destino</li>
                    <li><strong>INVENTARIO</strong> — Se ignora por completo (es un resumen calculado con fórmulas).</li>
                </ul>
                <p style="margin:0.75rem 0 0; font-size:0.8rem; color:#888;">
                    Las columnas se leen por nombre de encabezado (no por posición), así que el orden de columnas no importa.
                    La primera fila de cada hoja debe ser el encabezado. Los productos que aparezcan en ENTRADA/SALIDAS pero no en REGISTRO se crearán automáticamente.
                    Los movimientos sin fecha se guardan como "sin fecha de movimiento". El costo de todos los productos entra en 0 (no viene en el archivo).
                </p>
            </div>

            <form action="{{ route('admin.inventario.importar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="excel">Archivo Excel (.xlsx o .xls) *</label>
                    <input type="file" id="excel" name="excel"
                           class="form-control" accept=".xlsx,.xls" required>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                    <button type="submit" class="btn btn-primary">Importar</button>
                    <a href="{{ route('admin.inventario.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
