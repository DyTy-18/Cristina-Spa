<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El código de barras deja de ser único por sucursal: un mismo código puede
 * corresponder a varios productos (variantes, códigos reutilizados por la marca).
 * Se conserva un índice normal para las búsquedas por (sucursal_id, codigo_barras).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->index(['sucursal_id', 'codigo_barras']);
            $table->dropUnique(['sucursal_id', 'codigo_barras']);
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unique(['sucursal_id', 'codigo_barras']);
            $table->dropIndex(['sucursal_id', 'codigo_barras']);
        });
    }
};
