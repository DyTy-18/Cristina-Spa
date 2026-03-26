<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descuentos_programados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->nullable()->constrained()->nullOnDelete();
            $table->string('descripcion', 200)->nullable();
            $table->decimal('porcentaje', 5, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuentos_programados');
    }
};
