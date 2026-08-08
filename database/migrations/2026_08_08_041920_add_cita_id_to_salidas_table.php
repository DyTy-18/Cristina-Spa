<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salidas', function (Blueprint $table) {
            $table->foreignId('cita_id')->nullable()->after('destino')
                ->constrained('citas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salidas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cita_id');
        });
    }
};
