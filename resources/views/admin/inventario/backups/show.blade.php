@extends('admin.layouts.app')

@section('title', 'Detalle de Backup')
@section('page-title', 'Detalle de Backup')

@section('content')
    <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('admin.inventario.backups') }}" class="btn btn-outline btn-sm">&larr; Volver a Backups</a>
        <button type="button" class="btn btn-primary btn-sm" style="margin-left:auto;" onclick="abrirRestaurar()">
            ↺ Restaurar este backup
        </button>
    </div>

    @if ($backup->restaurado_at)
        <div class="alert alert-success" style="margin-bottom:1rem;">
            Restaurado por última vez el {{ $backup->restaurado_at->format('d/m/Y H:i') }}
            por {{ $backup->restauradoPor->name ?? 'usuario eliminado' }}.
        </div>
    @endif

    {{-- Modal de restaurar: mismo diseño que eliminar con contraseña --}}
    <div class="del-overlay" id="restaurarOverlay">
        <div class="del-modal" style="border-top-color:var(--accent-color);">
            <h4>Restaurar backup</h4>
            <p>
                Se volverán a crear <strong>{{ $backup->productos_count }} productos</strong>,
                <strong>{{ $backup->entradas_count }} entradas</strong> y <strong>{{ $backup->salidas_count }} salidas</strong>
                con sus datos originales (código, marca, línea, costo, tipo, sucursal…).
                Lo que ya exista en el inventario se mantiene y no se duplica.
                <br>
                No se recuperan las alertas de stock ni el uso como material de servicios.
                Ingresa la contraseña de administrador para continuar.
            </p>
            <form method="POST" action="{{ route('admin.inventario.backups.restaurar', $backup) }}" style="display:block;">
                @csrf
                <input type="password" name="password" id="restaurarPassword"
                       placeholder="Contraseña" autocomplete="current-password" required
                       style="width:100%;height:40px;padding:0 0.75rem;border:1px solid rgba(0,0,0,0.18);border-radius:4px;font-size:0.9rem;outline:none;margin-bottom:0.5rem;box-sizing:border-box;">
                <div id="restaurarPasswordError" style="font-size:0.8rem;color:#842029;margin-bottom:0.75rem;min-height:1rem;">
                    @if (session('confirmar_restaurar')) {{ $errors->first('password') }} @endif
                </div>
                <div class="del-modal-actions">
                    <button type="button" class="btn btn-outline" onclick="cerrarRestaurar()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Sí, restaurar</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $datos      = $backup->datos ?? [];
        $productos  = collect($datos['productos'] ?? []);
        $entradas   = collect($datos['entradas'] ?? []);
        $salidas    = collect($datos['salidas'] ?? []);
        $nombresSucursal = \App\Models\Sucursal::pluck('nombre', 'id');
        // Nombre del producto de cada movimiento: por producto_id (backups nuevos) o por código (viejos).
        $porId      = $productos->keyBy('id');
        $porCodigo  = $productos->keyBy('codigo_barras');
        $productoDe = fn ($m) => $porId[$m['producto_id'] ?? null] ?? $porCodigo[$m['codigo_barras'] ?? null] ?? null;
        $pool       = fn ($m) => ($m['tipo_stock'] ?? 'tecnico') === 'reventa' ? 'Reventa' : 'Técnico';
        $fecha      = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : 'Sin fecha';
        $relacionados = [
            'Materiales de servicio'    => count($datos['servicio_materiales'] ?? []),
            'Consumos de materiales'    => count($datos['consumos_material'] ?? []),
            'Alertas de stock'          => count($datos['alertas_stock'] ?? []),
            'Ventas en citas'           => count($datos['vinculos']['cita_productos'] ?? []),
            'Productos en seguimientos' => count($datos['vinculos']['seguimiento_productos'] ?? []),
            'Recomendados en servicios' => count($datos['vinculos']['producto_catalogo_servicio'] ?? []),
        ];
        $guardaRelacionados = array_key_exists('vinculos', $datos);
    @endphp

    <div style="padding:1rem 1.25rem; background:#f8f6f4; border:1px solid #e0d9d0; margin-bottom:1.5rem;">
        <p style="margin:0; font-size:0.9rem;">
            <strong>Eliminado el:</strong> {{ $backup->created_at->format('d/m/Y H:i') }}
            &nbsp;·&nbsp;
            <strong>Por:</strong> {{ $backup->user->name ?? 'Usuario eliminado' }}
            &nbsp;·&nbsp;
            <strong>{{ $backup->productos_count }}</strong> productos,
            <strong>{{ $backup->entradas_count }}</strong> entradas,
            <strong>{{ $backup->salidas_count }}</strong> salidas
        </p>
        <p style="margin:0.5rem 0 0; font-size:0.8rem; color:#888;">
            @if ($guardaRelacionados)
                También guardado:
                {{ collect($relacionados)->map(fn ($n, $k) => "{$k}: {$n}")->implode(' · ') }}
            @else
                Backup anterior a esta versión: solo guarda productos, entradas y salidas
                (sin materiales de servicio, alertas ni vínculos con citas).
            @endif
        </p>
    </div>

    <div class="table-container" style="margin-bottom:1.5rem;">
        <div class="table-header">
            <h3 class="table-title">Productos ({{ $productos->count() }})</h3>
        </div>
        @if ($productos->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Línea</th>
                        <th>Sucursal</th>
                        <th>Catálogo</th>
                        <th style="text-align:right;">Costo</th>
                        <th style="text-align:right;">Precio venta</th>
                        <th style="text-align:right;">Mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $p)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $p['codigo_barras'] }}</code></td>
                            <td>
                                {{ $p['nombre'] }}
                                @if (! empty($p['uso']))
                                    <span style="display:block; font-size:0.75rem; color:#999;">Uso: {{ $p['uso'] }}</span>
                                @endif
                            </td>
                            <td>{{ $p['marca'] ?? '—' }}</td>
                            <td>{{ $p['linea'] ?? '—' }}</td>
                            <td>{{ isset($p['sucursal_id']) ? ($nombresSucursal[$p['sucursal_id']] ?? 'Sucursal eliminada') : '—' }}</td>
                            <td>
                                @if (! empty($p['es_reventa']))
                                    Reventa · {{ \App\Models\Producto::TIPOS[$p['tipo'] ?? ''] ?? 'Genérico' }}
                                    @if (! empty($p['peso_gramos'])) ({{ (float) $p['peso_gramos'] }} g) @endif
                                @else
                                    Técnico
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">Bs. {{ number_format((float) ($p['costo'] ?? 0), 2) }}</td>
                            <td style="text-align:right; white-space:nowrap;">{{ isset($p['precio_venta']) ? 'Bs. ' . number_format((float) $p['precio_venta'], 2) : '—' }}</td>
                            <td style="text-align:right;">{{ $p['stock_minimo'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><p class="empty-state-text">Sin productos en este backup.</p></div>
        @endif
    </div>

    <div class="table-container" style="margin-bottom:1.5rem;">
        <div class="table-header">
            <h3 class="table-title">Entradas ({{ $entradas->count() }})</h3>
        </div>
        @if ($entradas->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Inventario</th>
                        <th>Sucursal</th>
                        <th style="text-align:right;">Unidades</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entradas as $e)
                        <tr>
                            <td>{{ $fecha($e['fecha'] ?? null) }}</td>
                            <td><code style="font-size:0.8rem;">{{ $e['codigo_barras'] ?? '—' }}</code></td>
                            <td>{{ $productoDe($e)['nombre'] ?? '—' }}</td>
                            <td>{{ $pool($e) }}</td>
                            <td>{{ $nombresSucursal[$e['sucursal_id'] ?? null] ?? '—' }}</td>
                            <td style="text-align:right;"><span class="badge badge-success">+{{ $e['unidades'] }}</span></td>
                            <td>
                                @if (! empty($e['es_transferencia']))
                                    <span class="badge badge-info">Transferencia desde {{ ($e['tipo_stock'] ?? '') === 'reventa' ? 'técnico' : 'reventa' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><p class="empty-state-text">Sin entradas en este backup.</p></div>
        @endif
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Salidas ({{ $salidas->count() }})</h3>
        </div>
        @if ($salidas->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Inventario</th>
                        <th>Sucursal</th>
                        <th style="text-align:right;">Unidades</th>
                        <th>Destino</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salidas as $s)
                        <tr>
                            <td>{{ $fecha($s['fecha'] ?? null) }}</td>
                            <td><code style="font-size:0.8rem;">{{ $s['codigo_barras'] ?? '—' }}</code></td>
                            <td>{{ $productoDe($s)['nombre'] ?? '—' }}</td>
                            <td>{{ $pool($s) }}</td>
                            <td>{{ $nombresSucursal[$s['sucursal_id'] ?? null] ?? '—' }}</td>
                            <td style="text-align:right;"><span class="badge badge-danger">&minus;{{ $s['unidades'] }}</span></td>
                            <td>
                                @if (! empty($s['es_transferencia']))
                                    <span class="badge badge-info">{{ $s['destino'] }}</span>
                                @else
                                    {{ $s['destino'] ?? '—' }}
                                @endif
                                @if (! empty($s['cita_id']))
                                    <span style="display:block; font-size:0.75rem; color:#999;">Cita #{{ $s['cita_id'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><p class="empty-state-text">Sin salidas en este backup.</p></div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function abrirRestaurar() {
    document.getElementById('restaurarPassword').value = '';
    document.getElementById('restaurarOverlay').classList.add('open');
    setTimeout(() => document.getElementById('restaurarPassword').focus(), 50);
}
function cerrarRestaurar() {
    document.getElementById('restaurarOverlay').classList.remove('open');
}
document.getElementById('restaurarOverlay').addEventListener('click', function (e) {
    if (e.target === this) cerrarRestaurar();
});
// Contraseña incorrecta: se vuelve a abrir el modal con el error.
@if (session('confirmar_restaurar'))
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('restaurarOverlay').classList.add('open');
    });
@endif
</script>
@endpush
