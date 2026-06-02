<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('id')
                  ->constrained('sucursales')->nullOnDelete();
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('id')
                  ->constrained('sucursales')->nullOnDelete();
        });

        // Asignar registros existentes a la sucursal principal
        $principal = DB::table('sucursales')->where('es_principal', true)->value('id');
        if ($principal) {
            DB::table('entradas')->whereNull('sucursal_id')->update(['sucursal_id' => $principal]);
            DB::table('salidas')->whereNull('sucursal_id')->update(['sucursal_id' => $principal]);
        }
    }

    public function down(): void
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
