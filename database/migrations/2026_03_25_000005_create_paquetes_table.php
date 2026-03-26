<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquetes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('categoria', 60);              // novia, quinceañera, eventos, otro
            $table->foreignId('nivel_id')
                  ->nullable()
                  ->constrained('paquete_niveles')
                  ->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->decimal('descuento_general', 5, 2)->nullable(); // % aplicado a todos los servicios sin descuento propio
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquetes');
    }
};
