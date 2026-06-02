<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('cliente_id')
                  ->constrained('sucursales')->nullOnDelete();
        });

        // Asignar citas existentes a la sucursal principal
        $principal = DB::table('sucursales')->where('es_principal', true)->value('id');
        if ($principal) {
            DB::table('citas')->whereNull('sucursal_id')->update(['sucursal_id' => $principal]);
        }
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
