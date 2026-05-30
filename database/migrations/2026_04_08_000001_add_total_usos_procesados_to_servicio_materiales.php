<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicio_materiales', function (Blueprint $table) {
            $table->unsignedInteger('total_usos_procesados')->default(0)->after('usos_por_unidad');
        });

        // Initialize from existing consumos (sum of usos_reales, treating NULL as 1)
        DB::statement('
            UPDATE servicio_materiales sm
            SET total_usos_procesados = COALESCE(
                (SELECT SUM(COALESCE(cm.usos_reales, 1))
                 FROM consumos_material cm
                 WHERE cm.servicio_material_id = sm.id),
                0
            )
        ');
    }

    public function down(): void
    {
        Schema::table('servicio_materiales', function (Blueprint $table) {
            $table->dropColumn('total_usos_procesados');
        });
    }
};
