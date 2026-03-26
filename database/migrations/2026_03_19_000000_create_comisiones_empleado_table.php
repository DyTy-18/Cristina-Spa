<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones_empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empleado_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones_empleado');
    }
};
