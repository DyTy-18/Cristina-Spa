<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Registro de la última restauración de cada backup (cuándo y quién). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_backups', function (Blueprint $table) {
            $table->timestamp('restaurado_at')->nullable()->after('datos');
            $table->foreignId('restaurado_por')->nullable()->after('restaurado_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventario_backups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurado_por');
            $table->dropColumn('restaurado_at');
        });
    }
};
