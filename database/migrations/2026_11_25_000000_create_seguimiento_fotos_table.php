<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fotos del resultado de un servicio, subidas desde el seguimiento de la cita.
 * Forman el álbum de la clienta: cuelgan de la clienta (no solo de la cita) para
 * que el historial sobreviva aunque se borre la cita.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ruta');
            $table->string('descripcion')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_fotos');
    }
};
