<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('campana_id')
                  ->nullable()
                  ->after('notas')
                  ->constrained('campanas')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Campana::class);
            $table->dropColumn('campana_id');
        });
    }
};
