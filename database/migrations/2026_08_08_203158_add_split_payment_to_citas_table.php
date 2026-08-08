<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->string('tipo_pago_2', 20)->nullable()->after('tipo_pago');
            $table->decimal('monto_2', 10, 2)->nullable()->after('tipo_pago_2');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['tipo_pago_2', 'monto_2']);
        });
    }
};
