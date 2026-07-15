<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->enum('tipo_stock', ['tecnico', 'reventa'])->default('tecnico')->after('codigo_barras');
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->enum('tipo_stock', ['tecnico', 'reventa'])->default('tecnico')->after('codigo_barras');
        });
    }

    public function down(): void
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->dropColumn('tipo_stock');
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->dropColumn('tipo_stock');
        });
    }
};
