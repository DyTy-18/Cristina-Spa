<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->enum('tipo', ['porcentaje', 'monto_fijo'])->default('porcentaje');
            $table->decimal('valor', 8, 2);
            $table->unsignedInteger('usos_maximos')->nullable();  // null = ilimitado
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
