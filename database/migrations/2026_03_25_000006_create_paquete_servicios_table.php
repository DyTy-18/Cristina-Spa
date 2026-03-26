<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquete_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paquete_id')->constrained('paquetes')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->decimal('descuento_porcentaje', 5, 2)->nullable(); // sobrescribe descuento_general
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paquete_servicios');
    }
};
