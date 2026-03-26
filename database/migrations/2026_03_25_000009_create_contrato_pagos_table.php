<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos_paquete')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_cuota')->default(1);
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->string('metodo_pago', 50)->nullable(); // efectivo, tarjeta, transferencia
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_pagos');
    }
};
