<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos_paquete', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('paquete_id')->constrained('paquetes')->restrictOnDelete();
            $table->date('fecha_inicio');
            $table->decimal('precio_total', 10, 2);
            $table->enum('tipo_pago', ['completo', 'cuotas'])->default('completo');
            $table->enum('estado', ['activo', 'completado', 'cancelado'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos_paquete');
    }
};
