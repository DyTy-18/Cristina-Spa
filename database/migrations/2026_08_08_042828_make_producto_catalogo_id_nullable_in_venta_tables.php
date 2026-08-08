<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * producto_catalogo_id deja de ser obligatorio: las filas nuevas usan producto_id
     * (inventario) directamente; solo las filas creadas antes de este cambio lo tienen.
     * No se usa Blueprint::change() para evitar la dependencia de doctrine/dbal.
     */
    public function up(): void
    {
        foreach (['cita_productos', 'producto_catalogo_servicio'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['producto_catalogo_id']);
            });

            DB::statement("ALTER TABLE {$tabla} MODIFY producto_catalogo_id BIGINT UNSIGNED NULL");

            Schema::table($tabla, function (Blueprint $table) {
                $table->foreign('producto_catalogo_id')->references('id')->on('productos_catalogo')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['cita_productos', 'producto_catalogo_servicio'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['producto_catalogo_id']);
            });

            DB::statement("ALTER TABLE {$tabla} MODIFY producto_catalogo_id BIGINT UNSIGNED NOT NULL");

            Schema::table($tabla, function (Blueprint $table) {
                $table->foreign('producto_catalogo_id')->references('id')->on('productos_catalogo')->cascadeOnDelete();
            });
        }
    }
};
