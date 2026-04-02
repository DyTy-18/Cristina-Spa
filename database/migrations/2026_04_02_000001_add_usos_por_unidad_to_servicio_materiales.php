<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicio_materiales', function (Blueprint $table) {
            $table->unsignedInteger('usos_por_unidad')->default(1)->after('unidad');
        });
    }

    public function down(): void
    {
        Schema::table('servicio_materiales', function (Blueprint $table) {
            $table->dropColumn('usos_por_unidad');
        });
    }
};
