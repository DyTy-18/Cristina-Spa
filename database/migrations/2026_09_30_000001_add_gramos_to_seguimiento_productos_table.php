<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimiento_productos', function (Blueprint $table) {
            $table->decimal('gramos', 10, 2)->nullable()->after('nombre_personalizado');
        });
    }

    public function down(): void
    {
        Schema::table('seguimiento_productos', function (Blueprint $table) {
            $table->dropColumn('gramos');
        });
    }
};
