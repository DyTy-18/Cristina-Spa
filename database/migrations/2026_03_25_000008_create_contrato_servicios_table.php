<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos_paquete')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete();
            $table->enum('estado', ['pendiente', 'completado', 'cancelado'])->default('pendiente');
            $table->date('fecha_completado')->nullable();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_servicios');
    }
};
