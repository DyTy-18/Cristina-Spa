<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El catálogo lo define es_reventa (técnico / reventa). El tipo (vitrina, seguimiento,
 * genérico) solo aplica a productos de reventa; los técnicos quedan con tipo NULL.
 *
 * Los productos de seguimiento se trataban como técnicos: pasan a reventa y sus
 * movimientos técnicos se mueven al stock de reventa para no perder su stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE productos MODIFY tipo VARCHAR(20) NULL DEFAULT NULL");

        $seguimiento = DB::table('productos')->where('es_reventa', false)->where('tipo', 'seguimiento')->pluck('id');
        if ($seguimiento->isNotEmpty()) {
            DB::table('productos')->whereIn('id', $seguimiento)->update(['es_reventa' => true]);
            DB::table('entradas')->whereIn('producto_id', $seguimiento)->update(['tipo_stock' => 'reventa']);
            DB::table('salidas')->whereIn('producto_id', $seguimiento)->update(['tipo_stock' => 'reventa']);
        }

        DB::table('productos')->where('es_reventa', false)->update(['tipo' => null, 'peso_gramos' => null]);
        DB::table('productos')->where('es_reventa', true)->whereNull('tipo')->update(['tipo' => 'generico']);
    }

    public function down(): void
    {
        DB::table('productos')->whereNull('tipo')->update(['tipo' => 'generico']);
        DB::statement("ALTER TABLE productos MODIFY tipo VARCHAR(20) NOT NULL DEFAULT 'generico'");
    }
};
