<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cada sucursal pasa a tener su propio catálogo de productos: el código de barras
 * deja de ser único global y pasa a ser único por (sucursal_id, codigo_barras).
 * Entradas y salidas se vinculan al producto por producto_id (ya no por código).
 *
 * Backfill: un producto con movimientos/ventas/seguimientos en varias sucursales se
 * clona, una fila por sucursal, y cada registro se reasigna a la copia de su sucursal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('id')
                ->constrained('sucursales')->nullOnDelete();
        });

        foreach (['entradas', 'salidas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('producto_id')->nullable()->after('sucursal_id')
                    ->constrained('productos')->cascadeOnDelete();
            });
        }

        // Las FK por codigo_barras exigen que sea único en productos; se quitan.
        Schema::table('entradas', fn (Blueprint $t) => $t->dropForeign(['codigo_barras']));
        Schema::table('salidas', fn (Blueprint $t) => $t->dropForeign(['codigo_barras']));
        Schema::table('productos_catalogo', fn (Blueprint $t) => $t->dropForeign(['codigo_barras']));

        Schema::table('productos', function (Blueprint $table) {
            $table->dropUnique(['codigo_barras']);
            $table->index('codigo_barras');
            $table->unique(['sucursal_id', 'codigo_barras']);
        });

        $this->backfill();
    }

    private function backfill(): void
    {
        $principal = DB::table('sucursales')->where('es_principal', true)->value('id')
            ?? DB::table('sucursales')->min('id');

        foreach (DB::table('productos')->orderBy('id')->get() as $producto) {
            $codigo = $producto->codigo_barras;

            $sids = DB::table('entradas')->where('codigo_barras', $codigo)->whereNotNull('sucursal_id')->pluck('sucursal_id')
                ->merge(DB::table('salidas')->where('codigo_barras', $codigo)->whereNotNull('sucursal_id')->pluck('sucursal_id'))
                ->merge(DB::table('cita_productos')->join('citas', 'citas.id', '=', 'cita_productos.cita_id')
                    ->where('cita_productos.producto_id', $producto->id)->whereNotNull('citas.sucursal_id')->pluck('citas.sucursal_id'))
                ->merge(DB::table('seguimiento_productos')->join('citas', 'citas.id', '=', 'seguimiento_productos.cita_id')
                    ->where('seguimiento_productos.producto_id', $producto->id)->whereNotNull('citas.sucursal_id')->pluck('citas.sucursal_id'))
                ->map(fn ($s) => (int) $s)->unique()->sort()->values();

            if ($sids->isEmpty() && $principal) {
                $sids = collect([(int) $principal]);
            }
            if ($sids->isEmpty()) {
                continue; // sin sucursales registradas: nada que asignar
            }

            $base = $sids->contains((int) $principal) ? (int) $principal : $sids->first();

            DB::table('productos')->where('id', $producto->id)->update(['sucursal_id' => $base]);

            // Movimientos de la sucursal base (o sin sucursal) → producto original.
            foreach (['entradas', 'salidas'] as $tabla) {
                DB::table($tabla)->where('codigo_barras', $codigo)
                    ->where(fn ($q) => $q->where('sucursal_id', $base)->orWhereNull('sucursal_id'))
                    ->update(['producto_id' => $producto->id, 'sucursal_id' => $base]);
            }

            foreach ($sids->reject(fn ($s) => $s === $base) as $sid) {
                $copia = (array) $producto;
                unset($copia['id']);
                $copia['sucursal_id'] = $sid;
                $copiaId = DB::table('productos')->insertGetId($copia);

                foreach (['entradas', 'salidas'] as $tabla) {
                    DB::table($tabla)->where('codigo_barras', $codigo)->where('sucursal_id', $sid)
                        ->update(['producto_id' => $copiaId]);
                }

                foreach (['cita_productos', 'seguimiento_productos'] as $tabla) {
                    DB::table($tabla)
                        ->where('producto_id', $producto->id)
                        ->whereIn('cita_id', DB::table('citas')->where('sucursal_id', $sid)->select('id'))
                        ->update(['producto_id' => $copiaId]);
                }
            }
        }
    }

    public function down(): void
    {
        // Fusiona las copias por sucursal en la fila de menor id de cada código.
        $duplicados = DB::table('productos')->select('codigo_barras', DB::raw('MIN(id) as keep_id'))
            ->groupBy('codigo_barras')->havingRaw('COUNT(*) > 1')->get();

        foreach ($duplicados as $dup) {
            $copias = DB::table('productos')->where('codigo_barras', $dup->codigo_barras)
                ->where('id', '!=', $dup->keep_id)->pluck('id');

            // Tablas con unique (servicio_id, producto_id): descartar lo que chocaría.
            foreach (['producto_catalogo_servicio', 'servicio_materiales'] as $tabla) {
                $serviciosKeep = DB::table($tabla)->where('producto_id', $dup->keep_id)->pluck('servicio_id');
                DB::table($tabla)->whereIn('producto_id', $copias)->whereIn('servicio_id', $serviciosKeep)->delete();
            }

            foreach (['cita_productos', 'seguimiento_productos', 'producto_catalogo_servicio', 'alertas_stock', 'servicio_materiales'] as $tabla) {
                DB::table($tabla)->whereIn('producto_id', $copias)->update(['producto_id' => $dup->keep_id]);
            }
            foreach (['entradas', 'salidas'] as $tabla) {
                DB::table($tabla)->whereIn('producto_id', $copias)->update(['producto_id' => $dup->keep_id]);
            }
            DB::table('productos')->whereIn('id', $copias)->delete();
        }

        foreach (['entradas', 'salidas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropConstrainedForeignId('producto_id');
            });
        }

        // La FK de sucursal_id usa el índice compuesto: se suelta primero.
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropUnique(['sucursal_id', 'codigo_barras']);
            $table->dropIndex(['codigo_barras']);
            $table->unique('codigo_barras');
            $table->dropColumn('sucursal_id');
        });

        Schema::table('entradas', fn (Blueprint $t) => $t->foreign('codigo_barras')->references('codigo_barras')->on('productos')->cascadeOnDelete());
        Schema::table('salidas', fn (Blueprint $t) => $t->foreign('codigo_barras')->references('codigo_barras')->on('productos')->cascadeOnDelete());
        Schema::table('productos_catalogo', fn (Blueprint $t) => $t->foreign('codigo_barras')->references('codigo_barras')->on('productos')->cascadeOnDelete());
    }
};
