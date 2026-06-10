<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('empleado_exclusivo_id')->nullable()->constrained('empleados')->nullOnDelete()->after('sucursal_id');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['empleado_exclusivo_id']);
            $table->dropColumn('empleado_exclusivo_id');
        });
    }
};
