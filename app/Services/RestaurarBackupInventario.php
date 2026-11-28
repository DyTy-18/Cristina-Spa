<?php

namespace App\Services;

use App\Models\InventarioBackup;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vuelve a crear los productos, entradas y salidas guardados en un backup de inventario.
 *
 * - Conserva los IDs originales; lo que ya existe (mismo id) se salta, así restaurar dos
 *   veces no duplica nada.
 * - Solo inserta las columnas que existen hoy en cada tabla (los backups viejos tienen
 *   otra estructura) y deja el resto con su valor por defecto.
 * - Backups anteriores al inventario por sucursal: los productos no tenían sucursal y los
 *   movimientos se ligaban por código de barras. Cada producto se asigna a la sucursal de
 *   sus movimientos; si tenía movimientos en varias, se crea una copia por sucursal (como
 *   hizo la migración de inventario por sucursal).
 * - Una sucursal o cita que ya no existe se reemplaza por la principal / se deja vacía.
 * - Backups nuevos guardan además alertas, materiales de servicio con sus consumos y los
 *   vínculos con ventas/seguimientos/recomendados: también se restauran (los backups
 *   anteriores no los tienen).
 */
class RestaurarBackupInventario
{
    /** Tablas cuyo producto_id queda en NULL al borrar el producto (se re-vinculan). */
    public const TABLAS_VINCULO = ['cita_productos', 'seguimiento_productos', 'producto_catalogo_servicio'];

    private array $columnas = [];
    private array $sucursalesValidas = [];
    private ?int $sucursalPorDefecto = null;

    /** @return array{productos:int, entradas:int, salidas:int, relacionados:int, omitidos:int} */
    public function restaurar(InventarioBackup $backup, ?int $sucursalActiva = null): array
    {
        foreach (['productos', 'entradas', 'salidas'] as $tabla) {
            $this->columnas[$tabla] = array_flip(Schema::getColumnListing($tabla));
        }
        $this->sucursalesValidas  = Sucursal::pluck('id')->flip()->all();
        $this->sucursalPorDefecto = $sucursalActiva
            ?: Sucursal::where('es_principal', true)->value('id')
            ?: Sucursal::orderBy('id')->value('id');

        $datos     = $backup->datos ?? [];
        $productos = collect($datos['productos'] ?? []);
        $entradas  = collect($datos['entradas'] ?? []);
        $salidas   = collect($datos['salidas'] ?? []);

        $stats = ['productos' => 0, 'entradas' => 0, 'salidas' => 0, 'relacionados' => 0, 'omitidos' => 0];

        return DB::transaction(function () use ($datos, $productos, $entradas, $salidas, &$stats) {
            // [código de barras][sucursal] => id de producto, para backups sin producto_id.
            $porCodigo = [];

            foreach ($productos as $p) {
                $p = (array) $p;

                if (array_key_exists('sucursal_id', $p)) {
                    $sucursales = [$this->sucursal($p['sucursal_id'])];
                } else {
                    // Backup viejo: sucursales donde el producto tuvo movimientos.
                    $sucursales = $entradas->merge($salidas)
                        ->filter(fn ($m) => ($m['codigo_barras'] ?? null) === $p['codigo_barras'])
                        ->map(fn ($m) => $this->sucursal($m['sucursal_id'] ?? null))
                        ->unique()->values()->all() ?: [$this->sucursalPorDefecto];
                }

                foreach ($sucursales as $i => $sid) {
                    $fila = $this->filtrar('productos', $p);
                    $fila['sucursal_id'] = $sid;

                    if ($i === 0 && DB::table('productos')->where('id', $p['id'])->exists()) {
                        $porCodigo[$p['codigo_barras']][$sid] = $p['id'];
                        $stats['omitidos']++;
                        continue;
                    }

                    if ($i === 0) {
                        DB::table('productos')->insert($fila);
                        $id = $p['id'];
                    } else {
                        unset($fila['id']);
                        $id = DB::table('productos')->insertGetId($fila);
                    }
                    $porCodigo[$p['codigo_barras']][$sid] = $id;
                    $stats['productos']++;
                }
            }

            foreach (['entradas' => $entradas, 'salidas' => $salidas] as $tabla => $movimientos) {
                foreach ($movimientos as $m) {
                    $m = (array) $m;
                    if (DB::table($tabla)->where('id', $m['id'])->exists()) {
                        $stats['omitidos']++;
                        continue;
                    }

                    $fila = $this->filtrar($tabla, $m);
                    $sid  = $this->sucursal($m['sucursal_id'] ?? null);

                    $productoId = $m['producto_id']
                        ?? $porCodigo[$m['codigo_barras'] ?? ''][$sid]
                        ?? null;
                    if (! $productoId || ! DB::table('productos')->where('id', $productoId)->exists()) {
                        $stats['omitidos']++;
                        continue;
                    }

                    // El movimiento queda en la sucursal de su producto.
                    $fila['producto_id'] = $productoId;
                    $fila['sucursal_id'] = DB::table('productos')->where('id', $productoId)->value('sucursal_id');

                    if (! empty($fila['cita_id']) && ! DB::table('citas')->where('id', $fila['cita_id'])->exists()) {
                        $fila['cita_id'] = null;
                    }

                    DB::table($tabla)->insert($fila);
                    $stats[$tabla]++;
                }
            }

            $this->restaurarRelacionados($datos, $stats);

            return $stats;
        });
    }

