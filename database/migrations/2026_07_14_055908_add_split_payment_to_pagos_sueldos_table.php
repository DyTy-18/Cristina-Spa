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
        Schema::table('pagos_sueldos', function (Blueprint $table) {
            $table->string('metodo_pago_2')->nullable()->after('metodo_pago'); // efectivo, transferencia, qr
            $table->decimal('monto_2', 10, 2)->nullable()->after('metodo_pago_2'); // monto pagado con metodo_pago_2 (el resto de "monto" corresponde a metodo_pago)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_sueldos', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago_2', 'monto_2']);
        });
    }
};
