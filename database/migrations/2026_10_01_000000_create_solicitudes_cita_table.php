<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_cita', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('telefono', 20); // Guardado con prefijo +591
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->text('consulta');
            $table->date('fecha_preferida');
            $table->time('hora_preferida');
            $table->enum('estado', ['pendiente', 'contactado', 'agendado', 'descartado'])->default('pendiente');
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atendido_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_cita');
    }
};
