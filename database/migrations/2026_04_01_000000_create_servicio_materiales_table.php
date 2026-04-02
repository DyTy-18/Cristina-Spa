<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->decimal('cantidad', 8, 2);
            $table->string('unidad', 30);
            $table->timestamps();

            $table->unique(['servicio_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_materiales');
    }
};
