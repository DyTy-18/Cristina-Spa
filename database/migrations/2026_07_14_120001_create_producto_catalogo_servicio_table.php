<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_catalogo_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_catalogo_id')->constrained('productos_catalogo')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['servicio_id', 'producto_catalogo_id'], 'prod_catalogo_servicio_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_catalogo_servicio');
    }
};
