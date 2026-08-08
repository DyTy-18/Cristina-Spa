<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cita_productos', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('producto_catalogo_id')
                ->constrained('productos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cita_productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producto_id');
        });
    }
};
