<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // vitrina = se vende al cliente (módulo Productos, stock de reventa)
            // seguimiento = se aplica en servicio y se descuenta por gramos
            // generico = solo inventario
            $table->string('tipo', 20)->default('generico')->after('es_reventa');
            $table->decimal('peso_gramos', 10, 2)->nullable()->after('tipo');
        });

        DB::table('productos')->where('es_reventa', true)->update(['tipo' => 'vitrina']);
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'peso_gramos']);
        });
    }
};
