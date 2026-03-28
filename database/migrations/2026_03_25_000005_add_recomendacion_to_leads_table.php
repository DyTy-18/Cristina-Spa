<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('recomendacion_link_id')
                  ->nullable()->after('ip_address')
                  ->constrained('recomendacion_links')->nullOnDelete();

            $table->foreignId('cliente_id')
                  ->nullable()->after('recomendacion_link_id')
                  ->constrained('clientes')->nullOnDelete();

            $table->foreignId('cita_id')
                  ->nullable()->after('cliente_id')
                  ->constrained('citas')->nullOnDelete();

            $table->foreignId('servicio_id')
                  ->nullable()->after('servicio')
                  ->constrained('servicios')->nullOnDelete();

            $table->foreignId('cupon_referido_id')
                  ->nullable()->after('cita_id')
                  ->constrained('cupones')->nullOnDelete();

            $table->foreignId('cupon_referidor_id')
                  ->nullable()->after('cupon_referido_id')
                  ->constrained('cupones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recomendacion_link_id');
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropConstrainedForeignId('cita_id');
            $table->dropConstrainedForeignId('servicio_id');
            $table->dropConstrainedForeignId('cupon_referido_id');
            $table->dropConstrainedForeignId('cupon_referidor_id');
        });
    }
};