    /** Alertas, materiales de servicio, consumos y vínculos (solo en backups nuevos). */
    private function restaurarRelacionados(array $datos, array &$stats): void
    {
        $existe = fn (string $tabla, $id) => $id && DB::table($tabla)->where('id', $id)->exists();

        $insertar = function (string $tabla, array $filas, callable $valida) use ($existe, &$stats) {
            $columnas = array_flip(Schema::getColumnListing($tabla));
            foreach ($filas as $fila) {
                $fila = (array) $fila;
                if ($existe($tabla, $fila['id']) || ! $valida($fila)) {
                    $stats['omitidos']++;
                    continue;
                }
                DB::table($tabla)->insert(array_intersect_key($fila, $columnas));
                $stats['relacionados']++;
            }
        };

        $insertar('servicio_materiales', $datos['servicio_materiales'] ?? [],
            fn ($f) => $existe('productos', $f['producto_id']) && $existe('servicios', $f['servicio_id']));
        $insertar('consumos_material', $datos['consumos_material'] ?? [],
            fn ($f) => $existe('servicio_materiales', $f['servicio_material_id']) && $existe('citas', $f['cita_id']));
        $insertar('alertas_stock', $datos['alertas_stock'] ?? [],
            fn ($f) => $existe('productos', $f['producto_id']));

        // Solo se re-vincula si la fila sigue sin producto (nadie la cambió después).
        foreach ($datos['vinculos'] ?? [] as $tabla => $filas) {
            if (! in_array($tabla, self::TABLAS_VINCULO, true)) {
                continue;
            }
            foreach ($filas as $v) {
                $v = (array) $v;
                $hecho = $existe('productos', $v['producto_id']) && DB::table($tabla)
                    ->where('id', $v['id'])->whereNull('producto_id')
                    ->update(['producto_id' => $v['producto_id']]);
                $hecho ? $stats['relacionados']++ : $stats['omitidos']++;
            }
        }
    }

    /** Solo las columnas que existen hoy en la tabla. */
    private function filtrar(string $tabla, array $fila): array
    {
        return array_intersect_key($fila, $this->columnas[$tabla]);
    }

    private function sucursal($id): ?int
    {
        return $id && isset($this->sucursalesValidas[$id]) ? (int) $id : $this->sucursalPorDefecto;
    }
}
