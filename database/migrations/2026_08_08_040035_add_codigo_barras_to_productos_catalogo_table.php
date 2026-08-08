<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos_catalogo', function (Blueprint $table) {
            $table->string('codigo_barras', 100)->nullable()->unique()->after('id');
            $table->foreign('codigo_barras')->references('codigo_barras')->on('productos')->cascadeOnDelete();
        });

        // Backfill: una fila de catálogo por cada producto de inventario existente,
        // con precio inicial igual al costo cargado en inventario.
        $productos = DB::table('productos')->select('codigo_barras', 'nombre', 'costo')->get();

        foreach ($productos as $producto) {
            $yaExiste = DB::table('productos_catalogo')->where('codigo_barras', $producto->codigo_barras)->exists();

            if ($yaExiste) {
                continue;
            }

            DB::table('productos_catalogo')->insert([
                'codigo_barras' => $producto->codigo_barras,
                'nombre'        => $producto->nombre,
                'precio'        => $producto->costo,
                'activo'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos_catalogo', function (Blueprint $table) {
            $table->dropForeign(['codigo_barras']);
            $table->dropColumn('codigo_barras');
        });
    }
};
